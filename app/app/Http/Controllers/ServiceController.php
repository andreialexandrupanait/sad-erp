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
            ->with('success', 'Service created successfully.');
    }

    public function update(UpdateServiceRequest $request, Service $service)
    {
        $service->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Service updated successfully.',
                'service' => $service->fresh(),
            ]);
        }

        return redirect()->route('settings.services')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        // Check if service has user assignments
        if ($service->userServices()->count() > 0) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete service with active user assignments. Deactivate it instead.',
                ], 422);
            }

            return redirect()->route('settings.services')
                ->with('error', 'Cannot delete service with active user assignments. Deactivate it instead.');
        }

        $service->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Service deleted successfully.',
            ]);
        }

        return redirect()->route('settings.services')
            ->with('success', 'Service deleted successfully.');
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
