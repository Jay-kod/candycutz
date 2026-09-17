<?php

namespace App\Domain\Content\Actions;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\BlogPost;
use App\Models\Gallery;
use App\Models\Holiday;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\WorkingHour;
use App\Models\User;
use App\Core\Enums\AppointmentStatus;
use App\Core\Enums\BlogStatus;
use App\Core\Enums\GalleryCategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class CreateBlogPost
{
    use \App\Core\Traits\HasSecureUploads;

    public function execute(User $author, array $data): BlogPost
    {
        $imagePath = isset($data['featured_image']) && $data['featured_image'] instanceof UploadedFile
            ? $this->uploadFile($data['featured_image'], 'blog')
            : null;

        return BlogPost::query()->create([
            'title' => $data['title'],
            'slug' => $data['slug'] ?? \Illuminate\Support\Str::slug($data['title']) . '-' . time(),
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['body'] ?? $data['content'] ?? '',
            'featured_image' => $imagePath,
            'author_id' => $author->id,
            'is_published' => isset($data['status']) ? ($data['status'] === 'published') : true,
        ]);
    }
}
