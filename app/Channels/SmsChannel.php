<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SmsChannel
{
    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification): void
    {
        try {
            $message = $notification->toSms($notifiable);
            $phoneNumber = $notifiable->routeNotificationFor('sms');

            if (!$phoneNumber) {
                Log::warning('No phone number for SMS notification', [
                    'notifiable_type' => get_class($notifiable),
                    'notifiable_id' => $notifiable->id ?? null,
                    'notification_type' => get_class($notification),
                ]);
                return;
            }

            // Mock SMS sending - In production, integrate with real SMS provider
            $this->sendMockSms($phoneNumber, $message);

            Log::info('SMS notification sent successfully', [
                'phone_number' => $this->maskPhoneNumber($phoneNumber),
                'message' => substr($message, 0, 100) . '...',
                'notification_type' => get_class($notification),
                'sent_at' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send SMS notification', [
                'notifiable_type' => get_class($notifiable),
                'notifiable_id' => $notifiable->id ?? null,
                'notification_type' => get_class($notification),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // In production, you might want to retry or handle the error differently
            // For now, we'll just log and continue
        }
    }

    /**
     * Mock SMS sending functionality.
     */
    protected function sendMockSms(string $phoneNumber, string $message): void
    {
        // Simulate SMS provider API call
        $mockResponse = [
            'status' => 'sent',
            'message_id' => 'SMS_' . uniqid(),
            'phone_number' => $phoneNumber,
            'message' => $message,
            'sent_at' => now()->toISOString(),
            'provider' => 'NaissanceChain-MockSMS',
        ];

        // Simulate processing time
        usleep(rand(100000, 500000)); // 0.1-0.5 seconds

        // Log mock response for debugging
        Log::debug('Mock SMS sent', $mockResponse);

        // In production, replace this with actual SMS provider integration:
        // - Twilio: https://www.twilio.com/docs/sms
        // - Vonage: https://developer.vonage.com/en/messaging/sms
        // - AWS SNS: https://aws.amazon.com/sns/
        // - Local SMS gateway
    }

    /**
     * Mask phone number for logging privacy.
     */
    protected function maskPhoneNumber(string $phoneNumber): string
    {
        $length = strlen($phoneNumber);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        $visible = substr($phoneNumber, -4);
        $masked = str_repeat('*', $length - 4) . $visible;

        return $masked;
    }

    /**
     * Validate phone number format.
     */
    protected function validatePhoneNumber(string $phoneNumber): bool
    {
        // Basic phone number validation - adjust according to your requirements
        $patterns = [
            '/^\+?[0-9]{10,15}$/', // International format
            '/^[0-9]{10}$/', // Local format (10 digits)
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $phoneNumber)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get SMS provider configuration.
     */
    protected function getProviderConfig(): array
    {
        return [
            'provider' => env('SMS_PROVIDER', 'mock'),
            'api_key' => env('SMS_API_KEY'),
            'api_secret' => env('SMS_API_SECRET'),
            'sender_id' => env('SMS_SENDER_ID', 'NaissanceChain'),
            'timeout' => env('SMS_TIMEOUT', 30),
            'retry_attempts' => env('SMS_RETRY_ATTEMPTS', 3),
        ];
    }

    /**
     * Format phone number to international format.
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Add country code if missing (Guinea: +224)
        if (strlen($cleaned) === 8 && str_starts_with($cleaned, '6')) {
            $cleaned = '224' . $cleaned;
        }

        // Add + prefix if missing
        if (!str_starts_with($cleaned, '+')) {
            $cleaned = '+' . $cleaned;
        }

        return $cleaned;
    }
}
