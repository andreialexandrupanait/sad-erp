<?php

namespace App\Http\Controllers;

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'default_rate' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'color_class' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $validated['organization_id'] = Auth::user()->organization_id;
        $validated['is_active'] = $request->boolean('is_active', true);

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

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'default_rate' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'color_class' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $service->update($validated);

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
