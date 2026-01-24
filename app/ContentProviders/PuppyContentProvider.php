<?php

namespace App\ContentProviders;

use App\Contracts\ContentProvider;
use App\Services\GeminiService;

class PuppyContentProvider implements ContentProvider
{
    public function __construct(private GeminiService $geminiService) {}

    public function getPayload(): array
    {
        $message = $this->geminiService->generateMotivationalText();
        $image = $this->geminiService->generatePuppyImage();

        return [
            'message' => $message,
            'image' => $image,
        ];
    }

    public function getProviderName(): string
    {
        return 'puppy';
    }
}
