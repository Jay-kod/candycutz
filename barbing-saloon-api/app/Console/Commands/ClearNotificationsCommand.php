<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\NotificationBroadcast;
use App\Models\NotificationDelivery;
use App\Models\NotificationRead;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClearNotificationsCommand extends Command
{
    protected $signature = 'notifications:clear 
        {--all : Wipe all notifications, delivery logs, and read states across the entire system}
        {--user= : Clear notifications only for a specific user ID or email address}
        {--days= : Only clear notifications older than specified number of days}
        {--keep-broadcasts : Preserve broadcast campaign records while clearing deliveries}
        {--force : Force operation without interactive confirmation}';

    protected $description = 'Clear notifications, delivery receipts, and read receipts from the database';

    public function handle(): int
    {
        $all = (bool) $this->option('all');
        $userIdentifier = $this->option('user');
        $days = $this->option('days') !== null ? (int) $this->option('days') : null;
        $keepBroadcasts = (bool) $this->option('keep-broadcasts');
        $force = (bool) $this->option('force');

        $this->info('╔══════════════════════════════════════════════════════════════════╗');
        $this->info('║             CandyCutz Notification Cleanup Utility               ║');
        $this->info('╚══════════════════════════════════════════════════════════════════╝');

        // Target: Specific user
        if ($userIdentifier) {
            $user = is_numeric($userIdentifier)
                ? User::find((int) $userIdentifier)
                : User::where('email', (string) $userIdentifier)->first();

            if (! $user) {
                $this->error("User not found: {$userIdentifier}");

                return Command::FAILURE;
            }

            $query = Notification::where('recipient_id', $user->id);
            if ($days !== null) {
                $query->where('created_at', '<=', now()->subDays($days));
            }

            $count = $query->count();
            $readsCount = NotificationRead::where('user_id', $user->id)->count();

            if ($count === 0 && $readsCount === 0) {
                $this->info("No notifications found for user #{$user->id} ({$user->email}).");

                return Command::SUCCESS;
            }

            if (! $force && ! $this->confirm("Permanently delete {$count} notifications and {$readsCount} read receipts for {$user->name} ({$user->email})?", true)) {
                $this->warn('Operation cancelled.');

                return Command::SUCCESS;
            }

            DB::transaction(function () use ($user, $days) {
                $query = Notification::where('recipient_id', $user->id);
                if ($days !== null) {
                    $query->where('created_at', '<=', now()->subDays($days));
                }
                $query->delete();

                NotificationRead::where('user_id', $user->id)->delete();
            });

            $this->info("Successfully cleared {$count} notification(s) for {$user->name}.");
            Log::info("Notifications cleared for user {$user->id} by artisan command.");

            return Command::SUCCESS;
        }

        // Target: Time-scoped cleanup
        if ($days !== null) {
            $cutoff = now()->subDays($days);
            $count = Notification::where('created_at', '<=', $cutoff)->count();

            if ($count === 0) {
                $this->info("No notifications found older than {$days} days.");

                return Command::SUCCESS;
            }

            if (! $force && ! $this->confirm("Permanently delete {$count} notification(s) created on or before {$cutoff->toIso8601String()}?", true)) {
                $this->warn('Operation cancelled.');

                return Command::SUCCESS;
            }

            $deleted = DB::transaction(function () use ($cutoff) {
                return Notification::where('created_at', '<=', $cutoff)->delete();
            });

            $this->info("Successfully deleted {$deleted} notification(s) older than {$days} days.");
            Log::info("Cleaned up {$deleted} notifications older than {$days} days.");

            return Command::SUCCESS;
        }

        // Target: Complete system purge
        $notifCount = Notification::count();
        $deliveriesCount = NotificationDelivery::count();
        $readsCount = NotificationRead::count();
        $broadcastsCount = NotificationBroadcast::count();

        if ($notifCount === 0 && $deliveriesCount === 0 && $readsCount === 0) {
            $this->info('Notification system is already completely clean (0 records found).');

            return Command::SUCCESS;
        }

        $this->table(
            ['Entity', 'Total Records To Clear'],
            [
                ['Notifications', $notifCount],
                ['Notification Deliveries', $deliveriesCount],
                ['Notification Read Receipts', $readsCount],
                ['Broadcast Campaigns', $keepBroadcasts ? '0 (preserved)' : $broadcastsCount],
            ]
        );

        if (! $force && ! $this->confirm('Are you sure you want to permanently clear ALL notifications and prepare the system for fresh activity?', true)) {
            $this->warn('Operation cancelled.');

            return Command::SUCCESS;
        }

        DB::transaction(function () use ($keepBroadcasts) {
            // Delete dependent delivery and read records
            NotificationDelivery::query()->delete();
            NotificationRead::query()->delete();

            // Delete core notifications
            Notification::query()->delete();

            if (! $keepBroadcasts) {
                NotificationBroadcast::query()->delete();
            }
        });

        // Flush any cached unread counters or throttles
        try {
            Cache::flush();
        } catch (\Throwable) {
            // Cache flush resilience
        }

        $this->newLine();
        $this->info('✅ All notifications cleared successfully.');
        $this->info('⚡ Notification system is in pristine state, optimized, and ready for fresh activity.');
        Log::info('Complete notification system purge executed via artisan notifications:clear.');

        return Command::SUCCESS;
    }
}
