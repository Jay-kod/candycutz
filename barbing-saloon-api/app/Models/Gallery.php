<?php

namespace App\Models;

use App\Domain\Shared\Enums\GalleryCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string|null $image_path
 * @property GalleryCategory|string|null $category
 * @property int|null $barber_id
 * @property bool $is_featured
 * @property int $display_order
 * @property-read Barber|null $barber
 */
class Gallery extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'gallery';

    protected $fillable = ['title', 'description', 'image_path', 'category', 'barber_id', 'is_featured', 'display_order'];

    protected $casts = ['category' => GalleryCategory::class, 'is_featured' => 'boolean'];

    /**
     * @return BelongsTo<Barber, $this>
     */
    public function barber(): BelongsTo
    {
        return $this->belongsTo(Barber::class);
    }
}
