<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\DeviceToken;
use App\Models\PushDeliveryStat;
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
        $totalSent = 0;
        $totalDelivered = 0;
        $totalFailed = 0;
        $failureReasons = [];

        foreach ($chunks as $chunk) {
            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Accept-Encoding' => 'gzip, deflate',
                ])->post('https://exp.host/--/api/v2/push/send', $chunk);

                $chunkCount = count($chunk);
                $totalSent += $chunkCount;

                if ($response->failed()) {
                    $totalFailed += $chunkCount;
                    $failureReasons['HttpRequestFailed'] = ($failureReasons['HttpRequestFailed'] ?? 0) + $chunkCount;
                    Log::warning('Expo Push API error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                } else {
                    $responseData = $response->json('data') ?? [];
                    foreach ($responseData as $index => $ticket) {
                        if (($ticket['status'] ?? '') === 'error') {
                            $totalFailed++;
                            $errCode = $ticket['details']['error'] ?? $ticket['message'] ?? 'UnknownError';
                            $failureReasons[$errCode] = ($failureReasons[$errCode] ?? 0) + 1;

                            if ($errCode === 'DeviceNotRegistered') {
                                $unregToken = $chunk[$index]['to'] ?? null;
                                if ($unregToken) {
                                    DeviceToken::where('token', $unregToken)->update(['is_revoked' => true]);
                                }
                            }

                            Log::warning('Expo push ticket error', [
                                'token' => $chunk[$index]['to'] ?? 'unknown',
                                'error' => $ticket['message'] ?? 'Unknown error',
                                'details' => $ticket['details'] ?? [],
                            ]);
                        } else {
                            $totalDelivered++;
                        }
                    }

                    Log::info('Expo Push sent successfully', [
                        'count' => count($chunk),
                        'tickets' => count($responseData),
                    ]);
                }
            } catch (\Throwable $e) {
                $totalFailed += count($chunk);
                $failureReasons['ExceptionThrown'] = ($failureReasons['ExceptionThrown'] ?? 0) + count($chunk);
                Log::error('Expo Push API request failed: '.$e->getMessage());
                throw $e; // Let the queue retry
            }
        }

        // Record aggregated stats for today
        try {
            $today = now()->toDateString();
            $totalTokens = DeviceToken::where('is_revoked', false)->count();
            $activeUsersWithToken = DeviceToken::where('is_revoked', false)->distinct('user_id')->count('user_id');

            $stat = PushDeliveryStat::firstOrCreate(
                ['date' => $today],
                [
                    'total_tokens' => $totalTokens,
                    'valid_tokens' => $totalTokens,
                    'expired_tokens' => 0,
                    'sent_count' => 0,
                    'delivered_count' => 0,
                    'failed_count' => 0,
                    'failure_reasons' => [],
                    'active_users_with_token' => $activeUsersWithToken,
                ]
            );

            $existingReasons = $stat->failure_reasons ?? [];
            foreach ($failureReasons as $reason => $count) {
                $existingReasons[$reason] = ($existingReasons[$reason] ?? 0) + $count;
            }

            $stat->increment('sent_count', $totalSent);
            $stat->increment('delivered_count', $totalDelivered);
            $stat->increment('failed_count', $totalFailed);
            $stat->total_tokens = $totalTokens;
            $stat->valid_tokens = $totalTokens;
            $stat->active_users_with_token = $activeUsersWithToken;
            $stat->failure_reasons = $existingReasons;
            $stat->save();
        } catch (\Throwable $e) {
            Log::warning('Failed to record push delivery stats: '.$e->getMessage());
        }
    }
}
