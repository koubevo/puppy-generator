<?php

use App\ContentProviders\PuppyContentProvider;
use App\Contracts\ContentProvider;
use App\Services\GeminiService;

use function Pest\Laravel\mock;

it('returns correct provider name', function () {
    $geminiService = mock(GeminiService::class);

    $provider = new PuppyContentProvider($geminiService);

    expect($provider->getProviderName())->toBe('puppy');
});

it('returns payload with message and image', function () {
    $geminiService = mock(GeminiService::class);

    $geminiService
        ->shouldReceive('generateMotivationalText')
        ->once()
        ->andReturn('Krásný den, bojovnice! Studium je jako štěně - čím víc mu věnuješ lásku, tím víc ti vrátí.');

    $geminiService
        ->shouldReceive('generatePuppyImage')
        ->once()
        ->andReturn([
            'mime_type' => 'image/png',
            'data' => 'base64encodedimagedata',
        ]);

    $provider = new PuppyContentProvider($geminiService);
    $payload = $provider->getPayload();

    expect($payload)
        ->toHaveKey('message')
        ->toHaveKey('image')
        ->and($payload['message'])->toBeString()->not->toBeEmpty()
        ->and($payload['image'])->toBeArray()
        ->and($payload['image']['mime_type'])->toBe('image/png')
        ->and($payload['image']['data'])->toBe('base64encodedimagedata');
});

it('is bound to ContentProvider contract', function () {
    // Mock GeminiService in container for this test
    $this->mock(GeminiService::class, function ($mock) {
        $mock->shouldReceive('generateMotivationalText')->andReturn('Test message');
        $mock->shouldReceive('generatePuppyImage')->andReturn([
            'mime_type' => 'image/png',
            'data' => 'test',
        ]);
    });

    $provider = app(ContentProvider::class);

    expect($provider)->toBeInstanceOf(PuppyContentProvider::class);
});
