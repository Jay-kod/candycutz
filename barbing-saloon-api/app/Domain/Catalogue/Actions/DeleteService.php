<?php

namespace App\Domain\Catalogue\Actions;

use App\Domain\Shared\Traits\HasSecureUploads;
use App\Models\Service;

class DeleteService
{
    use HasSecureUploads;

    public function execute(Service $service): void
    {
        if ($service->image) {
            $this->deleteFile($service->image);
        }

        $service->delete();
    }
}
