<?php

use App\Http\Controllers\FeedController;
use App\Http\Controllers\PushSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/feed', [FeedController::class, 'index'])->name('feed');

Route::post('/push/subscribe', [PushSubscriptionController::class, 'store']);
Route::delete('/push/unsubscribe', [PushSubscriptionController::class, 'destroy']);
