<?php

namespace App\Http\Controllers;

use App\Actions\GetFeedAction;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class FeedController extends Controller
{
    public function index(GetFeedAction $action): ViewContract
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

        $html = View::renderEach('partials._feed-item', $data['logs'], 'log');

        return response()->json([
            'html' => $html,
            'hasMore' => $data['hasMore'],
            'nextBefore' => $data['logs']->last()['id'] ?? null,
        ]);
    }
}
