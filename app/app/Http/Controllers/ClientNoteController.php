<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientNote;
use App\Http\Requests\ClientNote\StoreClientNoteRequest;
use App\Http\Requests\ClientNote\UpdateClientNoteRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClientNoteController extends Controller
{
    /**
     * Display a listing of all notes (with filters).
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = ClientNote::with(['client', 'user'])->latest();

        // Apply filters
        if ($request->filled('client_id')) {
            $query->forClient($request->client_id);
        }

        if ($request->filled('tag')) {
            $query->withTag($request->tag);
        }

        if ($request->filled('q')) {
            $query->search($request->q);
        }

        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        $notes = $query->paginate(20)->withQueryString();

        // Get clients for filter dropdown
        $clients = Client::orderBy('name')->get(['id', 'name', 'slug']);

        // Get all available tags
        $availableTags = ClientNote::getAvailableTags();

        if ($request->wantsJson()) {
            return response()->json([
                'notes' => $notes,
                'clients' => $clients,
                'tags' => $availableTags,
            ]);
        }

        return view('client-notes.index', [
            'notes' => $notes,
            'clients' => $clients,
            'availableTags' => $availableTags,
            'filters' => $request->only(['client_id', 'tag', 'q', 'start_date', 'end_date']),
        ]);
    }

    /**
     * Show the form for creating a new note.
     */
    public function create(Request $request): View
    {
        $clients = Client::orderBy('name')->get(['id', 'name', 'slug']);
        $selectedClient = null;

        if ($request->filled('client')) {
            $selectedClient = Client::where('slug', $request->client)->first();
        }

        return view('client-notes.create', [
            'clients' => $clients,
            'selectedClient' => $selectedClient,
        ]);
    }

    /**
     * Store a newly created note in storage.
     */
    public function store(StoreClientNoteRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $note = ClientNote::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Note created successfully.'),
                'note' => $note->load(['client', 'user']),
            ], 201);
        }

        return redirect()
            ->route('client-notes.index', ['client_id' => $note->client_id])
            ->with('success', __('Note created successfully.'));
    }

    /**
     * Display the specified note.
     */
    public function show(ClientNote $clientNote): View|JsonResponse
    {
        $clientNote->load(['client', 'user']);

        if (request()->wantsJson()) {
            return response()->json($clientNote);
        }

        return view('client-notes.show', [
            'note' => $clientNote,
        ]);
    }

    /**
     * Show the form for editing the specified note.
     */
    public function edit(ClientNote $clientNote): View
    {
        $clients = Client::orderBy('name')->get(['id', 'name', 'slug']);

        return view('client-notes.edit', [
            'note' => $clientNote,
            'clients' => $clients,
        ]);
    }

    /**
     * Update the specified note in storage.
     */
    public function update(UpdateClientNoteRequest $request, ClientNote $clientNote): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $clientNote->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Note updated successfully.'),
                'note' => $clientNote->fresh()->load(['client', 'user']),
            ]);
        }

        return redirect()
            ->route('client-notes.index', ['client_id' => $clientNote->client_id])
            ->with('success', __('Note updated successfully.'));
    }

    /**
     * Remove the specified note from storage.
     */
    public function destroy(ClientNote $clientNote): JsonResponse|RedirectResponse
    {
        $clientId = $clientNote->client_id;

        $clientNote->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Note deleted successfully.'),
            ]);
        }

        return redirect()
            ->route('client-notes.index', ['client_id' => $clientId])
            ->with('success', __('Note deleted successfully.'));
    }

    /**
     * Quick update of client assignment (AJAX).
     */
    public function updateClient(Request $request, ClientNote $clientNote): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
        ]);

        $clientNote->update([
            'client_id' => $validated['client_id'] ?: null,
        ]);

        $clientName = $clientNote->client?->name ?? __('No client');

        return response()->json([
            'success' => true,
            'message' => __('Client updated successfully.'),
            'client_name' => $clientName,
            'client_id' => $clientNote->client_id,
        ]);
    }

    /**
     * Get notes for a specific client (AJAX endpoint).
     */
    public function forClient(Client $client, Request $request): JsonResponse
    {
        $query = ClientNote::forClient($client->id)
            ->with('user')
            ->latest();

        if ($request->filled('tag')) {
            $query->withTag($request->tag);
        }

        if ($request->filled('q')) {
            $query->search($request->q);
        }

        $notes = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'notes' => $notes,
            'tags' => ClientNote::getAvailableTags(),
        ]);
    }

    /**
     * Get tag statistics for filtering.
     */
    public function tagStats(Request $request): JsonResponse
    {
        $query = ClientNote::query();

        if ($request->filled('client_id')) {
            $query->forClient($request->client_id);
        }

        // Get tag counts
        $allTags = ClientNote::getAvailableTags();
        $tagCounts = [];

        foreach ($allTags as $tag) {
            $count = (clone $query)->withTag($tag)->count();
            if ($count > 0) {
                $tagCounts[$tag] = $count;
            }
        }

        arsort($tagCounts); // Sort by count descending

        return response()->json($tagCounts);
    }
}
