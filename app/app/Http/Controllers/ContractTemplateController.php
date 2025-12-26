<?php

namespace App\Http\Controllers;

use App\Models\ContractTemplate;
use App\Services\Contract\ContractVariableRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContractTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of contract templates.
     */
    public function index(): View
    {
        $templates = ContractTemplate::orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return view('settings.contract-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new template.
     */
    public function create(): View
    {
        $categories = ContractTemplate::getCategories();
        $variables = ContractVariableRegistry::getDefinitions();

        return view('settings.contract-templates.create', compact('categories', 'variables'));
    }

    /**
     * Store a newly created template.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'content' => 'nullable|string|max:500000',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['organization_id'] = auth()->user()->organization_id;
        $validated['is_default'] = $validated['is_default'] ?? false;
        $validated['is_active'] = $validated['is_active'] ?? true;

        // If setting as default, unset other defaults
        if ($validated['is_default']) {
            ContractTemplate::where('is_default', true)->update(['is_default' => false]);
        }

        $template = ContractTemplate::create($validated);

        return redirect()
            ->route('settings.contract-templates.edit', $template)
            ->with('success', __('Contract template created successfully.'));
    }

    /**
     * Show the form for editing the template.
     */
    public function edit(ContractTemplate $contractTemplate): View
    {
        $categories = ContractTemplate::getCategories();
        // Use ContractVariableRegistry for consistent variables
        $variables = ContractVariableRegistry::getDefinitions();

        return view('settings.contract-templates.edit', [
            'template' => $contractTemplate,
            'categories' => $categories,
            'variables' => $variables,
        ]);
    }

    /**
     * Update the template.
     */
    public function update(Request $request, ContractTemplate $contractTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'content' => 'nullable|string|max:500000',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_default'] = $validated['is_default'] ?? false;
        $validated['is_active'] = $validated['is_active'] ?? true;

        // If setting as default, unset other defaults
        if ($validated['is_default'] && !$contractTemplate->is_default) {
            ContractTemplate::where('is_default', true)->update(['is_default' => false]);
        }

        $contractTemplate->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Contract template saved successfully.'),
            ]);
        }

        return back()->with('success', __('Contract template saved successfully.'));
    }

    /**
     * Delete the template.
     */
    public function destroy(Request $request, ContractTemplate $contractTemplate): JsonResponse|RedirectResponse
    {
        // Don't allow deleting if contracts are using this template
        if ($contractTemplate->contracts()->count() > 0) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => __('Cannot delete template that is in use by contracts.'),
                ], 422);
            }
            return back()->with('error', __('Cannot delete template that is in use by contracts.'));
        }

        $contractTemplate->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Contract template deleted successfully.'),
            ]);
        }

        return redirect()
            ->route('settings.contract-templates.index')
            ->with('success', __('Contract template deleted successfully.'));
    }

    /**
     * Set template as default.
     */
    public function setDefault(ContractTemplate $contractTemplate): JsonResponse
    {
        ContractTemplate::where('is_default', true)->update(['is_default' => false]);
        $contractTemplate->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => __('Default template updated.'),
        ]);
    }

    /**
     * Toggle template active status.
     */
    public function toggleActive(ContractTemplate $contractTemplate): JsonResponse
    {
        $contractTemplate->update(['is_active' => !$contractTemplate->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $contractTemplate->is_active,
            'message' => $contractTemplate->is_active
                ? __('Template activated.')
                : __('Template deactivated.'),
        ]);
    }

    /**
     * Duplicate a template.
     */
    public function duplicate(Request $request, ContractTemplate $contractTemplate): JsonResponse|RedirectResponse
    {
        $newTemplate = $contractTemplate->replicate();
        $newTemplate->name = $contractTemplate->name . ' (' . __('Copy') . ')';
        $newTemplate->is_default = false;
        $newTemplate->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Template duplicated successfully.'),
                'template' => $newTemplate,
            ]);
        }

        return redirect()
            ->route('settings.contract-templates.edit', $newTemplate)
            ->with('success', __('Template duplicated successfully.'));
    }
}
