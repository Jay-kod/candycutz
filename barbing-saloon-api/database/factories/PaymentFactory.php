<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Payment> */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'customer_id' => User::factory()->customer(),
            'amount' => fake()->randomFloat(2, 1000, 15000),
            'currency' => 'NGN',
            'status' => 'pending',
            'payment_method' => 'manual_transfer',
            'gateway_reference' => null,
            'gateway_charge_id' => null,
            'transaction_ref' => fake()->unique()->bothify('TXN-########'),
            'receipt_url' => null,
            'error_message' => null,
        ];
    }
}
