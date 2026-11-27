<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Shared\Enums\BlogStatus;
use App\Domain\Shared\Services\CatalogueCache;
use App\Models\Barber;
use App\Models\BlogPost;
use App\Models\Branch;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $barber = Barber::first();
        $customer = User::where('role', 'customer')->first();
        $branch = Branch::first();

        // ── 1. Four Demo Categories ──
        $categoriesData = [
            [
                'name' => 'Signature Haircuts',
                'slug' => 'signature-haircuts',
                'icon' => 'scissors',
                'display_order' => 1,
            ],
            [
                'name' => 'Beard & Shave Lounge',
                'slug' => 'beard-shave-lounge',
                'icon' => 'sparkles',
                'display_order' => 2,
            ],
            [
                'name' => 'Royal VIP Treatments',
                'slug' => 'royal-vip-treatments',
                'icon' => 'crown',
                'display_order' => 3,
            ],
            [
                'name' => 'Junior & Student Cuts',
                'slug' => 'junior-student-cuts',
                'icon' => 'user-group',
                'display_order' => 4,
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['slug']] = ServiceCategory::updateOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name' => $cat['name'],
                    'icon' => $cat['icon'],
                    'display_order' => $cat['display_order'],
                    'is_active' => true,
                ]
            );
        }

        // ── 2. Four Demo Services ──
        $servicesData = [
            [
                'category_slug' => 'signature-haircuts',
                'name' => 'The Executive Royal Fade',
                'slug' => 'the-executive-royal-fade',
                'description' => 'Precision skin fade or classic taper, steam hot towel neck shave, crisp razor edge-up, and matte clay styling finish.',
                'price' => 5000,
                'duration_minutes' => 45,
                'home_service_allowed' => true,
                'is_featured' => true,
                'display_order' => 1,
            ],
            [
                'category_slug' => 'beard-shave-lounge',
                'name' => 'Hot Towel Beard Sculpt & Hydration',
                'slug' => 'hot-towel-beard-sculpt-hydration',
                'description' => 'Deep botanical beard wash, eucalyptus hot towel wrap, precision razor line-up, and nourishing organic growth oil hydration.',
                'price' => 3500,
                'duration_minutes' => 35,
                'home_service_allowed' => true,
                'is_featured' => false,
                'display_order' => 2,
            ],
            [
                'category_slug' => 'royal-vip-treatments',
                'name' => 'The CandyCutz VIP Experience',
                'slug' => 'the-candycutz-vip-experience',
                'description' => 'All-inclusive luxury grooming: Signature haircut, full beard detailing, revitalizing charcoal peel-off mask, relaxing scalp massage, and gold finish.',
                'price' => 12000,
                'duration_minutes' => 75,
                'home_service_allowed' => true,
                'is_featured' => true,
                'display_order' => 3,
            ],
            [
                'category_slug' => 'junior-student-cuts',
                'name' => 'Campus Fade & Clean Lineup',
                'slug' => 'campus-fade-and-clean-lineup',
                'description' => 'Sharp, modern student fade or clean buzz cut tailored to your natural hairline, finished with cooling skin balm.',
                'price' => 3000,
                'duration_minutes' => 30,
                'home_service_allowed' => false,
                'is_featured' => false,
                'display_order' => 4,
            ],
        ];

        $services = [];
        foreach ($servicesData as $svc) {
            $cat = $categories[$svc['category_slug']] ?? null;
            if (! $cat) {
                continue;
            }

            $services[$svc['slug']] = Service::updateOrCreate(
                ['slug' => $svc['slug']],
                [
                    'branch_id' => $branch?->id,
                    'category_id' => $cat->id,
                    'barber_id' => $barber?->id,
                    'name' => $svc['name'],
                    'description' => $svc['description'],
                    'price' => $svc['price'],
                    'duration_minutes' => $svc['duration_minutes'],
                    'home_service_allowed' => $svc['home_service_allowed'],
                    'image' => 'images/services/service-1.png',
                    'image2' => 'images/gallery/fade-1.png',
                    'image3' => 'images/gallery/beard-1.png',
                    'is_active' => true,
                    'is_available' => true,
                    'approval_status' => 'approved',
                    'approved_by' => $admin?->id,
                    'approved_at' => now(),
                    'is_featured' => $svc['is_featured'],
                    'display_order' => $svc['display_order'],
                ]
            );
        }

        // ── 3. Four Demo Reviews (Testimonials) ──
        $reviewsData = [
            [
                'client_name' => 'Kefas D. (Verified Customer)',
                'rating' => 5,
                'review' => 'Best fade I have gotten in Keffi! The precision and razor edge-up were exceptional. The hot towel finish made the entire experience feel truly executive.',
                'service_slug' => 'the-executive-royal-fade',
                'is_featured' => true,
            ],
            [
                'client_name' => 'Dr. Ibrahim Abubakar',
                'rating' => 5,
                'review' => 'The CandyCutz VIP treatment is worth every naira. Comfortable VIP lounge, excellent hygiene standards, and a master barber who listens carefully to your instructions.',
                'service_slug' => 'the-candycutz-vip-experience',
                'is_featured' => true,
            ],
            [
                'client_name' => 'Chinedu Eze',
                'rating' => 5,
                'review' => 'I booked a home-service haircut on Saturday morning. The barber arrived right on time with fully sanitized tools and delivered a flawless beard trim and fade. Extremely convenient!',
                'service_slug' => 'hot-towel-beard-sculpt-hydration',
                'is_featured' => true,
            ],
            [
                'client_name' => 'Tosin Alabi',
                'rating' => 5,
                'review' => 'Super friendly staff and crisp student pricing. My campus fade was sharp and lasted fresh for over two weeks. Definitely making CandyCutz my permanent shop.',
                'service_slug' => 'campus-fade-and-clean-lineup',
                'is_featured' => true,
            ],
        ];

        foreach ($reviewsData as $rev) {
            $service = $services[$rev['service_slug']] ?? null;

            Testimonial::updateOrCreate(
                ['client_name' => $rev['client_name']],
                [
                    'customer_id' => $customer?->id,
                    'client_avatar' => 'testimonials/candycutz-sample-avatar.png',
                    'rating' => $rev['rating'],
                    'review' => $rev['review'],
                    'service_id' => $service?->id,
                    'barber_id' => $barber?->id,
                    'is_approved' => true,
                    'is_featured' => $rev['is_featured'],
                ]
            );
        }

        // ── 4. Four Demo Blogs ──
        $blogsData = [
            [
                'title' => '5 Essential Beard Grooming Habits Every Man Should Follow',
                'slug' => '5-essential-beard-grooming-habits',
                'excerpt' => 'From daily hydration with natural oils to proper trimming techniques, discover how to grow and maintain a healthier, softer, and fuller beard.',
                'body' => "## The Modern Gentleman's Beard Guide\n\nGrowing a beard is easy, but keeping it looking sharp, clean, and healthy requires intentional daily care. Here are five simple habits every man should adopt:\n\n### 1. Wash and Condition Regularly\nYour facial hair faces sweat, pollution, and food particles daily. Use a dedicated sulfate-free beard wash twice a week to cleanse without stripping natural moisture.\n\n### 2. Hydrate with Botanical Beard Oil\nBeard oil is not just for the hair—it nourishes the skin underneath. Apply 3-4 drops right after showering to lock in moisture and eliminate beard itch.\n\n### 3. Comb Daily with a Wooden Comb\nA wide-toothed wooden comb distributes natural sebum evenly, trains hair growth in a uniform direction, and avoids static frizz.\n\n### 4. Define Your Neckline Correctly\nThe most common DIY grooming mistake is trimming the neckline too high. Aim two fingers above your Adam's apple and follow a gentle curved line towards your ears.\n\n### 5. Visit a Professional Barber for Shaping\nA bi-weekly professional line-up ensures symmetry and sharp contours that complement your face shape.",
                'days_ago' => 3,
            ],
            [
                'title' => 'How to Keep Your Skin Fade Crisp and Fresh Between Cuts',
                'slug' => 'how-to-keep-skin-fade-crisp-between-cuts',
                'excerpt' => 'Simple habits to preserve clean edges, protect against razor bumps, and keep your haircut looking sharp between salon appointments.',
                'body' => "## Maximizing the Lifespan of Your Fade\n\nA skin fade looks incredible on day one, but how do you maintain that crisp gradient as the days progress? Here is how to keep your cut looking clean:\n\n### Maintain Scalp Hydration\nA dry scalp turns a sharp fade dull quickly. Use a lightweight non-comedogenic scalp moisturizer or aloe-based mist each morning.\n\n### Protect Your Hair at Night\nSleeping with a satin durag or on a silk pillowcase prevents friction that rubs down clean edges and causes uneven texture.\n\n### Brush Consistently\nDaily brushing stimulates blood flow to the scalp and keeps short hair aligned in its natural growth pattern.\n\n### Prevent Razor Bumps\nApply an alcohol-free cooling aftershave with tea tree or witch hazel to soothe the skin and prevent irritation.\n\n### Book Trims on Schedule\nFor low and mid fades, a touch-up every 10 to 14 days keeps the gradient seamless.",
                'days_ago' => 5,
            ],
            [
                'title' => 'The Executive Grooming Guide: Preparing for Special Occasions',
                'slug' => 'executive-grooming-guide-special-occasions',
                'excerpt' => 'Whether it is a wedding, boardroom presentation, or red carpet event, here is your essential grooming timeline for peak appearance.',
                'body' => "## The High-Stakes Grooming Blueprint\n\nWhen a significant milestone arrives, your grooming should project authority and sophistication. Follow this timeline for flawless results:\n\n### 3 Days Before the Event\nGet your primary haircut and beard detailing done. This gives the cut 48-72 hours to settle naturally with your skin tone.\n\n### 2 Days Before\nUndergo a deep facial treatment or charcoal peel-off mask. Removing dead skin cells and clearing pores leaves your face radiant without redness on event day.\n\n### The Night Before\nGet 7-8 hours of restful sleep, drink plenty of water, and avoid high-sodium foods to prevent morning facial puffiness.\n\n### Event Morning\nA quick shower, light matte clay for hair texture, a dab of beard balm for shine, and your signature cologne placed on pulse points.\n\nConfidence begins with clean grooming.",
                'days_ago' => 8,
            ],
            [
                'title' => 'Why Scalp Health is the Secret to Thicker, Stronger Hair',
                'slug' => 'scalp-health-secret-thicker-stronger-hair',
                'excerpt' => 'Healthy hair starts below the surface. Learn how scalp massage, deep exfoliation, and hydration protect against thinning and flaking.',
                'body' => "## Don't Neglect the Root System\n\nMost people spend time thinking about the hair they can see, while ignoring the skin that produces it. Understanding scalp health is the single most effective way to prevent thinning:\n\n### 1. Remove Product Build-up\nPomades, gels, and oils accumulate over time, clogging hair follicles. An occasional clarifying wash or scalp scrub detoxifies the pores.\n\n### 2. Boost Circulation with Scalp Massage\nA 5-minute daily massage with fingertip pads increases blood supply to the roots, delivering essential nutrients to active follicles.\n\n### 3. The Benefits of Hot Towel Steam\nOur in-shop steam hot towel treatments open pores and soften follicles, promoting relaxation and superior product absorption.\n\n### 4. Hydration From the Inside Out\nDrink sufficient water and incorporate foods rich in zinc, biotin, and vitamins A & E to support strong keratin synthesis.",
                'days_ago' => 12,
            ],
        ];

        $hasAuthorDisplay = Schema::hasColumn('blog_posts', 'author_display');

        foreach ($blogsData as $post) {
            $payload = [
                'title' => $post['title'],
                'excerpt' => $post['excerpt'],
                'body' => $post['body'],
                'featured_image' => 'images/blog/blog-1.png',
                'author_id' => $admin?->id ?? 1,
                'status' => BlogStatus::published,
                'published_at' => now()->subDays($post['days_ago']),
            ];

            if ($hasAuthorDisplay) {
                $payload['author_display'] = 'Master Barber & Style Director';
            }

            BlogPost::updateOrCreate(
                ['slug' => $post['slug']],
                $payload
            );
        }

        // Bust catalogue caches
        try {
            CatalogueCache::bust('services');
            CatalogueCache::bust('categories');
            CatalogueCache::bust('testimonials');
            CatalogueCache::bust('blog');
        } catch (\Throwable) {
            // Ignore if cache driver not active
        }
    }
}
