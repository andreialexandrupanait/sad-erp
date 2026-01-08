<?php

namespace App\Http\Controllers;

use App\Models\ContractTemplate;
use App\Models\DocumentTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DocumentTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of templates.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->wantsJson() || $request->ajax()) {
            return $this->indexJson($request);
        }

        $templates = DocumentTemplate::orderBy('type')
            ->orderBy('name')
            ->get();

        $types = DocumentTemplate::getTypes();

        return view('document-templates.index', compact('templates', 'types'));
    }

    /**
     * Return templates data as JSON.
     * Includes both DocumentTemplate (offers) and ContractTemplate records.
     */
    private function indexJson(Request $request): JsonResponse
    {
        $searchTerm = $request->filled('q') ? '%' . $request->q . '%' : null;
        $typeFilter = $request->filled('type') ? $request->type : null;
        $activeFilter = $request->has('is_active') && $request->is_active !== '' ? $request->boolean('is_active') : null;

        $allTemplates = collect();

        // Fetch DocumentTemplates (offers) - unless filtering for contract only
        if (!$typeFilter || $typeFilter === 'offer') {
            $docQuery = DocumentTemplate::query();

            if ($searchTerm) {
                $docQuery->where('name', 'like', $searchTerm);
            }
            if ($activeFilter !== null) {
                $docQuery->where('is_active', $activeFilter);
            }

            $docTemplates = $docQuery->orderBy('name')->get()->map(function ($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'type' => 'offer',
                    'type_label' => __('Offer'),
                    'model_type' => 'document_template',
                    'is_default' => $template->is_default,
                    'is_active' => $template->is_active,
                    'created_at' => $template->created_at?->toISOString(),
                    'updated_at' => $template->updated_at?->toISOString(),
                ];
            });

            $allTemplates = $allTemplates->merge($docTemplates);
        }

        // Fetch ContractTemplates - unless filtering for offer only
        if (!$typeFilter || $typeFilter === 'contract') {
            $contractQuery = ContractTemplate::query();

            if ($searchTerm) {
                $contractQuery->where('name', 'like', $searchTerm);
            }
            if ($activeFilter !== null) {
                $contractQuery->where('is_active', $activeFilter);
            }

            $contractTemplates = $contractQuery->orderBy('name')->get()->map(function ($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'type' => 'contract',
                    'type_label' => __('Contract'),
                    'model_type' => 'contract_template',
                    'category' => $template->category,
                    'is_default' => $template->is_default,
                    'is_active' => $template->is_active,
                    'created_at' => $template->created_at?->toISOString(),
                    'updated_at' => $template->updated_at?->toISOString(),
                ];
            });

            $allTemplates = $allTemplates->merge($contractTemplates);
        }

        // Sort by type then name
        $sortedTemplates = $allTemplates->sortBy([
            ['type', 'asc'],
            ['name', 'asc'],
        ])->values();

        return response()->json([
            'templates' => $sortedTemplates,
            'types' => [
                'offer' => __('Offer'),
                'contract' => __('Contract'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new template.
     * For offer templates, creates a blank template and redirects to builder.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $type = $request->get('type', 'offer');

        // For offer templates, create a blank template and redirect to builder
        if ($type === 'offer') {
            $template = DocumentTemplate::create([
                'name' => __('New Offer Template'),
                'type' => 'offer',
                'content' => json_encode(['blocks' => [], 'services' => []]),
                'is_default' => false,
                'is_active' => true,
            ]);

            return redirect()->route('settings.document-templates.builder', $template);
        }

        // For other types, show the form
        $types = DocumentTemplate::getTypes();
        $selectedType = $type;
        $template = new DocumentTemplate(['type' => $selectedType]);
        $variables = $template->getAvailableVariables();

        return view('document-templates.create', compact('types', 'selectedType', 'variables'));
    }

    /**
     * Store a newly created template.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:offer,contract,annex',
            'content' => 'required|string',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $template = DocumentTemplate::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'content' => $validated['content'],
            'is_default' => $validated['is_default'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Template created successfully.'),
                'template' => $template,
            ], 201);
        }

        return redirect()
            ->route('document-templates.index')
            ->with('success', __('Template created successfully.'));
    }

    /**
     * Display the specified template.
     */
    public function show(Request $request, DocumentTemplate $documentTemplate): View|JsonResponse
    {
        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            $content = is_string($documentTemplate->content)
                ? json_decode($documentTemplate->content, true)
                : $documentTemplate->content;

            return response()->json([
                'id' => $documentTemplate->id,
                'name' => $documentTemplate->name,
                'type' => $documentTemplate->type,
                'content' => $content,
                'is_default' => $documentTemplate->is_default,
                'is_active' => $documentTemplate->is_active,
            ]);
        }

        $variables = $documentTemplate->getAvailableVariables();

        return view('document-templates.show', [
            'template' => $documentTemplate,
            'variables' => $variables,
        ]);
    }

    /**
     * Show the form for editing the specified template.
     */
    public function edit(DocumentTemplate $documentTemplate): View
    {
        $types = DocumentTemplate::getTypes();
        $variables = $documentTemplate->getAvailableVariables();

        return view('document-templates.edit', [
            'template' => $documentTemplate,
            'types' => $types,
            'variables' => $variables,
        ]);
    }

    /**
     * Update the specified template.
     */
    public function update(Request $request, DocumentTemplate $documentTemplate): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:offer,contract,annex',
            'content' => 'required|string',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $documentTemplate->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'content' => $validated['content'],
            'is_default' => $validated['is_default'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Template updated successfully.'),
                'template' => $documentTemplate->fresh(),
            ]);
        }

        return redirect()
            ->route('document-templates.index')
            ->with('success', __('Template updated successfully.'));
    }

    /**
     * Remove the specified template.
     */
    public function destroy(Request $request, DocumentTemplate $documentTemplate): JsonResponse|RedirectResponse
    {
        // Check if template is in use
        $offersCount = $documentTemplate->offers()->count();
        $contractsCount = $documentTemplate->contracts()->count();

        if ($offersCount > 0 || $contractsCount > 0) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => __('Cannot delete template that is in use.'),
                ], 422);
            }
            return back()->with('error', __('Cannot delete template that is in use.'));
        }

        $documentTemplate->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Template deleted successfully.'),
            ]);
        }

        return redirect()
            ->route('settings.document-templates.index')
            ->with('success', __('Template deleted successfully.'));
    }

    /**
     * Bulk delete templates.
     * Handles both DocumentTemplate (offers) and ContractTemplate records.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer',
            'types' => 'nullable|array',
            'types.*' => 'nullable|string|in:document_template,contract_template',
        ]);

        $deleted = 0;
        $errors = [];

        $ids = $validated['ids'];
        $types = $validated['types'] ?? [];

        foreach ($ids as $index => $id) {
            $modelType = $types[$index] ?? null;

            // Try to find the template in the appropriate table
            $template = null;

            if ($modelType === 'contract_template') {
                $template = ContractTemplate::find($id);
                if ($template) {
                    // Check if contract template is in use
                    if ($template->contracts()->count() > 0) {
                        $errors[] = __('Template ":name" is in use by contracts and cannot be deleted.', ['name' => $template->name]);
                        continue;
                    }
                }
            } elseif ($modelType === 'document_template') {
                $template = DocumentTemplate::find($id);
                if ($template) {
                    // Check if document template is in use
                    if ($template->offers()->count() > 0) {
                        $errors[] = __('Template ":name" is in use by offers and cannot be deleted.', ['name' => $template->name]);
                        continue;
                    }
                }
            } else {
                // Try both tables if type not specified
                $template = DocumentTemplate::find($id);
                if ($template) {
                    if ($template->offers()->count() > 0) {
                        $errors[] = __('Template ":name" is in use by offers and cannot be deleted.', ['name' => $template->name]);
                        continue;
                    }
                } else {
                    $template = ContractTemplate::find($id);
                    if ($template && $template->contracts()->count() > 0) {
                        $errors[] = __('Template ":name" is in use by contracts and cannot be deleted.', ['name' => $template->name]);
                        continue;
                    }
                }
            }

            if (!$template) {
                continue;
            }

            $template->delete();
            $deleted++;
        }

        if (count($errors) > 0) {
            return response()->json([
                'success' => $deleted > 0,
                'message' => __(':count template(s) deleted.', ['count' => $deleted]),
                'errors' => $errors,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => __(':count template(s) deleted successfully.', ['count' => $deleted]),
        ]);
    }

    /**
     * Duplicate a template.
     */
    public function duplicate(Request $request, DocumentTemplate $documentTemplate): JsonResponse|RedirectResponse
    {
        $newTemplate = $documentTemplate->replicate(['is_default']);
        $newTemplate->name = $documentTemplate->name . ' (' . __('Copy') . ')';
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
            ->route('settings.document-templates.edit', $newTemplate)
            ->with('success', __('Template duplicated successfully.'));
    }

    /**
     * Set template as default.
     */
    public function setDefault(DocumentTemplate $documentTemplate): JsonResponse|RedirectResponse
    {
        $documentTemplate->is_default = true;
        $documentTemplate->save();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Template set as default.'),
            ]);
        }

        return back()->with('success', __('Template set as default.'));
    }

    /**
     * Toggle template active status.
     */
    public function toggleActive(DocumentTemplate $documentTemplate): JsonResponse
    {
        $documentTemplate->is_active = !$documentTemplate->is_active;
        $documentTemplate->save();

        return response()->json([
            'success' => true,
            'message' => $documentTemplate->is_active
                ? __('Template activated.')
                : __('Template deactivated.'),
            'is_active' => $documentTemplate->is_active,
        ]);
    }

    /**
     * Preview template with sample data.
     */
    public function preview(Request $request, DocumentTemplate $documentTemplate): View|JsonResponse
    {
        // Sample data for preview
        $sampleData = [
            'client_name' => 'Ion Popescu',
            'client_company' => 'SC Exemplu SRL',
            'client_email' => 'ion@exemplu.ro',
            'client_phone' => '0721 123 456',
            'client_address' => 'Str. Exemplu nr. 1, București',
            'client_tax_id' => 'RO12345678',
            'organization_name' => 'Compania Mea SRL',
            'current_date' => now()->format('d.m.Y'),
            'offer_number' => 'OFR-2025-001',
            'offer_title' => 'Servicii Web Development',
            'offer_valid_until' => now()->addDays(30)->format('d.m.Y'),
            'offer_subtotal' => '5,000.00 RON',
            'offer_discount' => '500.00 RON',
            'offer_total' => '4,500.00 RON',
            'offer_items_table' => '<table><tr><td>Dezvoltare Website</td><td>1</td><td>5,000.00 RON</td></tr></table>',
            'contract_number' => 'CTR-2025-001',
            'contract_title' => 'Contract Servicii Web',
            'contract_start_date' => now()->format('d.m.Y'),
            'contract_end_date' => now()->addYear()->format('d.m.Y'),
            'contract_total_value' => '4,500.00 RON',
            'annex_number' => '1',
            'annex_code' => 'AN-CTR-2025-001-1',
            'annex_title' => 'Anexa - Servicii Suplimentare',
            'annex_effective_date' => now()->format('d.m.Y'),
            'annex_additional_value' => '1,500.00 RON',
            'original_contract_number' => 'CTR-2025-001',
        ];

        $renderedContent = $documentTemplate->render($sampleData);

        if ($request->expectsJson()) {
            return response()->json([
                'content' => $renderedContent,
            ]);
        }

        return view('document-templates.preview', [
            'template' => $documentTemplate,
            'content' => $renderedContent,
        ]);
    }

    /**
     * Get available variables for a template type.
     */
    public function variables(Request $request): JsonResponse
    {
        $type = $request->get('type', 'offer');
        $template = new DocumentTemplate(['type' => $type]);

        return response()->json([
            'variables' => $template->getAvailableVariables(),
        ]);
    }

    /**
     * Show the visual builder for offer templates.
     */
    public function builder(DocumentTemplate $documentTemplate): View|RedirectResponse
    {
        // Only offer templates can use the visual builder
        if ($documentTemplate->type !== 'offer') {
            return redirect()
                ->route('settings.document-templates.edit', $documentTemplate)
                ->with('info', __('Visual builder is only available for offer templates.'));
        }

        $services = \App\Models\Service::where('is_active', true)->orderBy('sort_order')->get();
        $organization = auth()->user()->organization;

        // Parse existing content (blocks JSON)
        $blocks = [];
        $templateServices = [];
        if ($documentTemplate->content) {
            try {
                $content = json_decode($documentTemplate->content, true);
                if (is_array($content)) {
                    // Check if it's old format (blocks) or new format (with services)
                    if (isset($content['blocks'])) {
                        $blocks = $content['blocks'];
                        $templateServices = $content['services'] ?? [];
                    } else {
                        $blocks = $content;
                    }
                }
            } catch (\Exception $e) {
                $blocks = [];
            }
        }

        return view('document-templates.builder', [
            'template' => $documentTemplate,
            'services' => $services,
            'organization' => $organization,
            'existingBlocks' => $blocks,
            'templateServices' => $templateServices,
        ]);
    }

    /**
     * Update template from visual builder.
     */
    public function updateBuilder(Request $request, DocumentTemplate $documentTemplate): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'blocks' => 'required|array',
            'services' => 'nullable|array',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Store blocks and services together
        $content = json_encode([
            'blocks' => $validated['blocks'],
            'services' => $validated['services'] ?? [],
        ]);

        $documentTemplate->update([
            'name' => $validated['name'],
            'content' => $content,
            'is_default' => $validated['is_default'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Template saved successfully.'),
                'template' => $documentTemplate->fresh(),
            ]);
        }

        return redirect()
            ->route('settings.document-templates.index')
            ->with('success', __('Template saved successfully.'));
    }
}
