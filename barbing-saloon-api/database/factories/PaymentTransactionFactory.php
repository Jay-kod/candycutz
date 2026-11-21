<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PaymentTransaction> */
class PaymentTransactionFactory extends Factory
{
    protected $model = PaymentTransaction::class;

    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'transaction_type' => 'authorization',
            'gateway' => 'paystack',
            'gateway_event_id' => fake()->unique()->bothify('evt_########'),
            'amount' => fake()->randomFloat(2, 1000, 15000),
            'raw_payload' => [],
            'status' => 'pending',
            'created_at' => now(),
        ];
    }
}
