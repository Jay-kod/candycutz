<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Upgrade payments table
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (! Schema::hasColumn('payments', 'provider')) {
                    $table->string('provider', 50)->default('paystack')->after('customer_id');
                }
                if (! Schema::hasColumn('payments', 'reference')) {
                    $table->string('reference', 120)->nullable()->unique()->after('provider');
                }
                if (! Schema::hasColumn('payments', 'access_code')) {
                    $table->string('access_code', 150)->nullable()->after('reference');
                }
                if (! Schema::hasColumn('payments', 'authorization_url')) {
                    $table->text('authorization_url')->nullable()->after('access_code');
                }
                if (! Schema::hasColumn('payments', 'amount_minor')) {
                    $table->unsignedBigInteger('amount_minor')->nullable()->after('amount');
                }
                if (! Schema::hasColumn('payments', 'provider_transaction_id')) {
                    $table->string('provider_transaction_id', 150)->nullable()->index()->after('status');
                }
                if (! Schema::hasColumn('payments', 'channel')) {
                    $table->string('channel', 50)->nullable()->after('provider_transaction_id');
                }
                if (! Schema::hasColumn('payments', 'gateway_response')) {
                    $table->string('gateway_response', 255)->nullable()->after('channel');
                }
                if (! Schema::hasColumn('payments', 'fees_minor')) {
                    $table->unsignedBigInteger('fees_minor')->default(0)->after('gateway_response');
                }
                if (! Schema::hasColumn('payments', 'paid_at')) {
                    $table->timestamp('paid_at')->nullable()->after('fees_minor');
                }
                if (! Schema::hasColumn('payments', 'refunded_minor')) {
                    $table->unsignedBigInteger('refunded_minor')->default(0)->after('paid_at');
                }
                if (! Schema::hasColumn('payments', 'failure_reason')) {
                    $table->text('failure_reason')->nullable()->after('refunded_minor');
                }
                if (! Schema::hasColumn('payments', 'client')) {
                    $table->string('client', 20)->nullable()->after('failure_reason');
                }
                if (! Schema::hasColumn('payments', 'customer_email')) {
                    $table->string('customer_email', 150)->nullable()->after('client');
                }
                if (! Schema::hasColumn('payments', 'expires_at')) {
                    $table->timestamp('expires_at')->nullable()->after('customer_email');
                }
                if (! Schema::hasColumn('payments', 'flagged_reason')) {
                    $table->string('flagged_reason', 150)->nullable()->after('expires_at');
                }
            });

            // Backfill payments
            DB::table('payments')->whereNull('amount_minor')->update([
                'amount_minor' => DB::raw('amount'),
            ]);

            DB::table('payments')->whereNull('provider')->update([
                'provider' => DB::raw("CASE WHEN payment_method = 'manual_transfer' THEN 'manual_transfer' ELSE 'paystack' END"),
            ]);

            // Fill reference from transaction_ref or gateway_reference if null
            if (Schema::hasColumn('payments', 'transaction_ref')) {
                DB::table('payments')->whereNull('reference')->update([
                    'reference' => DB::raw("COALESCE(transaction_ref, 'CC-' || id)"),
                ]);
            }

            // Archive legacy Stripe rows if any exist with Stripe data
            if (Schema::hasColumn('payments', 'stripe_payment_intent_id')) {
                $stripeCount = DB::table('payments')->whereNotNull('stripe_payment_intent_id')->count();
                if ($stripeCount > 0) {
                    if (! Schema::hasTable('payments_legacy_stripe')) {
                        Schema::create('payments_legacy_stripe', function (Blueprint $table) {
                            $table->id();
                            $table->unsignedBigInteger('payment_id');
                            $table->string('stripe_payment_intent_id')->nullable();
                            $table->string('stripe_charge_id')->nullable();
                            $table->timestamps();
                        });

                        $legacyRows = DB::table('payments')
                            ->whereNotNull('stripe_payment_intent_id')
                            ->select(['id as payment_id', 'stripe_payment_intent_id', 'stripe_charge_id'])
                            ->get()
                            ->map(fn ($r) => (array) $r)
                            ->toArray();

                        if (! empty($legacyRows)) {
                            DB::table('payments_legacy_stripe')->insert($legacyRows);
                        }
                    }
                }

                // Drop legacy stripe columns if present
                Schema::table('payments', function (Blueprint $table) {
                    $table->dropColumn(['stripe_payment_intent_id', 'stripe_charge_id']);
                });
            }
        }

        // 2. Upgrade payment_transactions table
        if (Schema::hasTable('payment_transactions')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                if (! Schema::hasColumn('payment_transactions', 'amount_minor')) {
                    $table->unsignedBigInteger('amount_minor')->nullable()->after('amount');
                }
                if (! Schema::hasColumn('payment_transactions', 'idempotency_key')) {
                    $table->string('idempotency_key', 200)->nullable()->unique()->after('amount_minor');
                }
            });

            DB::table('payment_transactions')->whereNull('amount_minor')->update([
                'amount_minor' => DB::raw('amount'),
            ]);

            if (Schema::hasColumn('payment_transactions', 'gateway_event_id')) {
                DB::table('payment_transactions')->whereNull('idempotency_key')->update([
                    'idempotency_key' => DB::raw("COALESCE(gateway_event_id, 'legacy:' || id)"),
                ]);
            }
        }

        // 3. Create webhook_events table
        if (! Schema::hasTable('webhook_events')) {
            Schema::create('webhook_events', function (Blueprint $table) {
                $table->id();
                $table->string('provider', 50)->default('paystack');
                $table->string('event', 100);
                $table->string('provider_event_key', 255)->unique();
                $table->boolean('signature_valid')->default(false);
                $table->json('payload');
                $table->string('status', 50)->default('received'); // received, processed, failed, ignored
                $table->unsignedInteger('attempts')->default(1);
                $table->text('error')->nullable();
                $table->string('request_id', 100)->nullable();
                $table->timestamp('received_at')->useCurrent();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->index(['provider', 'status']);
                $table->index('received_at');
            });
        }

        // 4. Upgrade appointments table
        if (Schema::hasTable('appointments')) {
            Schema::table('appointments', function (Blueprint $table) {
                if (! Schema::hasColumn('appointments', 'payment_status')) {
                    $table->string('payment_status', 50)->default('unpaid')->after('status');
                }
                if (! Schema::hasColumn('appointments', 'amount_due_minor')) {
                    $table->unsignedBigInteger('amount_due_minor')->default(0)->after('payment_status');
                }
                if (! Schema::hasColumn('appointments', 'payment_due_at')) {
                    $table->timestamp('payment_due_at')->nullable()->after('amount_due_minor');
                }
            });

            DB::table('appointments')->where('payment_status', 'unpaid')->where('deposit_paid', true)->update([
                'payment_status' => 'paid',
            ]);

            DB::table('appointments')->where('amount_due_minor', 0)->update([
                'amount_due_minor' => DB::raw('COALESCE(grand_total, total_price, 0)'),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('payments_legacy_stripe');

        if (Schema::hasTable('appointments')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropColumn(['payment_status', 'amount_due_minor', 'payment_due_at']);
            });
        }

        if (Schema::hasTable('payment_transactions')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                $table->dropColumn(['amount_minor', 'idempotency_key']);
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn([
                    'provider',
                    'reference',
                    'access_code',
                    'authorization_url',
                    'amount_minor',
                    'provider_transaction_id',
                    'channel',
                    'gateway_response',
                    'fees_minor',
                    'paid_at',
                    'refunded_minor',
                    'failure_reason',
                    'client',
                    'customer_email',
                    'expires_at',
                    'flagged_reason',
                ]);
            });
        }
    }
};
