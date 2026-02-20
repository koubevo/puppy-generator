<?php

namespace App\Traits;

trait ExtractsImageUrl
{
    protected function extractImageUrl(array $payload): ?string
    {
        // Check for direct URL string
        if (isset($payload['image_url']) && is_string($payload['image_url'])) {
            return $payload['image_url'];
        }

        // Check for base64 image object with mime_type and data
        if (isset($payload['image']) && is_array($payload['image'])) {
            $image = $payload['image'];
            if (isset($image['mime_type'], $image['data'])) {
                return 'data:'.$image['mime_type'].';base64,'.$image['data'];
            }
        }

        // Check if image is a direct URL string
        if (isset($payload['image']) && is_string($payload['image'])) {
            return $payload['image'];
        }

        return null;
    }

    protected function createThumbnailBase64(string $base64, int $maxWidth = 400): string
    {
        // Extract the raw base64 data if it has a prefix
        if (str_starts_with($base64, 'data:')) {
            $parts = explode(',', $base64);
            $base64Data = $parts[1] ?? $parts[0];
            $mimeType = explode(';', explode(':', $parts[0])[1] ?? 'image/jpeg')[0];
        } else {
            $base64Data = $base64;
            $mimeType = 'image/jpeg'; // Fallback
        }

        $imageData = base64_decode($base64Data);
        if ($imageData === false) {
            return $base64; // Fallback if decoding fails
        }

        // Use GD to resize the image
        $sourceImage = @imagecreatefromstring($imageData);
        if (! $sourceImage) {
            return $base64; // Fallback if not a valid image
        }

        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);

        // Don't resize if it's already small enough
        if ($width <= $maxWidth) {
            imagedestroy($sourceImage);

            return $base64;
        }

        $ratio = $width / $height;
        $newWidth = $maxWidth;
        $newHeight = (int) ($maxWidth / $ratio);

        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG/GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
            imagefilledrectangle($thumbnail, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Capture output
        ob_start();
        if ($mimeType === 'image/png') {
            imagepng($thumbnail);
        } elseif ($mimeType === 'image/gif') {
            imagegif($thumbnail);
        } else {
            imagejpeg($thumbnail, null, 80);
        }
        $thumbnailData = ob_get_clean();

        imagedestroy($sourceImage);
        imagedestroy($thumbnail);

        return 'data:'.$mimeType.';base64,'.base64_encode($thumbnailData);
    }
}
