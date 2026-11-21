<?php

use App\Models\Address;
use App\Models\Appointment;
use App\Models\AppointmentItem;
use App\Models\AppointmentStatusHistory;
use App\Models\AuditLog;
use App\Models\Barber;
use App\Models\BarberService;
use App\Models\BlockedPeriod;
use App\Models\BlogPost;
use App\Models\Branch;
use App\Models\Business;
use App\Models\DeviceToken;
use App\Models\Gallery;
use App\Models\Holiday;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceZone;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\ThemeSetting;
use App\Models\ThemeVersion;
use App\Models\User;
use App\Models\WorkingHour;

it('can persist all models via factories', function () {
    $models = [
        Address::class,
        Appointment::class,
        AppointmentItem::class,
        AppointmentStatusHistory::class,
        AuditLog::class,
        Barber::class,
        BarberService::class,
        BlockedPeriod::class,
        BlogPost::class,
        Branch::class,
        Business::class,
        DeviceToken::class,
        Gallery::class,
        Holiday::class,
        Notification::class,
        Payment::class,
        PaymentTransaction::class,
        Service::class,
        ServiceCategory::class,
        ServiceZone::class,
        Setting::class,
        Testimonial::class,
        ThemeSetting::class,
        ThemeVersion::class,
        User::class,
        WorkingHour::class,
    ];

    foreach ($models as $model) {
        $instance = $model::factory()->create();
        expect($instance)->toBeInstanceOf($model);
    }
});
