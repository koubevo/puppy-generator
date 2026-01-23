<?php

namespace App\Actions;

use App\Models\PushSubscription;

class SubscribePushAction
{
    public function execute(array $data): PushSubscription
    {
        return PushSubscription::updateOrCreate(
            ['endpoint' => $data['endpoint']],
            ['keys' => $data['keys']]
        );
    }
}
