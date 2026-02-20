<?php

namespace App\Http\Controllers;

use App\Actions\SendDailyUpdateAction;
use App\Services\TaskSchedulerService;
use Illuminate\Http\Request;

class CronController extends Controller
{
    public function __construct(
        protected TaskSchedulerService $scheduler,
        protected SendDailyUpdateAction $action
    ) {}

    public function wakeUp(Request $request, string $taskName)
    {
        $token = $request->bearerToken();
        $expectedToken = config('app.cron_token');

        if (! is_string($expectedToken) || $expectedToken === '' || ! $token || ! hash_equals($expectedToken, $token)) {
            abort(403);
        }

        $task = $this->scheduler->getTaskIfReady($taskName);

        if ($task) {
            $this->action->execute($task);

            return response()->json(['status' => 'executed']);
        }

        return response()->json(['status' => 'idle']);
    }
}
