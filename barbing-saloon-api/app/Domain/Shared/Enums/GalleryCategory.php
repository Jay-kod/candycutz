<?php

namespace App\Domain\Shared\Enums;

enum GalleryCategory: string
{
    case haircut = 'haircut';
    case beard = 'beard';
    case combo = 'combo';
    case before_after = 'before_after';
    case shop = 'shop';
}
