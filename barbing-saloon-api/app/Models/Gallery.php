<?php

namespace App\Models;

use App\Core\Enums\GalleryCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gallery extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'description', 'image_path', 'category', 'barber_id', 'is_featured', 'display_order'];

    protected $casts = ['category' => GalleryCategory::class, 'is_featured' => 'boolean'];

    public function barber(): BelongsTo
    {
        return $this->belongsTo(Barber::class);
    }
}