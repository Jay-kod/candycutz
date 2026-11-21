<?php

namespace Database\Factories;

use App\Models\Barber;
use App\Models\BarberService;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BarberService> */
class BarberServiceFactory extends Factory
{
    protected $model = BarberService::class;

    public function definition(): array
    {
        return [
            'barber_id' => Barber::factory(),
            'service_id' => Service::factory(),
            'custom_price' => null,
            'custom_duration' => null,
            'is_offered' => true,
        ];
    }
}
