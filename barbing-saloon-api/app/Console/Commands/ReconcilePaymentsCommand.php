<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Services\PaymentConfirmer;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReconcilePaymentsCommand extends Command
{
    protected $signature = 'payments:reconcile {--minutes=10 : Age in minutes} {--limit=50 : Max records} {--dry-run : Only inspect without updating}';

    protected $description = 'Reconcile pending Paystack payments against gateway status';

    public function handle(PaymentGateway $gateway, PaymentConfirmer $confirmer): int
    {
        $minutes = (int) $this->option('minutes');
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');

        $cutoff = now()->subMinutes($minutes);

        $pendingPayments = Payment::whereIn('status', [PaymentStatus::initiated->value, PaymentStatus::pending->value])
            ->where('provider', 'paystack')
            ->whereNotNull('reference')
            ->where('created_at', '<=', $cutoff)
            ->limit($limit)
            ->get();

        if ($pendingPayments->isEmpty()) {
            $this->info("No pending payments found older than {$minutes} minutes.");

            return Command::SUCCESS;
        }

        $this->info("Found {$pendingPayments->count()} pending payment(s) to reconcile.");

        $confirmed = 0;
        $failed = 0;
        $stillPending = 0;

        foreach ($pendingPayments as $payment) {
            $reference = (string) $payment->reference;
            $this->line("Reconciling payment #{$payment->id} [ref: {$reference}]...");

            if ($dryRun) {
                $this->comment("  [DRY-RUN] Would query gateway for {$reference}");

                continue;
            }

            try {
                $result = $gateway->verify($reference);

                if ($result->isSuccessful()) {
                    $confirmer->confirm($reference, 'cron_reconcile', $result->raw_payload);
                    $this->info("  -> CONFIRMED payment #{$payment->id}");
                    $confirmed++;
                } elseif ($result->status === 'failed') {
                    $payment->update([
                        'status' => PaymentStatus::failed->value,
                        'gateway_response' => 'Payment abandoned or failed at gateway',
                    ]);
                    $this->warn("  -> MARKED FAILED payment #{$payment->id}");
                    $failed++;
                } else {
                    $this->line('  -> STILL PENDING at gateway');
                    $stillPending++;
                }
            } catch (\Throwable $e) {
                Log::warning("Reconciliation failed for payment [{$reference}]: {$e->getMessage()}");
                $this->error("  -> ERROR: {$e->getMessage()}");
            }
        }

        $this->info("Reconciliation complete. Confirmed: {$confirmed}, Failed: {$failed}, Still pending: {$stillPending}");

        return Command::SUCCESS;
    }
}
