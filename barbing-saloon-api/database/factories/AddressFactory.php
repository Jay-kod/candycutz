<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Address> */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->customer(),
            'title' => 'Home',
            'street' => fake()->streetAddress(),
            'landmark' => fake()->secondaryAddress(),
            'city' => 'Keffi',
            'state' => 'Nasarawa',
            'postal_code' => '961101',
            'latitude' => 8.8471,
            'longitude' => 7.8736,
            'is_default' => false,
        ];
    }
}
