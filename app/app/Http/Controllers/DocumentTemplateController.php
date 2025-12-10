<?php

namespace App\Http\Controllers;

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
     */
    private function indexJson(Request $request): JsonResponse
    {
        $query = DocumentTemplate::query();

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Active filter
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $templates = $query->orderBy('type')->orderBy('name')->get();

        return response()->json([
            'templates' => $templates->map(function ($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'type' => $template->type,
                    'type_label' => $template->type_label,
                    'is_default' => $template->is_default,
                    'is_active' => $template->is_active,
                    'created_at' => $template->created_at?->toISOString(),
                    'updated_at' => $template->updated_at?->toISOString(),
                ];
            }),
            'types' => DocumentTemplate::getTypes(),
        ]);
    }

    /**
     * Show the form for creating a new template.
     */
    public function create(Request $request): View
    {
        $types = DocumentTemplate::getTypes();
        $selectedType = $request->get('type', 'offer');

        // Get available variables for preview
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
    public function show(DocumentTemplate $documentTemplate): View
    {
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
    public function destroy(DocumentTemplate $documentTemplate): RedirectResponse
    {
        // Check if template is in use
        $offersCount = $documentTemplate->offers()->count();
        $contractsCount = $documentTemplate->contracts()->count();

        if ($offersCount > 0 || $contractsCount > 0) {
            return back()->with('error', __('Cannot delete template that is in use.'));
        }

        $documentTemplate->delete();

        return redirect()
            ->route('document-templates.index')
            ->with('success', __('Template deleted successfully.'));
    }

    /**
     * Duplicate a template.
     */
    public function duplicate(DocumentTemplate $documentTemplate): RedirectResponse
    {
        $newTemplate = $documentTemplate->replicate(['is_default']);
        $newTemplate->name = $documentTemplate->name . ' (' . __('Copy') . ')';
        $newTemplate->is_default = false;
        $newTemplate->save();

        return redirect()
            ->route('document-templates.edit', $newTemplate)
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
