<?php

use App\Http\Controllers\CronController;
use Illuminate\Support\Facades\Route;

Route::post("/cron/wakeup/{taskName}", [CronController::class, "wakeUp"])->name("cron.wakeup");
