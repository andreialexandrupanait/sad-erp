<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get VAPID public key for push subscription.
     */
    public function vapidPublicKey(): JsonResponse
    {
        return response()->json([
            'publicKey' => config('webpush.vapid.public_key'),
        ]);
    }

    /**
     * Subscribe to push notifications.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|url',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        $user = auth()->user();

        // Check if subscription already exists using hash
        $existing = PushSubscription::findByEndpoint($validated['endpoint']);
        
        if ($existing) {
            // Update existing subscription
            $existing->update([
                'user_id' => $user->id,
                'organization_id' => $user->organization_id,
                'p256dh_key' => $validated['keys']['p256dh'],
                'auth_key' => $validated['keys']['auth'],
                'is_active' => true,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => __('Push subscription updated.'),
            ]);
        }

        // Create new subscription
        PushSubscription::create([
            'user_id' => $user->id,
            'organization_id' => $user->organization_id,
            'endpoint' => $validated['endpoint'],
            'p256dh_key' => $validated['keys']['p256dh'],
            'auth_key' => $validated['keys']['auth'],
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Push notifications enabled.'),
        ]);
    }

    /**
     * Unsubscribe from push notifications.
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|url',
        ]);

        $endpointHash = hash('sha256', $validated['endpoint']);
        
        $deleted = PushSubscription::where('endpoint_hash', $endpointHash)
            ->where('user_id', auth()->id())
            ->delete();

        return response()->json([
            'success' => $deleted > 0,
            'message' => $deleted > 0 
                ? __('Push notifications disabled.') 
                : __('Subscription not found.'),
        ]);
    }

    /**
     * Check subscription status.
     */
    public function status(Request $request): JsonResponse
    {
        $endpoint = $request->query('endpoint');
        
        if (!$endpoint) {
            return response()->json(['subscribed' => false]);
        }

        $endpointHash = hash('sha256', $endpoint);
        
        $subscription = PushSubscription::where('endpoint_hash', $endpointHash)
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->exists();

        return response()->json(['subscribed' => $subscription]);
    }
}
