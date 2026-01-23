<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    private ?WebPush $webPush = null;

    public function __construct()
    {
        $publicKey = config('webpush.vapid.public_key');
        $privateKey = config('webpush.vapid.private_key');

        if ($publicKey && $privateKey) {
            $this->webPush = new WebPush([
                'VAPID' => [
                    'subject' => config('app.url'),
                    'publicKey' => $publicKey,
                    'privateKey' => $privateKey,
                ],
            ]);
        }
    }

    public function sendToAll(string $title, string $body, ?string $url = null): int
    {
        if (! $this->webPush) {
            return 0;
        }

        $subscriptions = PushSubscription::all();

        if ($subscriptions->isEmpty()) {
            return 0;
        }

        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'url' => $url ?? '/feed',
        ]);

        foreach ($subscriptions as $subscription) {
            $this->webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $subscription->endpoint,
                    'keys' => $subscription->keys,
                ]),
                $payload
            );
        }

        $sent = 0;

        foreach ($this->webPush->flush() as $report) {
            if ($report->isSuccess()) {
                $sent++;
            } else {
                $endpoint = $report->getEndpoint();

                Log::warning('Push notification failed', [
                    'endpoint' => $endpoint,
                    'reason' => $report->getReason(),
                ]);

                if ($report->isSubscriptionExpired()) {
                    PushSubscription::where('endpoint', $endpoint)->delete();
                }
            }
        }

        return $sent;
    }
}
