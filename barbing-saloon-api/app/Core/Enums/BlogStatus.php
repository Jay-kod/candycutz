<?php

namespace App\Core\Enums;

enum BlogStatus: string
{
    case draft = 'draft';
    case published = 'published';
}