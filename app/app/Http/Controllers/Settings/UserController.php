<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\UpdateUserPermissionsRequest;
use App\Models\Module;
use App\Models\RoleModuleDefault;
use App\Models\User;
use App\Models\UserModulePermission;
use App\Notifications\UserInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isOrgAdmin() && !auth()->user()->isSuperAdmin()) {
                abort(403, __('Only administrators can manage users.'));
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::where('organization_id', auth()->user()->organization_id)
            ->with('organization');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'archived') {
                $query->onlyTrashed();
            } else {
                $query->where('status', $request->status);
            }
        }

        // Sort
        $sortBy = $request->get('sort', 'name');
        $sortDir = $request->get('dir', 'asc');
        $allowedSorts = ['name', 'email', 'role', 'status', 'last_login_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'name';
        }
        $query->orderBy($sortBy, $sortDir);

        $users = $query->paginate(15)->withQueryString();

        // Stats
        $stats = [
            'total' => User::where('organization_id', auth()->user()->organization_id)->count(),
            'active' => User::where('organization_id', auth()->user()->organization_id)->where('status', 'active')->count(),
            'inactive' => User::where('organization_id', auth()->user()->organization_id)->where('status', 'inactive')->count(),
            'archived' => User::where('organization_id', auth()->user()->organization_id)->onlyTrashed()->count(),
        ];

        return view('settings.users.index', compact('users', 'stats'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('settings.users.create');
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();
        $validated['organization_id'] = auth()->user()->organization_id;

        // Handle password option
        if ($request->password_option === 'manual') {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            // Generate random password for invitation
            $validated['password'] = Hash::make(Str::random(32));
        }

        $user = User::create($validated);

        // Send invitation email if requested
        if ($request->password_option === 'invite') {
            $token = Password::broker()->createToken($user);
            $user->notify(new UserInvitation($token));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('User created successfully.'),
                'user' => $user,
            ], 201);
        }

        $message = $request->password_option === 'invite'
            ? __('User created and invitation email sent.')
            : __('User created successfully.');

        return redirect()->route('settings.users.index')
            ->with('success', $message);
    }

    /**
     * Display user details and permission matrix.
     */
    public function show(User $user)
    {
        $this->authorizeUserAccess($user);

        $modules = Module::getAllCached();
        $roleDefaults = RoleModuleDefault::getForRole($user->role ?? 'user');
        $userPermissions = $user->modulePermissions()->with('module')->get()->keyBy('module_id');

        // Build permission matrix
        $permissionMatrix = [];
        foreach ($modules as $module) {
            $userPerm = $userPermissions->get($module->id);
            $isCustom = $userPerm !== null;

            $permissions = $isCustom
                ? [
                    'can_view' => $userPerm->can_view,
                    'can_create' => $userPerm->can_create,
                    'can_update' => $userPerm->can_update,
                    'can_delete' => $userPerm->can_delete,
                    'can_export' => $userPerm->can_export,
                ]
                : ($roleDefaults[$module->slug] ?? [
                    'can_view' => false,
                    'can_create' => false,
                    'can_update' => false,
                    'can_delete' => false,
                    'can_export' => false,
                ]);

            $permissionMatrix[$module->slug] = [
                'module' => $module,
                'permissions' => $permissions,
                'is_custom' => $isCustom,
            ];
        }

        return view('settings.users.show', compact('user', 'modules', 'permissionMatrix'));
    }

    /**
     * Show the form for editing a user.
     */
    public function edit(User $user)
    {
        $this->authorizeUserAccess($user);

        return view('settings.users.edit', compact('user'));
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorizeUserAccess($user);
        $this->preventSelfDemotion($user, $request);

        $validated = $request->validated();

        // Only update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('User updated successfully.'),
                'user' => $user->fresh(),
            ]);
        }

        return redirect()->route('settings.users.show', $user)
            ->with('success', __('User updated successfully.'));
    }

    /**
     * Soft delete the specified user.
     */
    public function destroy(User $user)
    {
        $this->authorizeUserAccess($user);

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->route('settings.users.index')
                ->with('error', __('You cannot delete your own account.'));
        }

        // Prevent deleting superadmin
        if ($user->isSuperAdmin()) {
            return redirect()->route('settings.users.index')
                ->with('error', __('Cannot delete super admin user.'));
        }

        $user->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('User archived successfully.'),
            ]);
        }

        return redirect()->route('settings.users.index')
            ->with('success', __('User archived successfully.'));
    }

    /**
     * Restore a soft-deleted user.
     */
    public function restore($userId)
    {
        $user = User::withTrashed()
            ->where('organization_id', auth()->user()->organization_id)
            ->findOrFail($userId);

        $user->restore();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('User restored successfully.'),
            ]);
        }

        return redirect()->route('settings.users.index')
            ->with('success', __('User restored successfully.'));
    }

    /**
     * Update user's module permissions.
     */
    public function updatePermissions(UpdateUserPermissionsRequest $request, User $user)
    {
        $this->authorizeUserAccess($user);

        // Cannot modify superadmin or admin permissions
        if ($user->isSuperAdmin() || $user->isOrgAdmin()) {
            return response()->json([
                'success' => false,
                'message' => __('Cannot modify permissions for administrators.'),
            ], 403);
        }

        $validated = $request->validated();
        $modules = Module::getAllCached()->keyBy('slug');

        DB::transaction(function () use ($user, $validated, $modules) {
            foreach ($validated['permissions'] as $moduleSlug => $perms) {
                $module = $modules->get($moduleSlug);
                if (!$module) {
                    continue;
                }

                UserModulePermission::updateOrCreate(
                    ['user_id' => $user->id, 'module_id' => $module->id],
                    [
                        'can_view' => $perms['can_view'] ?? false,
                        'can_create' => $perms['can_create'] ?? false,
                        'can_update' => $perms['can_update'] ?? false,
                        'can_delete' => $perms['can_delete'] ?? false,
                        'can_export' => $perms['can_export'] ?? false,
                    ]
                );
            }
        });

        // Clear user's permission cache
        $user->clearPermissionCache();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Permissions updated successfully.'),
            ]);
        }

        return redirect()->route('settings.users.show', $user)
            ->with('success', __('Permissions updated successfully.'));
    }

    /**
     * Reset user's permissions to role defaults.
     */
    public function resetToRoleDefaults(User $user)
    {
        $this->authorizeUserAccess($user);

        // Cannot modify superadmin or admin permissions
        if ($user->isSuperAdmin() || $user->isOrgAdmin()) {
            return response()->json([
                'success' => false,
                'message' => __('Cannot modify permissions for administrators.'),
            ], 403);
        }

        // Delete all custom permissions
        $user->modulePermissions()->delete();
        $user->clearPermissionCache();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Permissions reset to role defaults.'),
            ]);
        }

        return redirect()->route('settings.users.show', $user)
            ->with('success', __('Permissions reset to role defaults.'));
    }

    /**
     * Resend invitation email.
     */
    public function resendInvite(User $user)
    {
        $this->authorizeUserAccess($user);

        $token = Password::broker()->createToken($user);
        $user->notify(new UserInvitation($token));

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Invitation email sent.'),
            ]);
        }

        return redirect()->route('settings.users.show', $user)
            ->with('success', __('Invitation email sent.'));
    }

    /**
     * Ensure user belongs to same organization.
     */
    protected function authorizeUserAccess(User $user): void
    {
        if ($user->organization_id !== auth()->user()->organization_id) {
            abort(403, __('User not found.'));
        }
    }

    /**
     * Prevent users from demoting themselves.
     */
    protected function preventSelfDemotion(User $user, Request $request): void
    {
        if ($user->id === auth()->id()) {
            // Prevent changing own role to non-admin
            if ($request->has('role') && !in_array($request->role, ['admin', 'superadmin'])) {
                abort(403, __('You cannot demote yourself.'));
            }
            // Prevent deactivating self
            if ($request->has('status') && $request->status !== 'active') {
                abort(403, __('You cannot deactivate your own account.'));
            }
        }
    }
}
