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

class UpdateBlogPost
{
    use \App\Core\Traits\HasSecureUploads;

    public function execute(BlogPost $blogPost, array $data): BlogPost
    {
        if (isset($data['featured_image']) && $data['featured_image'] instanceof UploadedFile) {
            if ($blogPost->featured_image) {
                $this->deleteFile($blogPost->featured_image);
            }

            $data['featured_image'] = $this->uploadFile($data['featured_image'], 'blog');
        }

        if (isset($data['body'])) {
            $data['content'] = $data['body'];
            unset($data['body']);
        }
        if (isset($data['status'])) {
            $data['is_published'] = $data['status'] === 'published';
            unset($data['status']);
        }

        $blogPost->update($data);return $blogPost->refresh()->load('author');
    }
}
