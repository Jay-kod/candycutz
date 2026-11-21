<?php

declare(strict_types=1);

namespace App\Support\Errors;

class UploadException extends AppException
{
    public static function invalidFile(?string $message = null, array $details = []): self
    {
        return new self(ErrorCode::UPLOAD_INVALID, $message, $details);
    }
}
