<?php

namespace App\Domain\Shared\Enums;

enum BlogStatus: string
{
    case draft = 'draft';
    case published = 'published';
}
