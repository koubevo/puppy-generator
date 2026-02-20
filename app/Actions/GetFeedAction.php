<?php

namespace App\Actions;

use App\Models\UpdateLog;
use App\Services\BotRegistry;
use App\Traits\ExtractsImageUrl;
use Illuminate\Support\Collection;

class GetFeedAction
{
    use ExtractsImageUrl;

    public function __construct(
        private BotRegistry $botRegistry
    ) {}

    /**
     * Get the feed data for display.
     *
     * @return array{logs: Collection, hasMore: bool, vapidPublicKey: ?string}
     */
    public function execute(?int $beforeId = null, int $limit = 6): array
    {
        $query = UpdateLog::query()
            ->where('status', UpdateLog::STATUS_SUCCESS)
            ->latest('sent_at');

        if ($beforeId !== null) {
            $query->where('id', '<', $beforeId);
        }

        $logs = $query
            ->take($limit + 1)
            ->get();

        $hasMore = $logs->count() > $limit;

        if ($hasMore) {
            $logs = $logs->take($limit);
        }

        $formattedLogs = $logs->map(fn (UpdateLog $log) => $this->formatLog($log));

        return [
            'logs' => $formattedLogs,
            'hasMore' => $hasMore,
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
            'imageUrl' => $this->extractImageUrl($log->payload),
            'sentAt' => $sentAt = $log->sent_at ?? $log->created_at,
            'isToday' => $sentAt->isToday(),
            'isYesterday' => $sentAt->isYesterday(),
        ];
    }
}
