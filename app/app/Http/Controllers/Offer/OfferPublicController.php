<?php

namespace App\Http\Controllers\Offer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\SafeJsonResponse;
use App\Models\Offer;
use App\Services\Offer\OfferPublicService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Handles public-facing offer operations.
 * These endpoints are accessible without authentication via share tokens.
 */
class OfferPublicController extends Controller
{
    use SafeJsonResponse;

    public function __construct(
        protected OfferPublicService $offerPublicService
    ) {}

    /**
     * Public view for client (token-based).
     */
    public function view(string $token): View
    {
        $offer = $this->offerPublicService->getOfferByToken($token);

        $this->offerPublicService->recordView($offer, request()->ip(), request()->userAgent());

        $offer->load(['items']);
        $offer->setRelation('client', $offer->client_id
            ? \App\Models\Client::where('id', $offer->client_id)
                ->where('organization_id', $offer->organization_id)
                ->first()
            : null);
        $offer->setRelation('organization', \App\Models\Organization::find($offer->organization_id));

        return view('offers.public', compact('offer'));
    }

    /**
     * Public API endpoint to get current offer state.
     */
    public function state(string $token): JsonResponse
    {
        try {
            return response()->json($this->offerPublicService->getPublicState($token));
        } catch (\Exception $e) {
            return $this->safeJsonError($e, 'Get offer state', 404);
        }
    }

    /**
     * Public API endpoint to update customer's service selections.
     */
    public function updateSelections(Request $request, string $token): JsonResponse
    {
        $validated = $request->validate([
            'deselected_services' => 'array',
            'deselected_services.*' => 'integer',
            'selected_cards' => 'array',
            'selected_cards.*' => 'integer',
            'selected_optional_services' => 'array',
            'selected_optional_services.*' => 'string',
        ]);

        try {
            $result = $this->offerPublicService->updateSelections($token, $validated);
            return response()->json($result);
        } catch (\RuntimeException $e) {
            return $this->safeJsonValidationError(__('This action is not allowed.'), [], 403);
        } catch (\Exception $e) {
            return $this->safeJsonError($e, 'Update offer selections');
        }
    }

    /**
     * Public accept action (token-based).
     */
    public function accept(Request $request, string $token): JsonResponse|RedirectResponse
    {
        try {
            $this->offerPublicService->acceptPublic(
                $token,
                $request->verification_code,
                $request->ip()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Offer accepted successfully.'),
                ]);
            }

            return redirect()->back()->with('success', __('Offer accepted successfully.'));
        } catch (\Exception $e) {
            return $this->respondException($e, 'Accept offer');
        }
    }

    /**
     * Public reject action (token-based).
     */
    public function reject(Request $request, string $token): JsonResponse|RedirectResponse
    {
        try {
            $this->offerPublicService->rejectPublic(
                $token,
                $request->rejection_reason,
                $request->ip()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Offer declined successfully.'),
                ]);
            }

            return redirect()->back()->with('success', __('Offer declined successfully.'));
        } catch (\Exception $e) {
            return $this->respondException($e, 'Reject offer');
        }
    }

    /**
     * Request verification code for public offer.
     */
    public function requestVerificationCode(string $token): JsonResponse
    {
        try {
            $this->offerPublicService->sendVerificationCode($token);

            return response()->json([
                'success' => true,
                'message' => __('Verification code sent to your email.'),
            ]);
        } catch (\Exception $e) {
            return $this->safeJsonError($e, 'Request verification code', 400);
        }
    }

    /**
     * Public view for signed URL access.
     */
    public function viewSigned(Offer $offer): View
    {
        $this->offerPublicService->recordView($offer, request()->ip(), request()->userAgent());

        $offer->load(['items']);
        $offer->setRelation('client', $offer->client_id
            ? \App\Models\Client::where('id', $offer->client_id)
                ->where('organization_id', $offer->organization_id)
                ->first()
            : null);
        $offer->setRelation('organization', \App\Models\Organization::find($offer->organization_id));

        return view('offers.public', compact('offer'));
    }

    /**
     * Public state for signed URL access.
     */
    public function stateSigned(Offer $offer): JsonResponse
    {
        try {
            return response()->json($this->offerPublicService->getPublicStateSigned($offer));
        } catch (\Exception $e) {
            return $this->safeJsonError($e, 'Get signed offer state', 404);
        }
    }

    /**
     * Update selections for signed URL access.
     */
    public function updateSelectionsSigned(Request $request, Offer $offer): JsonResponse
    {
        $validated = $request->validate([
            'deselected_services' => 'array',
            'deselected_services.*' => 'integer',
            'selected_cards' => 'array',
            'selected_cards.*' => 'integer',
            'selected_optional_services' => 'array',
            'selected_optional_services.*' => 'string',
        ]);

        try {
            $result = $this->offerPublicService->updateSelectionsSigned($offer, $validated);
            return response()->json($result);
        } catch (\RuntimeException $e) {
            return $this->safeJsonValidationError(__('This action is not allowed.'), [], 403);
        } catch (\Exception $e) {
            return $this->safeJsonError($e, 'Update signed offer selections');
        }
    }

    /**
     * Accept for signed URL access.
     */
    public function acceptSigned(Request $request, Offer $offer): JsonResponse|RedirectResponse
    {
        try {
            $this->offerPublicService->acceptSigned($offer, $request->ip());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Offer accepted successfully.'),
                ]);
            }

            return redirect()->back()->with('success', __('Offer accepted successfully.'));
        } catch (\Exception $e) {
            return $this->respondException($e, 'Accept signed offer');
        }
    }

    /**
     * Reject for signed URL access.
     */
    public function rejectSigned(Request $request, Offer $offer): JsonResponse|RedirectResponse
    {
        try {
            $this->offerPublicService->rejectSigned(
                $offer,
                $request->rejection_reason,
                $request->ip()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Offer declined successfully.'),
                ]);
            }

            return redirect()->back()->with('success', __('Offer declined successfully.'));
        } catch (\Exception $e) {
            return $this->respondException($e, 'Reject signed offer');
        }
    }
}
