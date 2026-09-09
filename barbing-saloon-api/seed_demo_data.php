<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Barber;
use App\Models\WorkingHour;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\Setting;
use App\Core\Enums\AppointmentStatus;
use Carbon\Carbon;

echo "=== STARTING DEMO DATA REPAIR & SEEDING ===\n";

// 1. Ensure Marcus Vance (Barber 4) has working hours for all 7 days
$marcusUser = User::where('email', 'marcus@candycutz.com')->first();
if (!$marcusUser) {
    echo "ERROR: Marcus user not found!\n";
    exit(1);
}

$barberCols = Illuminate\Support\Facades\Schema::getColumnListing('barbers');
echo "Barbers columns: " . implode(', ', $barberCols) . "\n";

$marcusBarber = Barber::where('user_id', $marcusUser->id)->first();
$barberData = [
    'is_available' => true,
    'is_featured' => true,
    'rating' => 4.95,
];
if (in_array('status', $barberCols)) {
    $barberData['status'] = 'active';
}
if (!$marcusBarber) {
    $barberData['user_id'] = $marcusUser->id;
    $barberData['bio'] = 'Master barber with over 8 years of artisan grooming experience. Precision fades, bespoke scissor work, and traditional hot towel treatments.';
    $barberData['specialties'] = ['Classic Fade', 'Textured Crop', 'Hot Towel Shave', 'Beard Detail'];
    $barberData['years_experience'] = 8;
    $marcusBarber = Barber::create($barberData);
    echo "Created Marcus Barber record (ID: {$marcusBarber->id})\n";
} else {
    $marcusBarber->update($barberData);
    echo "Updated Marcus Barber record (ID: {$marcusBarber->id})\n";
}
$marcusUser->update(['status' => 'active', 'is_active' => true]);

// Seed Monday (1) to Saturday (6) and Sunday (0) for Barber 4
$schedule = [
    0 => ['open' => '10:00:00', 'close' => '18:00:00', 'closed' => false], // Sunday open for weekend bookings
    1 => ['open' => '08:00:00', 'close' => '19:00:00', 'closed' => false], // Monday
    2 => ['open' => '08:00:00', 'close' => '19:00:00', 'closed' => false], // Tuesday
    3 => ['open' => '08:00:00', 'close' => '19:00:00', 'closed' => false], // Wednesday
    4 => ['open' => '08:00:00', 'close' => '19:00:00', 'closed' => false], // Thursday
    5 => ['open' => '08:00:00', 'close' => '19:00:00', 'closed' => false], // Friday
    6 => ['open' => '08:00:00', 'close' => '19:00:00', 'closed' => false], // Saturday
];

foreach ($schedule as $day => $times) {
    WorkingHour::updateOrCreate(
        ['barber_id' => $marcusBarber->id, 'day_of_week' => $day],
        [
            'open_time' => $times['open'],
            'close_time' => $times['close'],
            'is_closed' => $times['closed'],
        ]
    );
}
echo "Working hours seeded for Marcus Vance (7 days configured, Sunday open 10am-6pm, Mon-Sat 8am-7pm).\n";

// Also ensure Barbers 1, 2, 3 have Sunday open or standard hours
for ($bId = 1; $bId <= 3; $bId++) {
    WorkingHour::updateOrCreate(
        ['barber_id' => $bId, 'day_of_week' => 0],
        ['open_time' => '10:00:00', 'close_time' => '18:00:00', 'is_closed' => false]
    );
}

// 2. Setup Customer 15 (Chinedu Okafor)
$customerUser = User::where('email', 'customer@candycutz.com')->first();
if (!$customerUser) {
    echo "ERROR: customer@candycutz.com not found!\n";
    exit(1);
}

// Ensure first services exist
$services = Service::all();
if ($services->isEmpty()) {
    echo "ERROR: No services found!\n";
    exit(1);
}
$service1 = $services->get(0);
$service2 = $services->get(1) ?? $service1;
$service3 = $services->get(2) ?? $service1;

// 3. Clear existing dummy appointments for customer 15 and barber 4 to avoid duplicates
Appointment::where('customer_id', $customerUser->id)->delete();
Appointment::where('barber_id', $marcusBarber->id)->delete();

