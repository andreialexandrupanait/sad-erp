<?php

namespace App\Http\Controllers;

use App\Models\ContractTemplate;
use App\Services\VariableRegistry;
use Barryvdh\DomPDF\Facade\Pdf;
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

        // If setting as default, unset other defaults in the same category
        if ($validated['is_default']) {
            ContractTemplate::where('is_default', true)
                ->where('category', $validated['category'])
                ->update(['is_default' => false]);
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

        // If setting as default, unset other defaults in the same category
        if ($validated['is_default'] && !$contractTemplate->is_default) {
            $category = $validated['category'] ?? $contractTemplate->category;
            ContractTemplate::where('is_default', true)
                ->where('category', $category)
                ->update(['is_default' => false]);
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
        // Only unset defaults in the same category
        ContractTemplate::where('is_default', true)
            ->where('category', $contractTemplate->category)
            ->update(['is_default' => false]);
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

    /**
     * Preview the template as PDF with sample/mock data.
     */
    public function preview(Request $request, ContractTemplate $contractTemplate)
    {
        // Get content from request (current editor content) or use saved content
        $content = $request->input('content', $contractTemplate->content);

        // Replace variables with sample data
        $content = $this->replaceVariablesWithSampleData($content, $contractTemplate->category);

        // Determine which PDF view to use based on category
        $view = $contractTemplate->category === 'annex' ? 'contracts.annex-pdf' : 'contracts.pdf';

        // Create mock data for the view
        $mockData = $this->createMockDataForPreview($contractTemplate->category);

        // Generate PDF
        $pdf = Pdf::loadView($view, array_merge($mockData, [
            'content' => $content,
        ]));
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('defaultFont', 'DejaVu Sans');

        // Return as inline PDF for browser viewing
        return response($pdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $contractTemplate->name . '-preview.pdf"')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Replace variables with sample/mock data for preview.
     */
    protected function replaceVariablesWithSampleData(string $content, ?string $category = null): string
    {
        $sampleData = [
            // Client variables
            'client_company_name' => 'SC Exemplu Client SRL',
            'client_address' => 'Str. Exemplu nr. 123, București, Sector 1',
            'client_trade_register_number' => 'J40/1234/2020',
            'client_tax_id' => 'RO12345678',
            'client_bank_account' => 'RO49AAAA1B31007593840000',
            'client_representative' => 'Ion Popescu',
            'client_email' => 'contact@exemplu-client.ro',
            'client_phone' => '+40 712 345 678',

            // Contract variables
            'contract_number' => 'CTR-2026-001',
            'contract_date' => now()->format('d.m.Y'),
            'contract_start_date' => now()->format('d.m.Y'),
            'contract_end_date' => now()->addYear()->format('d.m.Y'),
            'contract_total' => '5.000,00',
            'contract_currency' => 'EUR',
            'contract_title' => 'Contract de prestări servicii',

            // Organization variables
            'org_name' => config('app.name', 'Compania Mea SRL'),
            'org_address' => 'Str. Firmei nr. 1, București',
            'org_tax_id' => 'RO87654321',
            'org_trade_register' => 'J40/5678/2015',
            'org_representative' => 'Administrator Firma',
            'org_bank_account' => 'RO49BBBB1B31007593840000 - Banca Exemplu',
            'org_email' => config('mail.from.address', 'office@firma.ro'),
            'org_phone' => '+40 21 123 4567',

            // Special variables
            'services_list' => '<p style="margin: 0 0 4px 20px;">&bull; <strong>Serviciu 1</strong> - 1.000,00 EUR</p><p style="margin: 0 0 4px 20px;">&bull; <strong>Serviciu 2</strong> - 2.000,00 EUR</p><p style="margin: 0 0 4px 20px;">&bull; <strong>Serviciu 3</strong> - 2.000,00 EUR</p><p style="margin-top: 10px;"><strong>Total: 5.000,00 EUR</strong></p>',
            'offer_services_list' => '<p style="margin: 0 0 4px 20px;">&bull; <strong>Serviciu 1</strong> - 1.000,00 EUR</p><p style="margin: 0 0 4px 20px;">&bull; <strong>Serviciu 2</strong> - 2.000,00 EUR</p><p style="margin: 0 0 4px 20px;">&bull; <strong>Serviciu 3</strong> - 2.000,00 EUR</p><p style="margin-top: 10px;"><strong>Total: 5.000,00 EUR</strong></p>',
            'current_date' => now()->format('d.m.Y'),

            // Annex variables
            'annex_number' => '1',
            'annex_code' => 'AA-CTR-2026-001-01',
            'annex_date' => now()->format('d.m.Y'),
            'annex_title' => 'Act adițional nr. 1',
            'annex_value' => '1.500,00',
            'parent_contract_number' => 'CTR-2026-001',
            'parent_contract_date' => now()->subMonth()->format('d.m.Y'),
            'new_contract_total' => '6.500,00',
            'annex_services_list' => '<p style="margin: 0 0 4px 20px;">&bull; <strong>Serviciu suplimentar</strong> - 1.500,00 EUR</p>',
        ];

        foreach ($sampleData as $key => $value) {
            $content = str_replace('{{' . $key . '}}', (string) $value, $content);
        }

        return $content;
    }

    /**
     * Create mock data objects for preview PDF view.
     */
    protected function createMockDataForPreview(string $category): array
    {
        if ($category === 'annex') {
            // Create mock annex and contract objects
            $mockContract = new \stdClass();
            $mockContract->contract_number = 'CTR-2026-001';
            $mockContract->title = 'Contract de prestări servicii';
            $mockContract->client = new \stdClass();
            $mockContract->client->display_name = 'SC Exemplu Client SRL';
            $mockContract->client->company_name = 'SC Exemplu Client SRL';
            $mockContract->client->full_address = 'Str. Exemplu nr. 123, București';
            $mockContract->client->fiscal_code = 'RO12345678';

            $mockAnnex = new \stdClass();
            $mockAnnex->annex_code = 'AA-CTR-2026-001-01';
            $mockAnnex->annex_number = 1;
            $mockAnnex->effective_date = now();
            $mockAnnex->title = 'Act adițional nr. 1';
            $mockAnnex->value = 1500.00;
            $mockAnnex->currency = 'EUR';
            $mockAnnex->description = null;
            $mockAnnex->offer = null;

            return [
                'annex' => $mockAnnex,
                'contract' => $mockContract,
            ];
        }

        // Mock contract for contract/offer templates
        $mockContract = new \stdClass();
        $mockContract->contract_number = 'CTR-2026-001';
        $mockContract->pdf_content = null;
        $mockContract->content = null;

        return [
            'contract' => $mockContract,
        ];
    }
}
