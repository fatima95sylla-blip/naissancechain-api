<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleXMLElement;

class QRCodeService
{
    /**
     * Generate QR code and save to storage.
     */
    public function generate(string $data, string $filename = null): string
    {
        $filename = $filename ?: 'qr-' . Str::random(10) . '.svg';
        $path = 'qrcodes/' . $filename;

        // Generate QR code using Google Charts API (free and reliable)
        $qrData = urlencode($data);
        $size = 200;
        $url = "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl={$qrData}&choe=UTF-8";

        // Download and save QR code
        $qrContent = file_get_contents($url);
        Storage::disk('public')->put($path, $qrContent);

        return $path;
    }

    /**
     * Generate QR code as base64 string.
     */
    public function generateBase64(string $data): string
    {
        $qrData = urlencode($data);
        $size = 200;
        $url = "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl={$qrData}&choe=UTF-8";

        $qrContent = file_get_contents($url);
        return base64_encode($qrContent);
    }

    /**
     * Generate verification QR code with additional metadata.
     */
    public function generateVerificationQR(array $metadata): string
    {
        $verificationData = [
            'type' => 'naissance_verification',
            'numero' => $metadata['numero_unique'],
            'hash' => $metadata['hash_sha256'],
            'date' => $metadata['date_enregistrement'],
            'url' => route('verification.show', $metadata['numero_unique'])
        ];

        return $this->generate(json_encode($verificationData));
    }

    /**
     * Delete QR code file.
     */
    public function delete(string $path): bool
    {
        return Storage::disk('public')->delete($path);
    }
}
