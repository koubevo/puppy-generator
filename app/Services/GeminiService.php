<?php

namespace App\Services;

use Gemini;
use Gemini\Client;
use Gemini\Data\GenerationConfig;
use Gemini\Enums\ResponseModality;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private Client $client;

    public function __construct()
    {
        $apiKey = config('services.gemini.api_key');

        if (empty($apiKey)) {
            throw new \RuntimeException('Gemini API key is not configured');
        }

        $this->client = Gemini::client($apiKey);
    }

    /**
     * Generate a motivational text in Czech for a girlfriend who studies and loves dogs.
     */
    public function generateMotivationalText(): string
    {
        $prompt = <<<'PROMPT'
Jsi přátelský a láskyplný denní motivační asistent. Tvým úkolem je napsat krátkou, 
srdečnou motivační zprávu v češtině pro mladou ženu, která pilně studuje a miluje psy.

Pravidla:
- Zpráva by měla být max 2 věty
- Použij pozitivní a povzbuzující tón, ale nebuď příliš vlezlý a pozitivní
- Buď kreativní a každý den jiný
- Nepoužívej formální oslovení, piš jako blízký přítel
- Nepodepisuj se

Napiš motivační zprávu:
PROMPT;

        try {
            $response = $this->client
                ->generativeModel(model: 'gemini-2.5-flash')
                ->generateContent($prompt);

            return trim($response->text());
        } catch (\Throwable $e) {
            Log::error('Gemini text generation failed', [
                'error' => $e->getMessage(),
                'prompt' => $prompt,
            ]);
            throw $e;
        }
    }

    /**
     * Generate a cute puppy image using Gemini's Imagen model.
     *
     * @return array{mime_type: string, data: string} Base64 encoded image data
     */
    public function generatePuppyImage(): array
    {
        $breeds = [
            'Golden Retriever',
            'Labrador Retriever',
            'Corgi',
            'Shiba Inu',
            'Pomeranian',
            'French Bulldog',
            'Beagle',
            'Husky',
            'Australian Shepherd',
            'Cavalier King Charles Spaniel',
            'Samoyed',
            'Border Collie',
            'Dachshund',
            'Maltese',
            'Yorkshire Terrier',
            'German Shepherd',
            'Bernese Mountain Dog',
            'Poodle',
            'Cocker Spaniel',
            'Dalmatian',
        ];

        $selectedBreed = $breeds[array_rand($breeds)];

        $prompt = "Generate a cute, adorable {$selectedBreed} puppy. Heartwarming expression, professional pet photography quality. The puppy should be the main focus of the image.";

        try {
            $response = $this->client
                ->generativeModel(model: 'gemini-2.5-flash-image')
                ->withGenerationConfig(
                    generationConfig: new GenerationConfig(
                        responseModalities: [ResponseModality::IMAGE, ResponseModality::TEXT],
                    )
                )
                ->generateContent($prompt);

            foreach ($response->candidates as $candidate) {
                foreach ($candidate->content->parts as $part) {
                    if (isset($part->inlineData)) {
                        return [
                            'mime_type' => $part->inlineData->mimeType->value,
                            'data' => $part->inlineData->data,
                        ];
                    }
                }
            }

            throw new \RuntimeException('No image data in response');
        } catch (\Throwable $e) {
            Log::error('Gemini image generation failed', [
                'error' => $e->getMessage(),
                'prompt' => $prompt,
            ]);
            throw $e;
        }
    }
}
