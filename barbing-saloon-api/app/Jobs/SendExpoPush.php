<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Queued job that sends push notifications via the Expo Push API.
 *
 * @see https://docs.expo.dev/push-notifications/sending-notifications/
 */
class SendExpoPush implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of retries before giving up.
     */
    public int $tries = 3;

    /**
     * Seconds to wait before retrying.
     */
    public int $backoff = 10;

    /**
     * @param  array<string>  $tokens   Expo push tokens
     * @param  string         $title    Notification title
     * @param  string         $body     Notification body text
     * @param  array          $data     Extra data payload for deep linking
     */
    public function __construct(
        public array $tokens,
        public string $title,
        public string $body,
        public array $data = [],
    ) {}

    public function handle(): void
    {
        if (empty($this->tokens)) {
            return;
        }

        // Build messages — one per token, batched to Expo API
        $messages = [];
        foreach ($this->tokens as $token) {
            // Only process valid Expo push tokens
            if (! str_starts_with($token, 'ExponentPushToken[') && ! str_starts_with($token, 'ExpoPushToken[')) {
                Log::debug("Skipping non-Expo token: {$token}");

                continue;
            }

            $messages[] = [
                'to' => $token,
                'title' => $this->title,
                'body' => $this->body,
                'data' => $this->data,
                'sound' => 'default',
                'priority' => 'high',
                'channelId' => 'candycutz-default',
            ];
        }

        if (empty($messages)) {
            Log::debug('SendExpoPush: No valid Expo tokens to send to.');

            return;
        }

        // Expo Push API supports batch — up to 100 messages per request
        $chunks = array_chunk($messages, 100);

        foreach ($chunks as $chunk) {
            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Accept-Encoding' => 'gzip, deflate',
                ])->post('https://exp.host/--/api/v2/push/send', $chunk);

                if ($response->failed()) {
                    Log::warning('Expo Push API error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                } else {
                    $responseData = $response->json('data') ?? [];
                    foreach ($responseData as $index => $ticket) {
                        if (($ticket['status'] ?? '') === 'error') {
                            Log::warning('Expo push ticket error', [
                                'token' => $chunk[$index]['to'] ?? 'unknown',
                                'error' => $ticket['message'] ?? 'Unknown error',
                                'details' => $ticket['details'] ?? [],
                            ]);
                        }
                    }

                    Log::info('Expo Push sent successfully', [
                        'count' => count($chunk),
                        'tickets' => count($responseData),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Expo Push API request failed: '.$e->getMessage());
                throw $e; // Let the queue retry
            }
        }
    }
}
