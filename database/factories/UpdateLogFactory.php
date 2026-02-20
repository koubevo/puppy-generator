<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UpdateLog>
 */
class UpdateLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider' => 'system',
            'transport' => 'log',
            'status' => 'success',
            'payload' => [],
            'sent_at' => now(),
        ];
    }
}
