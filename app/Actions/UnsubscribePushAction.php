<?php

namespace App\Actions;

use App\Models\PushSubscription;

class UnsubscribePushAction
{
    public function execute(string $endpoint): bool
    {
        return PushSubscription::where('endpoint', $endpoint)->delete() > 0;
    }
}
