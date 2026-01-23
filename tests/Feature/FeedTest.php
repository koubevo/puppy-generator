<?php

use App\Models\PushSubscription;
use App\Models\UpdateLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can access feed page without authentication', function () {
    $response = $this->get('/feed');

    $response->assertSuccessful();
    $response->assertViewIs('feed');
});

it('displays update logs from different providers as different bots', function () {
    UpdateLog::create([
        'provider' => 'puppy',
        'transport' => 'telegram',
        'status' => UpdateLog::STATUS_SUCCESS,
        'payload' => ['message' => 'Here is your daily puppy!'],
        'sent_at' => now(),
    ]);

    UpdateLog::create([
        'provider' => 'weather',
        'transport' => 'telegram',
        'status' => UpdateLog::STATUS_SUCCESS,
        'payload' => ['message' => 'Sunny day ahead!'],
        'sent_at' => now()->subHour(),
    ]);

    $response = $this->get('/feed');

    $response->assertSuccessful();
    $response->assertSee('Puppy');
    $response->assertSee('Weather');
    $response->assertSee('Here is your daily puppy!');
    $response->assertSee('Sunny day ahead!');
});

it('can subscribe to push notifications', function () {
    $response = $this->postJson('/push/subscribe', [
        'endpoint' => 'https://example.com/push/abc123',
        'keys' => [
            'p256dh' => 'test-p256dh-key',
            'auth' => 'test-auth-key',
        ],
    ]);

    $response->assertSuccessful();

    $this->assertDatabaseHas('push_subscriptions', [
        'endpoint' => 'https://example.com/push/abc123',
    ]);
});

it('can unsubscribe from push notifications', function () {
    PushSubscription::create([
        'endpoint' => 'https://example.com/push/abc123',
        'keys' => ['p256dh' => 'test', 'auth' => 'test'],
    ]);

    $response = $this->deleteJson('/push/unsubscribe', [
        'endpoint' => 'https://example.com/push/abc123',
    ]);

    $response->assertSuccessful();

    $this->assertDatabaseMissing('push_subscriptions', [
        'endpoint' => 'https://example.com/push/abc123',
    ]);
});
