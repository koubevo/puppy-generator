<?php

namespace App\Http\Controllers;

use App\Actions\SendDailyEventAction;
use Illuminate\Http\Request;

class EventCronController extends Controller
{
    public function __construct(
        protected SendDailyEventAction $action
    ) {}

    public function check(Request $request)
    {
        $token = $request->bearerToken();
        $expectedToken = config('app.cron_token');

        if (! is_string($expectedToken) || $expectedToken === '' || ! $token || ! hash_equals($expectedToken, $token)) {
            abort(403);
        }

        $sent = $this->action->execute();

        return response()->json([
            'status' => $sent ? 'executed' : 'idle',
        ]);
    }
}
