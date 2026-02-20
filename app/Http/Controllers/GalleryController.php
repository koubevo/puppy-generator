<?php

namespace App\Http\Controllers;

use App\Actions\GetGalleryAction;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class GalleryController extends Controller
{
    public function index(GetGalleryAction $action): ViewContract
    {
        $data = $action->execute();

        return view('gallery', $data);
    }

    public function more(Request $request, GetGalleryAction $action): JsonResponse
    {
        $data = $action->execute(
            beforeId: $request->integer('before'),
            limit: 12,
        );

        $html = View::renderEach('partials._gallery-item', $data['logs']->all(), 'log');

        return response()->json([
            'html' => $html,
            'hasMore' => $data['hasMore'],
            'nextBefore' => $data['logs']->last()['id'] ?? null,
        ]);
    }
}
