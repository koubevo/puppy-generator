<?php

namespace App\Http\Controllers;

use App\Actions\GetFeedAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index(GetFeedAction $action): View
    {
        $data = $action->execute();

        return view('feed', $data);
    }

    public function more(Request $request, GetFeedAction $action): JsonResponse
    {
        $data = $action->execute(
            beforeId: $request->integer('before'),
            limit: 6,
        );

        $html = '';
        foreach ($data['logs'] as $log) {
            $html .= view('partials._feed-item', ['log' => $log])->render();
        }

        return response()->json([
            'html' => $html,
            'hasMore' => $data['hasMore'],
            'nextBefore' => $data['logs']->last()['id'] ?? null,
        ]);
    }
}
