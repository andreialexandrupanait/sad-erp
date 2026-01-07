<?php

namespace App\Http\Controllers;

use App\Models\FinancialFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ShareController extends Controller
{
    /**
     * Display a shared invoice file (public access via signed URL).
     *
     * @param Request $request
     * @param FinancialFile $file
     * @return Response
     */
    public function invoice(Request $request, FinancialFile $file)
    {
        // Validate the signed URL
        if (!$request->hasValidSignature()) {
            abort(403, __('This link has expired or is invalid.'));
        }

        // Check if file exists
        if (!Storage::disk($file->disk)->exists($file->path)) {
            abort(404, __('File not found.'));
        }

        // Get the file content
        $content = Storage::disk($file->disk)->get($file->path);
        $mimeType = Storage::disk($file->disk)->mimeType($file->path);

        // Return inline response (display in browser)
        return response($content, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $file->original_name . '"',
        ]);
    }

    /**
     * Download a shared invoice file (public access via signed URL).
     *
     * @param Request $request
     * @param FinancialFile $file
     * @return Response
     */
    public function download(Request $request, FinancialFile $file)
    {
        // Validate the signed URL
        if (!$request->hasValidSignature()) {
            abort(403, __('This link has expired or is invalid.'));
        }

        // Check if file exists
        if (!Storage::disk($file->disk)->exists($file->path)) {
            abort(404, __('File not found.'));
        }

        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }
}
