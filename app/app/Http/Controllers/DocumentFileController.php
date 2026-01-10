<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractAnnex;
use App\Models\DocumentFile;
use App\Services\Document\DocumentFileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentFileController extends Controller
{
    public function __construct(
        protected DocumentFileService $documentFileService
    ) {}

    /**
     * Download a document file
     */
    public function download(DocumentFile $documentFile): StreamedResponse
    {
        $this->authorizeAccess($documentFile);

        return $this->documentFileService->download($documentFile);
    }

    /**
     * View a document file inline (PDF viewer)
     */
    public function view(DocumentFile $documentFile): StreamedResponse
    {
        $this->authorizeAccess($documentFile);

        return $this->documentFileService->view($documentFile);
    }

    /**
     * Upload a signed document for a contract
     */
    public function uploadSignedContract(Request $request, Contract $contract)
    {
        Gate::authorize('update', $contract);

        $request->validate([
            'signed_document' => 'required|file|mimes:pdf|max:20480', // 20MB max
        ]);

        $documentFile = $this->documentFileService->storeSignedUpload(
            $contract,
            $request->file('signed_document')
        );

        // Redirect back with success message
        return back()->with('success', __('Documentul semnat a fost încărcat cu succes.'));
    }

    /**
     * Upload a signed document for a contract annex
     */
    public function uploadSignedAnnex(Request $request, Contract $contract, ContractAnnex $annex)
    {
        Gate::authorize('update', $contract);

        // Ensure annex belongs to contract
        if ($annex->contract_id !== $contract->id) {
            abort(404);
        }

        $request->validate([
            'signed_document' => 'required|file|mimes:pdf|max:20480', // 20MB max
        ]);

        $documentFile = $this->documentFileService->storeSignedUpload(
            $annex,
            $request->file('signed_document')
        );

        // Redirect back with success message
        return back()->with('success', __('Documentul semnat a fost încărcat cu succes.'));
    }

    /**
     * Get document versions for a contract
     */
    public function contractVersions(Contract $contract, string $type)
    {
        Gate::authorize('view', $contract);

        if (!in_array($type, ['draft', 'signed'])) {
            abort(400, 'Invalid document type');
        }

        $versions = $this->documentFileService->getAllVersions($contract, $type);

        return response()->json([
            'versions' => $versions->map(fn ($doc) => [
                'id' => $doc->id,
                'uuid' => $doc->uuid,
                'version' => $doc->version,
                'is_active' => $doc->is_active,
                'file_size' => $doc->file_size_human,
                'created_at' => $doc->created_at->format('d.m.Y H:i'),
                'download_url' => $doc->download_url,
                'view_url' => $doc->url,
            ]),
        ]);
    }

    /**
     * Get document versions for an annex
     */
    public function annexVersions(Contract $contract, ContractAnnex $annex, string $type)
    {
        Gate::authorize('view', $contract);

        if ($annex->contract_id !== $contract->id) {
            abort(404);
        }

        if (!in_array($type, ['draft', 'signed'])) {
            abort(400, 'Invalid document type');
        }

        $versions = $this->documentFileService->getAllVersions($annex, $type);

        return response()->json([
            'versions' => $versions->map(fn ($doc) => [
                'id' => $doc->id,
                'uuid' => $doc->uuid,
                'version' => $doc->version,
                'is_active' => $doc->is_active,
                'file_size' => $doc->file_size_human,
                'created_at' => $doc->created_at->format('d.m.Y H:i'),
                'download_url' => $doc->download_url,
                'view_url' => $doc->url,
            ]),
        ]);
    }

    /**
     * Authorize access to a document file
     */
    protected function authorizeAccess(DocumentFile $documentFile): void
    {
        $documentable = $documentFile->documentable;

        if ($documentable instanceof Contract) {
            Gate::authorize('view', $documentable);
        } elseif ($documentable instanceof ContractAnnex) {
            Gate::authorize('view', $documentable->contract);
        } else {
            // For offers, check the offer policy
            Gate::authorize('view', $documentable);
        }
    }
}
