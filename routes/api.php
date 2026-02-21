<?php

use App\Http\Controllers\CronController;
use App\Http\Controllers\EventCronController;
use Illuminate\Support\Facades\Route;

Route::post('/cron/wakeup/{taskName}', [CronController::class, 'wakeUp'])->name('cron.wakeup');

Route::post('/cron/events/daily', [EventCronController::class, 'check'])->name('cron.events.daily');
