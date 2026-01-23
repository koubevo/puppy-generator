<?php

namespace App\Actions;

use App\Models\UpdateLog;
use App\Services\BotRegistry;
use Illuminate\Support\Collection;

class GetFeedAction
{
    public function __construct(
        private BotRegistry $botRegistry
    ) {
    }

    /**
     * Get the feed data for display.
     *
     * @return array{logs: Collection, vapidPublicKey: ?string}
     */
    public function execute(): array
    {
        $logs = UpdateLog::query()
            ->where('status', UpdateLog::STATUS_SUCCESS)
            ->latest('sent_at')
            ->take(20)
            ->get()
            ->map(fn(UpdateLog $log) => $this->formatLog($log));

        return [
            'logs' => $logs,
            'vapidPublicKey' => config('webpush.vapid.public_key'),
        ];
    }

    private function formatLog(UpdateLog $log): array
    {
        $bot = $this->botRegistry->get($log->provider ?? 'system');

        return [
            'id' => $log->id,
            'bot' => $bot,
            'message' => $log->payload['message'] ?? $log->payload['text'] ?? 'New update available',
            'imageUrl' => $log->payload['image'] ?? $log->payload['image_url'] ?? null,
            'sentAt' => $sentAt = $log->sent_at ?? $log->created_at,
            'isToday' => $sentAt->isToday(),
            'isYesterday' => $sentAt->isYesterday(),
        ];
    }
}