$today = Carbon::today();
$yesterday = Carbon::yesterday();
$twoDaysAgo = Carbon::today()->subDays(2);
$threeDaysAgo = Carbon::today()->subDays(3);
$tomorrow = Carbon::tomorrow();
$inTwoDays = Carbon::today()->addDays(2);

// Appointment 1: Today for Customer 15 with Barber 4 (Marcus Vance) - CONFIRMED
Appointment::create([
    'booking_reference' => 'BK-' . strtoupper(bin2hex(random_bytes(4))),
    'customer_id' => $customerUser->id,
    'client_name' => $customerUser->name,
    'client_phone' => $customerUser->phone ?: '08031234567',
    'client_email' => $customerUser->email,
    'barber_id' => $marcusBarber->id,
    'service_id' => $service1->id,
    'appointment_type' => 'in_shop',
    'appointment_date' => $today->toDateString(),
    'appointment_time' => '14:00:00',
    'end_time' => '14:45:00',
    'total_duration_minutes' => $service1->duration_minutes ?: 45,
    'total_amount' => $service1->price,
    'total_price' => $service1->price,
    'grand_total' => $service1->price,
    'deposit_paid' => true,
    'deposit_amount' => 500.00,
    'status' => AppointmentStatus::confirmed->value,
    'notes' => 'Looking for a sharp taper fade and beard lineup.',
]);

// Appointment 2: Tomorrow for Customer 15 with Barber 4 (Marcus Vance) - PENDING
Appointment::create([
    'booking_reference' => 'BK-' . strtoupper(bin2hex(random_bytes(4))),
    'customer_id' => $customerUser->id,
    'client_name' => $customerUser->name,
    'client_phone' => $customerUser->phone ?: '08031234567',
    'client_email' => $customerUser->email,
    'barber_id' => $marcusBarber->id,
    'service_id' => $service2->id,
    'appointment_type' => 'in_shop',
    'appointment_date' => $tomorrow->toDateString(),
    'appointment_time' => '11:00:00',
    'end_time' => '11:45:00',
    'total_duration_minutes' => $service2->duration_minutes ?: 45,
    'total_amount' => $service2->price,
    'total_price' => $service2->price,
    'grand_total' => $service2->price,
    'deposit_paid' => false,
    'deposit_amount' => 500.00,
    'status' => AppointmentStatus::pending->value,
    'notes' => 'Regular maintenance and hot towel treatment.',
]);

// Appointment 3: Yesterday for Customer 15 with Barber 4 (Marcus Vance) - COMPLETED
Appointment::create([
    'booking_reference' => 'BK-' . strtoupper(bin2hex(random_bytes(4))),
    'customer_id' => $customerUser->id,
    'client_name' => $customerUser->name,
    'client_phone' => $customerUser->phone ?: '08031234567',
    'client_email' => $customerUser->email,
    'barber_id' => $marcusBarber->id,
    'service_id' => $service1->id,
    'appointment_type' => 'in_shop',
    'appointment_date' => $yesterday->toDateString(),
    'appointment_time' => '15:30:00',
    'end_time' => '16:15:00',
    'total_duration_minutes' => $service1->duration_minutes ?: 45,
    'total_amount' => $service1->price,
    'total_price' => $service1->price,
    'grand_total' => $service1->price,
    'deposit_paid' => true,
    'deposit_amount' => 500.00,
    'status' => AppointmentStatus::completed->value,
    'notes' => 'Executive trim before business meeting.',
]);

// Appointment 4: 3 Days ago for Customer 15 with Barber 1 - COMPLETED
Appointment::create([
    'booking_reference' => 'BK-' . strtoupper(bin2hex(random_bytes(4))),
    'customer_id' => $customerUser->id,
    'client_name' => $customerUser->name,
    'client_phone' => $customerUser->phone ?: '08031234567',
    'client_email' => $customerUser->email,
    'barber_id' => 1,
    'service_id' => $service3->id,
    'appointment_type' => 'in_shop',
    'appointment_date' => $threeDaysAgo->toDateString(),
    'appointment_time' => '10:00:00',
    'end_time' => '10:30:00',
    'total_duration_minutes' => $service3->duration_minutes ?: 30,
    'total_amount' => $service3->price,
    'total_price' => $service3->price,
    'grand_total' => $service3->price,
    'deposit_paid' => true,
    'deposit_amount' => 500.00,
    'status' => AppointmentStatus::completed->value,
    'notes' => 'First visit to Candycutz.',
]);

