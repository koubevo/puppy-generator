<?php

namespace App\Actions;

use App\Contracts\ContentProvider;
use App\Contracts\MessageTransport;
use App\Models\Task;
use App\Models\UpdateLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SendDailyUpdateAction
{
    public function __construct(
        private ContentProvider $contentProvider,
        private MessageTransport $messageTransport
    ) {
    }

    public function execute(Task $task, string $providerName, string $transportName): bool
    {
        try {
            return DB::transaction(function () use ($task, $providerName, $transportName) {
                $payload = $this->contentProvider->getPayload();

                $success = $this->messageTransport->send($payload);

                $this->createUpdateLog(
                    providerName: $providerName,
                    transportName: $transportName,
                    payload: $payload,
                    success: $success,
                    errorMessage: null
                );

                if ($success) {
                    $this->updateTask($task);
                }

                return $success;
            });
        } catch (Exception $e) {
            Log::error('Failed to send daily update', [
                'task_id' => $task->id,
                'task_name' => $task->name,
                'provider' => $providerName,
                'transport' => $transportName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            try {
                $this->createUpdateLog(
                    providerName: $providerName,
                    transportName: $transportName,
                    payload: [],
                    success: false,
                    errorMessage: $e->getMessage()
                );
            } catch (Exception $logException) {
                Log::error('Failed to create error log', [
                    'error' => $logException->getMessage(),
                ]);
            }

            return false;
        }
    }

    private function createUpdateLog(
        string $providerName,
        string $transportName,
        array $payload,
        bool $success,
        ?string $errorMessage
    ): void {
        UpdateLog::create([
            'provider' => $providerName,
            'transport' => $transportName,
            'status' => $success ? 'success' : 'failed',
            'payload' => $payload,
            'error_message' => $errorMessage,
            'sent_at' => now(),
        ]);
    }

    private function updateTask(Task $task): void
    {
        $task->last_run_at = now();
        $task->next_run_at = now()->addDay();
        $task->save();
    }
}
