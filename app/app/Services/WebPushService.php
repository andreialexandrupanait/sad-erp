<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class WebPushService
{
    protected ?WebPush $webPush = null;

    public function __construct()
    {
        $this->initWebPush();
    }

    protected function initWebPush(): void
    {
        $publicKey = config('webpush.vapid.public_key');
        $privateKey = config('webpush.vapid.private_key');
        $subject = config('webpush.vapid.subject');

        if (!$publicKey || !$privateKey) {
            Log::warning('WebPush VAPID keys not configured');
            return;
        }

        $auth = [
            'VAPID' => [
                'subject' => $subject ?? config('app.url'),
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
            ],
        ];

        try {
            $this->webPush = new WebPush($auth);
            $this->webPush->setReuseVAPIDHeaders(true);
        } catch (\Exception $e) {
            Log::error('Failed to initialize WebPush', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Send notification to all subscribers in an organization.
     */
    public function sendToOrganization(int $organizationId, array $payload): void
    {
        if (!$this->webPush) {
            return;
        }

        $subscriptions = PushSubscription::active()
            ->forOrganization($organizationId)
            ->get();

        foreach ($subscriptions as $subscription) {
            $this->queueNotification($subscription, $payload);
        }

        $this->flush();
    }

    /**
     * Send notification to a specific user.
     */
    public function sendToUser(int $userId, array $payload): void
    {
        if (!$this->webPush) {
            return;
        }

        $subscriptions = PushSubscription::active()
            ->where('user_id', $userId)
            ->get();

        foreach ($subscriptions as $subscription) {
            $this->queueNotification($subscription, $payload);
        }

        $this->flush();
    }

    /**
     * Queue a notification for sending.
     */
    protected function queueNotification(PushSubscription $subscription, array $payload): void
    {
        $pushSubscription = Subscription::create([
            'endpoint' => $subscription->endpoint,
            'publicKey' => $subscription->p256dh_key,
            'authToken' => $subscription->auth_key,
            'contentEncoding' => $subscription->content_encoding,
        ]);

        $this->webPush->queueNotification(
            $pushSubscription,
            json_encode($payload)
        );
    }

    /**
     * Flush queued notifications.
     */
    protected function flush(): void
    {
        try {
            foreach ($this->webPush->flush() as $report) {
                $endpoint = $report->getRequest()->getUri()->__toString();

                if ($report->isSuccess()) {
                    Log::debug('Push notification sent successfully', ['endpoint' => $endpoint]);
                } else {
                    Log::warning('Push notification failed', [
                        'endpoint' => $endpoint,
                        'reason' => $report->getReason(),
                    ]);

                    // If subscription is expired or invalid, deactivate it
                    if ($report->isSubscriptionExpired()) {
                        PushSubscription::where('endpoint', $endpoint)
                            ->update(['is_active' => false]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send push notifications', ['error' => $e->getMessage()]);
        }
    }
}
