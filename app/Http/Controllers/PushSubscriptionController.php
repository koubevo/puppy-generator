<?php

namespace App\Http\Controllers;

use App\Actions\SubscribePushAction;
use App\Actions\UnsubscribePushAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request, SubscribePushAction $action): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'url', 'max:500'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
        ]);

        $action->execute($validated);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, UnsubscribePushAction $action): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'url'],
        ]);

        $action->execute($validated['endpoint']);

        return response()->json(['success' => true]);
    }
}
