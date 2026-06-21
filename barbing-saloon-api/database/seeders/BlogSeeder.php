<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $authorId = User::where('role', 'admin')->value('id') ?? User::query()->value('id');
        $posts = [
            ['title' => 'How to Keep a Fade Sharp for Longer', 'excerpt' => 'Small care habits that help your haircut stay fresh.', 'body' => 'Use a light moisturizer, brush regularly, and book trims on time.', 'status' => 'published'],
            ['title' => 'Best Beard Care Routine for Busy Men', 'excerpt' => 'A simple routine for cleaner lines and healthier facial hair.', 'body' => 'Wash, condition, trim, and shape consistently for the best beard look.', 'status' => 'published'],
            ['title' => 'Why Regular Haircuts Improve Your Style', 'excerpt' => 'A fresh cut can improve confidence and overall appearance.', 'body' => 'Consistent grooming keeps your style intentional and polished.', 'status' => 'draft'],
            ['title' => 'Preparing for a Wedding or Special Event', 'excerpt' => 'Plan your grooming session before the big day.', 'body' => 'Book ahead, choose a clean style, and leave time for final touch-ups.', 'status' => 'published'],
            ['title' => 'What to Ask Your Barber Before a New Style', 'excerpt' => 'Good communication leads to better results.', 'body' => 'Bring reference photos, mention your routine, and discuss face shape.', 'status' => 'draft'],
        ];

        foreach ($posts as $index => $post) {
            $slug = Str::slug($post['title']);

            BlogPost::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'body' => $post['body'],
                    'featured_image' => null,
                    'author_id' => $authorId,
                    'status' => $post['status'],
                    'published_at' => $post['status'] === 'published' ? now()->subDays(10 - $index) : null,
                ]
            );
        }
    }
}