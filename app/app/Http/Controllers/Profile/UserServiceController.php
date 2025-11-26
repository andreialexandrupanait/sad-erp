<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserServiceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get all active organization services
        $allServices = Service::query()
            ->active()
            ->ordered()
            ->get();

        // Get user's service assignments
        $userServices = UserService::query()
            ->where('user_id', $user->id)
            ->with('service')
            ->get()
            ->keyBy('service_id');

        return view('profile.services', compact('allServices', 'userServices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'hourly_rate' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
        ]);

        $user = Auth::user();

        // Check if already exists
        $existing = UserService::where('user_id', $user->id)
            ->where('service_id', $validated['service_id'])
            ->first();

        if ($existing) {
            return redirect()->route('profile.services')
                ->with('error', 'You already have a rate set for this service.');
        }

        UserService::create([
            'user_id' => $user->id,
            'service_id' => $validated['service_id'],
            'hourly_rate' => $validated['hourly_rate'],
            'currency' => $validated['currency'],
            'is_active' => true,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Service rate added successfully.',
            ]);
        }

        return redirect()->route('profile.services')
            ->with('success', 'Service rate added successfully.');
    }

    public function update(Request $request, UserService $userService)
    {
        // Ensure user can only update their own records
        if ($userService->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'hourly_rate' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $userService->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Service rate updated successfully.',
            ]);
        }

        return redirect()->route('profile.services')
            ->with('success', 'Service rate updated successfully.');
    }

    public function destroy(UserService $userService)
    {
        // Ensure user can only delete their own records
        if ($userService->user_id !== Auth::id()) {
            abort(403);
        }

        $userService->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Service rate removed.',
            ]);
        }

        return redirect()->route('profile.services')
            ->with('success', 'Service rate removed.');
    }
}
