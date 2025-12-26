<?php

namespace App\Http\Controllers;

use App\Http\Requests\Service\StoreServiceRequest;
use App\Http\Requests\Service\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::query()
            ->ordered()
            ->withCount('userServices')
            ->get();

        return view('settings.services.index', compact('services'));
    }

    public function create()
    {
        return view('settings.services.create');
    }

    public function edit(Service $service)
    {
        return view('settings.services.edit', compact('service'));
    }

    public function store(StoreServiceRequest $request)
    {
        $validated = $request->validated();
        $validated['organization_id'] = Auth::user()->organization_id;

        $service = Service::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Service created successfully.',
                'service' => $service,
            ]);
        }

        return redirect()->route('settings.services')
            ->with('success', __('Serviciul a fost creat cu succes.'));
    }

    public function update(UpdateServiceRequest $request, Service $service)
    {
        $service->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Serviciul a fost actualizat cu succes.'),
                'service' => $service->fresh(),
            ]);
        }

        return redirect()->route('settings.services')
            ->with('success', __('Serviciul a fost actualizat cu succes.'));
    }

    public function destroy(Service $service)
    {
        // Check if service has user assignments
        if ($service->userServices()->count() > 0) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Nu puteți șterge un serviciu cu utilizatori asignați. Dezactivați-l în schimb.'),
                ], 422);
            }

            return redirect()->route('settings.services')
                ->with('error', __('Nu puteți șterge un serviciu cu utilizatori asignați. Dezactivați-l în schimb.'));
        }

        $service->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Serviciul a fost șters cu succes.'),
            ]);
        }

        return redirect()->route('settings.services')
            ->with('success', __('Serviciul a fost șters cu succes.'));
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:services,id',
        ]);

        foreach ($request->order as $index => $id) {
            Service::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
