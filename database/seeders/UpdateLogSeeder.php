<?php

namespace Database\Seeders;

use App\Models\UpdateLog;
use Illuminate\Database\Seeder;

class UpdateLogSeeder extends Seeder
{
    public function run(): void
    {
        UpdateLog::create([
            'provider' => 'puppy',
            'transport' => 'telegram',
            'status' => UpdateLog::STATUS_SUCCESS,
            'payload' => [
                'message' => 'Here is your adorable puppy for today! 🐕',
                'image_url' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400',
            ],
            'sent_at' => now(),
        ]);

        UpdateLog::create([
            'provider' => 'weather',
            'transport' => 'telegram',
            'status' => UpdateLog::STATUS_SUCCESS,
            'payload' => [
                'message' => 'Good morning! Today will be sunny with a high of 22°C ☀️',
            ],
            'sent_at' => now()->subHours(2),
        ]);

        UpdateLog::create([
            'provider' => 'puppy',
            'transport' => 'telegram',
            'status' => UpdateLog::STATUS_SUCCESS,
            'payload' => [
                'message' => 'Yesterday\'s puppy was extra fluffy! 🐶',
                'image_url' => 'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=400',
            ],
            'sent_at' => now()->subDay(),
        ]);
    }
}
