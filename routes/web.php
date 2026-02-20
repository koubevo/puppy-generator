<?php

use App\Http\Controllers\FeedController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\PushSubscriptionController;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Route;

Route::get('/feed', [FeedController::class, 'index'])->name('feed');
Route::get('/feed/more', [FeedController::class, 'more'])->name('feed.more');

Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery');
Route::get('/gallery/more', [GalleryController::class, 'more'])->name('gallery.more');

Route::get('/image/{updateLog}', [ImageController::class, 'show'])->name('image.show');

Route::post('/push/subscribe', [PushSubscriptionController::class, 'store']);
Route::delete('/push/unsubscribe', [PushSubscriptionController::class, 'destroy']);

// Test routes for Gemini - only available in local/dev environment
if (app()->isLocal()) {
    Route::get('/test/gemini/text', function (GeminiService $gemini) {
        return response()->json([
            'message' => $gemini->generateMotivationalText(),
        ]);
    });

    Route::get('/test/gemini/image', function (GeminiService $gemini) {
        $image = $gemini->generatePuppyImage();

        return response()->json([
            'mime_type' => $image['mime_type'],
            'image_preview' => 'data:'.$image['mime_type'].';base64,'.substr($image['data'], 0, 100).'...',
        ]);
    });

    Route::get('/test/gemini/full', function (GeminiService $gemini) {
        $image = $gemini->generatePuppyImage();

        return view('test-gemini', [
            'message' => $gemini->generateMotivationalText(),
            'image' => 'data:'.$image['mime_type'].';base64,'.$image['data'],
        ]);
    });
}
