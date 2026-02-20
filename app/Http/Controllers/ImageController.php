<?php

namespace App\Http\Controllers;

use App\Models\UpdateLog;
use App\Traits\ExtractsImageUrl;

class ImageController extends Controller
{
    use ExtractsImageUrl;

    public function show(UpdateLog $updateLog)
    {
        // Ensure this log actually has an image
        $imageUrl = $this->extractImageUrl($updateLog->payload ?? []);

        if (! $imageUrl) {
            abort(404, 'Image not found');
        }

        // If it's a URL, just redirect to it
        if (! str_starts_with($imageUrl, 'data:')) {
            return redirect($imageUrl);
        }

        // It's a base64 image, we should parse it and serve it as a real image response
        // data:image/jpeg;base64,...
        $parts = explode(',', $imageUrl);
        if (count($parts) !== 2) {
            abort(404, 'Invalid image format');
        }

        $mediaType = explode(';', explode(':', $parts[0])[1])[0];
        $base64Data = $parts[1];

        $imageData = base64_decode($base64Data);

        if ($imageData === false) {
            abort(404, 'Invalid image data');
        }

        return response($imageData)->header('Content-Type', $mediaType)
            ->header('Cache-Control', 'public, max-age=31536000');
    }
}
