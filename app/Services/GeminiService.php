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
            'Shih Tzu',
            'Chihuahua',
            'Jack Russell Terrier',
            'Akita',
            'Boxer',
            'Great Dane',
            'Rottweiler',
            'Doberman',
            'Newfoundland',
            'Saint Bernard',
            'Irish Setter',
            'Weimaraner',
            'Vizsla',
            'Whippet',
            'Papillon',
            'Havanese',
            'Basenji',
            'Chow Chow',
        ];

        $environments = [
            'in a sunny wildflower meadow',
            'on a sandy beach at golden hour',
            'in a cozy living room by a fireplace',
            'in a snowy forest',
            'in a colorful autumn park with fallen leaves',
            'in a blooming cherry blossom garden',
            'on a wooden porch during a rainy day',
            'in a sunlit kitchen',
            'in a grassy backyard with a garden hose',
            'on a hiking trail in the mountains',
            'in a field of lavender',
            'by a calm lake at sunset',
            'on a city sidewalk café terrace',
            'in a Christmas-decorated room',
            'in a sunflower field',
            'on a farm with hay bales',
            'in a misty forest clearing at dawn',
            'on a boat on a lake',
            'in a cozy bed with blankets',
            'in a spring garden with tulips',
            'on a rocky coastline',
            'under a big oak tree',
            'in a studio with colorful paper backdrops',
            'on a picnic blanket in a park',
        ];

        $activities = [
            'sleeping peacefully curled up',
            'running joyfully',
            'playing with a tennis ball',
            'tilting its head curiously',
            'catching snowflakes',
            'splashing in a puddle',
            'chewing on a toy bone',
            'sitting proudly and looking at the camera',
            'yawning adorably',
            'rolling on its back in the grass',
            'chasing a butterfly',
            'digging in the sand',
            'peeking out from behind a blanket',
            'stretching after a nap',
            'playing tug-of-war with a rope toy',
            'sniffing flowers curiously',
            'jumping in the air',
            'cuddling with a stuffed animal',
            'balancing a treat on its nose',
            'howling at the sky',
            'shaking off water',
            'playing with autumn leaves',
            'lying in a sunbeam',
            'wearing a tiny knitted sweater',
        ];

        $moods = [
            'heartwarming and cozy',
            'playful and energetic',
            'serene and peaceful',
            'funny and goofy',
            'majestic and proud',
            'curious and adventurous',
            'sleepy and soft',
            'joyful and excited',
        ];

        $styles = [
            'professional pet photography, shallow depth of field',
            'warm cinematic lighting, film grain',
            'vibrant and colorful, high contrast',
            'soft pastel tones, dreamy atmosphere',
            'golden hour natural light, bokeh background',
            'cozy warm tones, lifestyle photography',
            'dramatic lighting, studio quality',
            'watercolor painting style, artistic',
        ];

        $selectedBreed = $breeds[array_rand($breeds)];
        $selectedEnvironment = $environments[array_rand($environments)];
        $selectedActivity = $activities[array_rand($activities)];
        $selectedMood = $moods[array_rand($moods)];
        $selectedStyle = $styles[array_rand($styles)];

        $prompt = "A cute, adorable {$selectedBreed} puppy {$selectedActivity} {$selectedEnvironment}. The mood is {$selectedMood}. Style: {$selectedStyle}. The puppy should be the main focus of the image.";

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
