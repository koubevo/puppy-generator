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

it('shows only 6 items on initial load', function () {
    foreach (range(1, 8) as $i) {
        UpdateLog::create([
            'provider' => 'puppy',
            'transport' => 'telegram',
            'status' => UpdateLog::STATUS_SUCCESS,
            'payload' => ['message' => "Puppy update #{$i}"],
            'sent_at' => now()->subHours(8 - $i),
        ]);
    }

    $response = $this->get('/feed');

    $response->assertSuccessful();
    // Newest 6 should be visible
    $response->assertSee('Puppy update #8');
    $response->assertSee('Puppy update #3');
    // Oldest 2 should NOT be visible
    $response->assertDontSee('Puppy update #2');
    $response->assertDontSee('Puppy update #1');
    // Load more button should be present
    $response->assertSee('Load more');
});

it('does not show load more button when there are 6 or fewer items', function () {
    foreach (range(1, 6) as $i) {
        UpdateLog::create([
            'provider' => 'puppy',
            'transport' => 'telegram',
            'status' => UpdateLog::STATUS_SUCCESS,
            'payload' => ['message' => "Puppy update #{$i}"],
            'sent_at' => now()->subHours(6 - $i),
        ]);
    }

    $response = $this->get('/feed');

    $response->assertSuccessful();
    $response->assertDontSee('Load more');
});

it('returns next batch via load more endpoint', function () {
    $logs = collect();
    foreach (range(1, 8) as $i) {
        $logs->push(UpdateLog::create([
            'provider' => 'puppy',
            'transport' => 'telegram',
            'status' => UpdateLog::STATUS_SUCCESS,
            'payload' => ['message' => "Puppy update #{$i}"],
            'sent_at' => now()->subHours(8 - $i),
        ]));
    }

    // The 7th newest log (index 6 when sorted newest-first) should be the cursor boundary.
    // We want items older than the 6th newest, so pass the id of the 3rd log (index 5 from newest = log #3).
    $sixthNewestId = $logs->sortByDesc('sent_at')->values()[5]->id;

    $response = $this->getJson("/feed/more?before={$sixthNewestId}");

    $response->assertSuccessful();
    $response->assertJsonStructure(['html', 'hasMore', 'nextBefore']);
    $response->assertJsonFragment(['hasMore' => false]);
    expect($response->json('html'))->toContain('Puppy update #2');
    expect($response->json('html'))->toContain('Puppy update #1');
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
