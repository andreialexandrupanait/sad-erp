<?php

namespace App\Http\Controllers;

use App\Models\ContractTemplate;
use App\Services\VariableRegistry;
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
        $variables = VariableRegistry::getForUI(VariableRegistry::TYPE_CONTRACT);

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
            'blocks' => 'nullable|array',
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
        \Log::info('[ContractTemplate] Loading template', [
            'id' => $contractTemplate->id,
            'content_length' => strlen($contractTemplate->content ?? ''),
        ]);

        return view('settings.contract-templates.edit-simple', [
            'template' => $contractTemplate,
            'categories' => ContractTemplate::getCategories(),
            'variables' => VariableRegistry::getForUI(VariableRegistry::TYPE_CONTRACT),
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

        // Log for debugging
        \Log::info('[ContractTemplate] Saving template', [
            'id' => $contractTemplate->id,
            'content_length' => strlen($validated['content'] ?? ''),
        ]);

        // Handle checkbox values (unchecked = not sent)
        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['blocks'] = null; // Phase 1: Not using blocks

        // If setting as default, unset other defaults
        if ($validated['is_default'] && !$contractTemplate->is_default) {
            ContractTemplate::where('is_default', true)->update(['is_default' => false]);
        }

        $contractTemplate->update($validated);

        // Verify save
        $contractTemplate->refresh();
        \Log::info('[ContractTemplate] Template saved', [
            'id' => $contractTemplate->id,
            'content_length' => strlen($contractTemplate->content ?? ''),
        ]);

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
        try {
            \Log::info('[ContractTemplate] Delete request', [
                'id' => $contractTemplate->id,
                'name' => $contractTemplate->name,
            ]);

            // Don't allow deleting if contracts are using this template
            $contractsCount = $contractTemplate->contracts()->count();
            if ($contractsCount > 0) {
                \Log::warning('[ContractTemplate] Cannot delete - in use by contracts', [
                    'id' => $contractTemplate->id,
                    'contracts_count' => $contractsCount,
                ]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => __('Cannot delete template that is in use by contracts.'),
                    ], 422);
                }
                return back()->with('error', __('Cannot delete template that is in use by :count contracts.', ['count' => $contractsCount]));
            }

            $contractTemplate->delete();

            \Log::info('[ContractTemplate] Template deleted', ['id' => $contractTemplate->id]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Contract template deleted successfully.'),
                ]);
            }

            return redirect()
                ->route('settings.contract-templates.index')
                ->with('success', __('Contract template deleted successfully.'));

        } catch (\Exception $e) {
            \Log::error('[ContractTemplate] Delete failed', [
                'id' => $contractTemplate->id,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => __('Failed to delete template: ') . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', __('Failed to delete template: ') . $e->getMessage());
        }
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
