<?php

namespace App\Actions;

use App\Models\UpdateLog;
use App\Traits\ExtractsImageUrl;
use Illuminate\Support\Collection;

class GetGalleryAction
{
    use ExtractsImageUrl;

    /**
     * Get the gallery data for display.
     *
     * @return array{logs: Collection, hasMore: bool}
     */
    public function execute(?int $beforeId = null, int $limit = 12): array
    {
        $query = UpdateLog::query()
            ->where('status', UpdateLog::STATUS_SUCCESS)
            ->where(function ($q) {
                $q->whereNotNull('payload->image_url')
                    ->orWhereNotNull('payload->image');
            })
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

        $formattedLogs = $logs->map(fn (UpdateLog $log) => $this->formatLog($log))
            ->filter(fn (array $log) => $log['imageUrl'] !== null)
            ->values();

        return [
            'logs' => $formattedLogs,
            'hasMore' => $hasMore,
        ];
    }

    private function formatLog(UpdateLog $log): array
    {
        $imageUrl = $this->extractImageUrl($log->payload);
        $thumbnailUrl = $imageUrl;

        // Only thumbnail if it's a data URI (base64)
        if ($imageUrl && str_starts_with($imageUrl, 'data:')) {
            $thumbnailUrl = $this->createThumbnailBase64($imageUrl, 400);
        }

        return [
            'id' => $log->id,
            'imageUrl' => $thumbnailUrl,
            'fullImageUrl' => $imageUrl,
            'message' => $log->payload['message'] ?? $log->payload['text'] ?? '',
            'sentAt' => $log->sent_at ?? $log->created_at,
        ];
    }
}
