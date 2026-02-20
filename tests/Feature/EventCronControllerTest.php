<?php

use App\Models\UpdateLog;
use App\Services\WebPushService;
use Carbon\Carbon;
use Mockery\MockInterface;

use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\mock;
use function Pest\Laravel\postJson;

it('requires correct token for daily events', function () {
    config(['app.cron_token' => 'secret']);

    $response = postJson('/api/cron/events/daily', [], ['Authorization' => 'Bearer wrong']);
    $response->assertForbidden();
});

it('does not send event if there is no matched date', function () {
    config(['app.cron_token' => 'secret']);
    config([
        'events.daily' => [
            ['date' => '01-01', 'message' => 'New Year'],
        ],
    ]);

    Carbon::setTestNow('2024-05-05');

    $response = postJson('/api/cron/events/daily', [], ['Authorization' => 'Bearer secret']);

    $response->assertSuccessful();
    $response->assertJson(['status' => 'idle']);

    assertDatabaseMissing('update_logs', [
        'provider' => 'event_puppy',
    ]);
});

it('sends event if month and day match', function () {
    config(['app.cron_token' => 'secret']);
    config([
        'events.daily' => [
            ['date' => '05-05', 'message' => 'Cinco de Mayo'],
        ],
    ]);

    Carbon::setTestNow('2024-05-05');

    mock(WebPushService::class, function (MockInterface $mock) {
        $mock->shouldReceive('sendToAll')->once()->with('Puppy', 'Cinco de Mayo');
    });

    $response = postJson('/api/cron/events/daily', [], ['Authorization' => 'Bearer secret']);

    $response->assertSuccessful();
    $response->assertJson(['status' => 'executed']);

    $log = UpdateLog::where('provider', 'event_puppy')->first();
    expect($log)->not->toBeNull();
    expect($log->payload['message'])->toBe('Cinco de Mayo');
});

it('sends event if day only matches', function () {
    config(['app.cron_token' => 'secret']);
    config([
        'events.daily' => [
            ['date' => '15', 'message' => 'Mid month'],
        ],
    ]);

    Carbon::setTestNow('2024-06-15');

    mock(WebPushService::class, function (MockInterface $mock) {
        $mock->shouldReceive('sendToAll')->once()->with('Puppy', 'Mid month');
    });

    $response = postJson('/api/cron/events/daily', [], ['Authorization' => 'Bearer secret']);

    $response->assertSuccessful();
    $response->assertJson(['status' => 'executed']);

    $log = UpdateLog::where('provider', 'event_puppy')->latest()->first();
    expect($log)->not->toBeNull();
    expect($log->payload['message'])->toBe('Mid month');
});