// Appointment 5: Additional appointment for Marcus Vance with another customer (Customer 6) - TODAY COMPLETED
$otherCustomer = User::where('role', 'customer')->where('id', '!=', $customerUser->id)->first();
if ($otherCustomer) {
    Appointment::create([
        'booking_reference' => 'BK-' . strtoupper(bin2hex(random_bytes(4))),
        'customer_id' => $otherCustomer->id,
        'client_name' => $otherCustomer->name,
        'client_phone' => $otherCustomer->phone ?: '08039876543',
        'client_email' => $otherCustomer->email,
        'barber_id' => $marcusBarber->id,
        'service_id' => $service2->id,
        'appointment_type' => 'in_shop',
        'appointment_date' => $today->toDateString(),
        'appointment_time' => '09:00:00',
        'end_time' => '09:45:00',
        'total_duration_minutes' => 45,
        'total_amount' => $service2->price,
        'total_price' => $service2->price,
        'grand_total' => $service2->price,
        'deposit_paid' => true,
        'deposit_amount' => 500.00,
        'status' => AppointmentStatus::completed->value,
        'notes' => 'Early morning cut.',
    ]);

    // Appointment 6: In two days for Marcus Vance with Customer 6 - CONFIRMED
    Appointment::create([
        'booking_reference' => 'BK-' . strtoupper(bin2hex(random_bytes(4))),
        'customer_id' => $otherCustomer->id,
        'client_name' => $otherCustomer->name,
        'client_phone' => $otherCustomer->phone ?: '08039876543',
        'client_email' => $otherCustomer->email,
        'barber_id' => $marcusBarber->id,
        'service_id' => $service3->id,
        'appointment_type' => 'in_shop',
        'appointment_date' => $inTwoDays->toDateString(),
        'appointment_time' => '13:00:00',
        'end_time' => '13:30:00',
        'total_duration_minutes' => 30,
        'total_amount' => $service3->price,
        'total_price' => $service3->price,
        'grand_total' => $service3->price,
        'deposit_paid' => true,
        'deposit_amount' => 500.00,
        'status' => AppointmentStatus::confirmed->value,
        'notes' => 'Beard shape and wash.',
    ]);

    // Appointment 7: Marcus Vance No Show
    Appointment::create([
        'booking_reference' => 'BK-' . strtoupper(bin2hex(random_bytes(4))),
        'customer_id' => $otherCustomer->id,
        'client_name' => $otherCustomer->name,
        'client_phone' => $otherCustomer->phone ?: '08039876543',
        'client_email' => $otherCustomer->email,
        'barber_id' => $marcusBarber->id,
        'service_id' => $service1->id,
        'appointment_type' => 'in_shop',
        'appointment_date' => $twoDaysAgo->toDateString(),
        'appointment_time' => '16:00:00',
        'end_time' => '16:45:00',
        'total_duration_minutes' => 45,
        'total_amount' => $service1->price,
        'total_price' => $service1->price,
        'grand_total' => $service1->price,
        'deposit_paid' => false,
        'deposit_amount' => 500.00,
        'status' => AppointmentStatus::no_show->value,
        'notes' => 'Customer did not arrive.',
    ]);
}

// 4. Testimonial for Customer 15
Testimonial::updateOrCreate(
    ['customer_id' => $customerUser->id],
    [
        'client_name' => $customerUser->name,
        'client_avatar' => $customerUser->avatar,
        'barber_id' => $marcusBarber->id,
        'service_id' => $service1->id,
        'rating' => 5,
        'review' => 'Marcus Vance gave me the cleanest fade I have had in Nasarawa. Exceptional precision and top-notch hospitality!',
        'is_approved' => true,
        'is_featured' => true,
    ]
);

echo "Appointments and testimonial seeded successfully!\n";
echo "Customer 15 Appointments count: " . Appointment::where('customer_id', $customerUser->id)->count() . "\n";
echo "Barber 4 Appointments count: " . Appointment::where('barber_id', $marcusBarber->id)->count() . "\n";
echo "=== SEEDING COMPLETED ===\n";
