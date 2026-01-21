<?php

use App\Http\Controllers\CronController;

Route::get("/cron/wakeup", [CronController::class, "wakeUp"])->name("cron.wakeup");