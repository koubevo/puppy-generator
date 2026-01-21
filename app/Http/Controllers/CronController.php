<?php

namespace App\Http\Controllers;

use App\Actions\SendDailyUpdateAction;
use App\Services\TaskSchedulerService;

class CronController extends Controller
{
    public function __construct(
        protected TaskSchedulerService $scheduler,
        protected SendDailyUpdateAction $action
    ) {
    }

    public function wakeUp(string $token)
    {
        if (!hash_equals(config('app.cron_token'), $token)) {
            abort(403);
        }

        $task = $this->scheduler->getTaskIfReady('puppy_daily');

        if ($task) {
            $this->action->execute($task);
            return response()->json(['status' => 'executed']);
        }

        return response()->json(['status' => 'idle']);
    }
}
