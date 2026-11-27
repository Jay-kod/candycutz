<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Move the legal pages onto CMS-owned settings.
 *
 * The copy below is the previously rendered fallback content, seeded once so
 * the public pages keep working. From here on the database is the source of
 * truth and admins edit it at /admin/websites/terms and /admin/websites/privacy.
 *
 * Existing keys are never overwritten, so re-running or rolling back this
 * migration cannot destroy published legal copy.
 */
return new class extends Migration
{
    public function up(): void
    {
        $termsSections = [
            [
                'heading' => '1. Agreement to Terms',
                'body' => 'By accessing or using CandyCutz services, website, or mobile application, you agree to be bound by these Terms of Service. If you disagree with any part of the terms, then you may not access our services.',
            ],
            [
                'heading' => '2. Booking and Appointments',
                'body' => "We strive to provide excellent service and value your time. Please note our policies regarding appointments:\n\n• Appointments can be booked up to 30 days in advance.\n• We require at least 24 hours notice for cancellations or rescheduling.\n• Late arrivals (more than 15 minutes) may result in a shortened service or cancellation.\n• No-shows may be subject to a fee equal to 50% of the scheduled service cost.",
            ],
            [
                'heading' => '3. Payment and Pricing',
                'body' => 'All prices for our services are subject to change without notice. We accept cash, credit/debit cards, and mobile payments. Payment is due in full at the time of service. For certain premium packages or group bookings, a deposit may be required in advance.',
            ],
            [
                'heading' => '4. Salon Safety & Code of Conduct',
                'body' => 'For the safety and comfort of all clients, CandyCutz adheres to strict sanitation and hygiene standards. Clients are expected to maintain courteous conduct towards barbers and fellow guests. We reserve the right to decline or discontinue service to individuals exhibiting disruptive, intoxicated, or disrespectful behavior.',
            ],
            [
                'heading' => '5. Intellectual Property & Brand Rights',
                'body' => 'The service and its original content, features, and brand imagery are and will remain the exclusive property of CandyCutz and its licensors. Our trademarks, logos, and service dress may not be used in connection with any commercial product or service without prior written consent from CandyCutz.',
            ],
            [
                'heading' => '6. Contact & Dispute Resolution',
                'body' => "If you have any questions or disputes regarding these Terms, please contact our legal and customer operations desk:\n\n• By email: legal@candycutz.com\n• By visiting the contact desk on our website or mobile app.",
            ],
        ];

        $privacySections = [
            [
                'heading' => '1. Introduction',
                'body' => 'Welcome to CandyCutz. We respect your privacy and are committed to protecting your personal data. This privacy policy will inform you as to how we look after your personal data when you visit our website (regardless of where you visit it from) and tell you about your privacy rights and how the law protects you.',
            ],
            [
                'heading' => '2. The Data We Collect About You',
                'body' => "Personal data, or personal information, means any information about an individual from which that person can be identified. We may collect, use, store and transfer different kinds of personal data about you:\n\n• Identity Data includes first name, last name, username or similar identifier.\n• Contact Data includes billing address, email address and telephone numbers.\n• Transaction Data includes details about payments to and from you and other details of services you have purchased from us.\n• Profile Data includes your username and password, purchases or orders made by you, your interests, preferences, feedback and survey responses.",
            ],
            [
                'heading' => '3. How We Use Your Personal Data',
                'body' => 'We will only use your personal data when the law allows us to. Most commonly, we will use your personal data to register you as a new customer, process and deliver your order, manage our relationship with you, and to improve our website, products/services, marketing or customer relationships.',
            ],
            [
                'heading' => '4. Data Security',
                'body' => 'We have put in place appropriate security measures to prevent your personal data from being accidentally lost, used or accessed in an unauthorised way, altered or disclosed. In addition, we limit access to your personal data to those employees, agents, contractors and other third parties who have a business need to know.',
            ],
            [
                'heading' => '5. Your Legal Rights',
                'body' => "Under certain circumstances, you have rights under data protection laws in relation to your personal data, including the right to:\n\n• Request access to your personal data.\n• Request correction of your personal data.\n• Request erasure of your personal data.\n• Object to processing of your personal data.",
            ],
            [
                'heading' => '6. Account Deactivation & Data Erasure (App Store & Play Store Compliance)',
                'body' => "In accordance with Apple Guideline 5.1.1(v) and Google Play user data policies, users have full autonomy to deactivate their accounts and request data deletion:\n\n• In-App Deactivation: You may initiate immediate account deactivation within the CandyCutz mobile app under Profile > Security > Deactivate Account.\n• Web Deletion Request: You can submit an account erasure request at https://candycutz.com/account-deletion or by emailing privacy@candycutz.com.\n• Data Retention Policy: Upon deactivation, your profile is immediately deactivated and active authentication tokens are revoked. Historical financial transaction logs are retained strictly in accordance with statutory accounting and tax regulations before being permanently purged.",
            ],
        ];

        $defaults = [
            ['key' => 'terms_title', 'value' => 'Terms of Service'],
            ['key' => 'terms_subtitle', 'value' => 'The terms that apply when you use CandyCutz services, website, or mobile application.'],
            ['key' => 'terms_sections', 'value' => json_encode($termsSections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)],
            ['key' => 'terms_version', 'value' => '2026-11-26'],
            ['key' => 'privacy_title', 'value' => 'Privacy Policy'],
            ['key' => 'privacy_subtitle', 'value' => 'How we collect, use, and protect your personal information across all platforms.'],
            ['key' => 'privacy_sections', 'value' => json_encode($privacySections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)],
            ['key' => 'privacy_version', 'value' => '2026-11-26'],
            ['key' => 'cookie_policy_version', 'value' => '2026-11-26'],
        ];

        foreach ($defaults as $setting) {
            $exists = DB::table('settings')->where('key', $setting['key'])->exists();

            if ($exists) {
                continue;
            }

            DB::table('settings')->insert([
                'key' => $setting['key'],
                'value' => $setting['value'],
                'group' => 'legal',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Legal content is evidence. Never delete published policy on rollback.
    }
};
