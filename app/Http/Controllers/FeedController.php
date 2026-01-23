<?php

namespace App\Http\Controllers;

use App\Actions\GetFeedAction;
use Illuminate\Contracts\View\View;

class FeedController extends Controller
{
    public function index(GetFeedAction $action): View
    {
        $data = $action->execute();

        return view('feed', $data);
    }
}
