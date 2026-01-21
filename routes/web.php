<?php

use App\Http\Controllers\CronController;

Route::get("/cron/wakeup/{token}", [CronController::class, "wakeUp"])->name("cron.wakeup");