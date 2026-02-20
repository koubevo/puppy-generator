<?php

use App\Models\UpdateLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('displays an image from a log', function () {
    $log = UpdateLog::factory()->create([
        'provider' => 'system',
        'transport' => 'log',
        'status' => 'success',
        'payload' => [
            'image' => [
                'mime_type' => 'image/png',
                'data' => base64_encode('fake_image_data_here'),
            ],
        ],
    ]);

    $response = $this->get('/image/'.$log->id);

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'image/png');

    // streamedContent doesn't exist on TestResponse, so we use stream-aware output buffering
    ob_start();
    $response->sendContent();
    $content = ob_get_clean();

    expect($content)->toBe('fake_image_data_here');
});

it('redirects to external url if it is not base64', function () {
    $log = UpdateLog::factory()->create([
        'provider' => 'system',
        'transport' => 'log',
        'status' => 'success',
        'payload' => [
            'image_url' => 'https://example.com/puppy.jpg',
        ],
    ]);

    $response = $this->get('/image/'.$log->id);

    $response->assertRedirect('https://example.com/puppy.jpg');
});
