<?php

namespace App\Services;

use App\Models\Task;
use InvalidArgumentException;

class TaskSchedulerService
{
    public function getTaskIfReady(string $taskName): ?Task
    {
        $this->validateTaskName($taskName);

        $task = Task::firstOrCreate(
            ['name' => $taskName]
        );
        $now = now();

        if (!$task->next_run_at || $task->next_run_at->isBefore($now->startOfDay())) {
            $task->update([
                'next_run_at' => now()->setHour(rand(9, 12))->setMinute(rand(0, 59)),
            ]);

            return null;
        }

        $isTime = $now->greaterThanOrEqualTo($task->next_run_at);
        $notSentYet = !$task->last_run_at || !$task->last_run_at->isToday();

        return ($isTime && $notSentYet) ? $task : null;
    }

    private function validateTaskName(string $taskName): void
    {
        $allowedTasks = config('tasks.allowed', []);

        if (!in_array($taskName, $allowedTasks, true)) {
            throw new InvalidArgumentException(
                "Task '{$taskName}' is not allowed. Allowed tasks: " . implode(', ', $allowedTasks)
            );
        }
    }
}
