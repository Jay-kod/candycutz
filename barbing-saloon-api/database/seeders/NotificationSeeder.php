<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\NotificationRead;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('email', 'kefas@customer.com')->first()
            ?? User::where('role', 'customer')->first();

        $barberUser = User::where('email', 'obo@barber.com')->first()
            ?? User::where('role', 'barber')->first();

        $admin = User::where('email', 'webadmin@candycutz.com')->first()
            ?? User::where('role', 'admin')->first();

        // 1. Customer Notifications
        if ($customer) {
            Notification::firstOrCreate(
                [
                    'recipient_id' => $customer->id,
                    'title' => 'Booking Confirmed ✂️',
                ],
                [
                    'recipient_type' => 'customer',
                    'type' => 'booking_created',
                    'message' => 'Your appointment for Signature Royal Fade on tomorrow at 14:00 has been booked. Ref: CC-DEMO-01',
                    'related_entity_id' => 1,
                    'is_read' => false,
                    'created_at' => now()->subMinutes(15),
                    'updated_at' => now()->subMinutes(15),
                ]
            );

            Notification::firstOrCreate(
                [
                    'recipient_id' => $customer->id,
                    'title' => 'Payment Confirmed 💳',
                ],
                [
                    'recipient_type' => 'customer',
                    'type' => 'payment_verified',
                    'message' => 'Your deposit payment of ₦5,000 for booking CC-DEMO-01 has been confirmed.',
                    'related_entity_id' => 1,
                    'is_read' => false,
                    'created_at' => now()->subMinutes(10),
                    'updated_at' => now()->subMinutes(10),
                ]
            );

            Notification::firstOrCreate(
                [
                    'recipient_id' => $customer->id,
                    'title' => 'Review Published 🎉',
                ],
                [
                    'recipient_type' => 'customer',
                    'type' => 'review_approved',
                    'message' => 'Your review for VIP Fade has been verified and published! Thank you for your feedback.',
                    'related_entity_id' => 1,
                    'is_read' => true,
                    'created_at' => now()->subDays(2),
                    'updated_at' => now()->subDays(2),
                ]
            );
        }

        // 2. Barber Notifications
        if ($barberUser) {
            Notification::firstOrCreate(
                [
                    'recipient_id' => $barberUser->id,
                    'title' => 'New Booking Assigned 📋',
                ],
                [
                    'recipient_type' => 'barber',
                    'type' => 'booking_created',
                    'message' => 'Demo Customer booked Signature Royal Fade for tomorrow at 14:00. Ref: CC-DEMO-01',
                    'related_entity_id' => 1,
                    'is_read' => false,
                    'created_at' => now()->subMinutes(14),
                    'updated_at' => now()->subMinutes(14),
                ]
            );

            Notification::firstOrCreate(
                [
                    'recipient_id' => $barberUser->id,
                    'title' => 'New Review Received ⭐⭐⭐⭐⭐',
                ],
                [
                    'recipient_type' => 'barber',
                    'type' => 'review_submitted',
                    'message' => 'Demo Customer left a 5-star review: "Masterful fade, incredibly sharp styling and great ambience!"',
                    'related_entity_id' => 1,
                    'is_read' => false,
                    'created_at' => now()->subHours(1),
                    'updated_at' => now()->subHours(1),
                ]
            );

            Notification::firstOrCreate(
                [
                    'recipient_id' => $barberUser->id,
                    'title' => 'Category Request Approved 🎉',
                ],
                [
                    'recipient_type' => 'barber',
                    'type' => 'category_status_changed',
                    'message' => 'Your request for category "Beard Spa & Steam" has been approved and is now active.',
                    'related_entity_id' => 1,
                    'is_read' => true,
                    'created_at' => now()->subDays(1),
                    'updated_at' => now()->subDays(1),
                ]
            );
        }

        // 3. Admin Notifications (Direct + Broadcast)
        Notification::firstOrCreate(
            [
                'recipient_type' => 'all_admins',
                'title' => 'New Review for Moderation ⭐️',
            ],
            [
                'recipient_id' => null,
                'type' => 'review_submitted',
                'message' => 'Demo Customer submitted a 5-star review awaiting approval.',
                'related_entity_id' => 1,
                'is_read' => false,
                'created_at' => now()->subMinutes(55),
                'updated_at' => now()->subMinutes(55),
            ]
        );

        Notification::firstOrCreate(
            [
                'recipient_type' => 'all_admins',
                'title' => 'New Service Category Request 🏷️',
            ],
            [
                'recipient_id' => null,
                'type' => 'category_requested',
                'message' => 'Demo Barber requested a new category: "Luxury Grooming".',
                'related_entity_id' => 1,
                'is_read' => false,
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ]
        );

        Notification::firstOrCreate(
            [
                'recipient_type' => 'all_admins',
                'title' => 'New User Feedback (Feature request) 💬',
            ],
            [
                'recipient_id' => null,
                'type' => 'feedback_submitted',
                'message' => 'Demo Customer submitted feedback: "Consider adding Apple Pay & Google Pay direct checkout options."',
                'related_entity_id' => 1,
                'is_read' => false,
                'created_at' => now()->subHours(4),
                'updated_at' => now()->subHours(4),
            ]
        );

        // 4. Global Broadcast Notification
        $broadcast = Notification::firstOrCreate(
            [
                'recipient_type' => 'all',
                'title' => 'Welcome to CandyCutz 2.0 💈',
            ],
            [
                'recipient_id' => null,
                'type' => 'system_update',
                'message' => 'We have enhanced our digital appointment suite with real-time notifications, luxury styling, and instant confirmation.',
                'related_entity_id' => null,
                'is_read' => false,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ]
        );

        // Simulate customer having read this broadcast, while barber and admin have it unread
        if ($customer && $broadcast) {
            NotificationRead::firstOrCreate(
                [
                    'notification_id' => $broadcast->id,
                    'user_id' => $customer->id,
                ],
                [
                    'read_at' => now()->subDays(2),
                ]
            );
        }
    }
}
