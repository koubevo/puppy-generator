<?php

namespace App\Actions;

use App\Models\UpdateLog;
use App\Services\WebPushService;
use Illuminate\Support\Facades\Log;

class SendDailyEventAction
{
    public function __construct(
        private WebPushService $webPushService
    ) {
    }

    public function execute(): bool
    {
        $events = config('events.daily');

        if (!is_array($events)) {
            Log::warning('No valid events configuration found.');

            return false;
        }

        $todayMonthDay = now()->format('m-d');
        $todayDay = now()->format('d');

        $matchedEvent = null;

        foreach ($events as $event) {
            $date = $event['date'] ?? null;
            if ($date === $todayMonthDay || $date === $todayDay) {
                $matchedEvent = $event;
                break;
            }
        }

        if (!$matchedEvent) {
            return false;
        }

        try {
            $message = $matchedEvent['message'];

            $payload = [
                'message' => $message,
            ];

            $this->createUpdateLog($payload);
            $this->sendPushNotification($message);

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send daily event', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function createUpdateLog(array $payload): void
    {
        UpdateLog::create([
            'provider' => 'announcements',
            'status' => UpdateLog::STATUS_SUCCESS,
            'payload' => $payload,
            'sent_at' => now(),
        ]);
    }

    private function sendPushNotification(string $message): void
    {
        try {
            $this->webPushService->sendToAll('Puppy', $message);
        } catch (\Throwable $e) {
            Log::warning('Failed to send push notifications for event', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
