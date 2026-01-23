<?php

namespace App\Actions;

use App\Contracts\ContentProvider;
use App\Models\Task;
use App\Models\UpdateLog;
use App\Services\WebPushService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendDailyUpdateAction
{
    public function __construct(
        private ContentProvider $contentProvider,
        private WebPushService $webPushService
    ) {}

    public function execute(Task $task): bool
    {
        try {
            return DB::transaction(function () use ($task) {
                $payload = $this->contentProvider->getPayload();

                $this->createUpdateLog(
                    providerName: $this->contentProvider->getProviderName(),
                    payload: $payload
                );

                $this->updateTaskLastRun($task);
                $this->sendPushNotifications($payload);

                return true;
            });
        } catch (\Throwable $e) {
            Log::error('Failed to send daily update', [
                'task_id' => $task->id,
                'task_name' => $task->name,
                'provider' => $this->contentProvider->getProviderName(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            try {
                UpdateLog::create([
                    'provider' => $this->contentProvider->getProviderName(),
                    'status' => UpdateLog::STATUS_FAILED,
                    'payload' => [],
                    'error_message' => $e->getMessage(),
                    'sent_at' => now(),
                ]);
            } catch (\Throwable $logException) {
                Log::error('Failed to create error log', [
                    'error' => $logException->getMessage(),
                ]);
            }

            return false;
        }
    }

    private function createUpdateLog(string $providerName, array $payload): void
    {
        UpdateLog::create([
            'provider' => $providerName,
            'status' => UpdateLog::STATUS_SUCCESS,
            'payload' => $payload,
            'sent_at' => now(),
        ]);
    }

    private function updateTaskLastRun(Task $task): void
    {
        $task->last_run_at = now();
        $task->save();
    }

    private function sendPushNotifications(array $payload): void
    {
        try {
            $message = $payload['message'] ?? $payload['text'] ?? 'New update available!';
            $title = ucfirst($this->contentProvider->getProviderName()).' Update';

            $this->webPushService->sendToAll($title, $message);
        } catch (\Throwable $e) {
            Log::warning('Failed to send push notifications', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
