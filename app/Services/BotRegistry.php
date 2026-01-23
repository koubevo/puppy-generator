<?php

namespace App\Services;

class BotRegistry
{
    /**
     * Bot configurations indexed by provider name.
     *
     * @var array<string, array{emoji: string, color: string, name: string}>
     */
    private array $bots = [
        'puppy' => [
            'emoji' => '🐕',
            'color' => 'bg-orange-100 text-orange-600',
            'name' => 'Puppy',
        ],
        'weather' => [
            'emoji' => '☀️',
            'color' => 'bg-blue-100 text-blue-600',
            'name' => 'Weather',
        ],
        'system' => [
            'emoji' => '🤖',
            'color' => 'bg-gray-100 text-gray-600',
            'name' => 'System',
        ],
    ];

    private array $default = [
        'emoji' => '📣',
        'color' => 'bg-gray-100 text-gray-600',
        'name' => 'Update',
    ];

    /**
     * Get bot configuration by provider name.
     *
     * @return array{emoji: string, color: string, name: string}
     */
    public function get(string $provider): array
    {
        $key = strtolower($provider);
        $bot = $this->bots[$key] ?? $this->default;

        if (! isset($this->bots[$key])) {
            $bot['name'] = ucfirst($provider);
        }

        return $bot;
    }

    /**
     * Get all registered bots.
     *
     * @return array<string, array{emoji: string, color: string, name: string}>
     */
    public function all(): array
    {
        return $this->bots;
    }
}
