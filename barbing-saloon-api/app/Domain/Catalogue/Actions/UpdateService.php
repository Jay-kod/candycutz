<?php

namespace App\Domain\Catalogue\Actions;

use App\Domain\Shared\Traits\HasSecureUploads;
use App\Models\Service;
use Illuminate\Http\UploadedFile;

class UpdateService
{
    use HasSecureUploads;

    public function execute(Service $service, array $data): Service
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            if ($service->image) {
                $this->deleteFile($service->image);
            }

            $data['image'] = $this->uploadFile($data['image'], 'services');
        } else {
            unset($data['image']);
        }

        unset($data['slug']);
        unset($data['is_active']);
        unset($data['display_order']);

        $service->update($data);

        return $service->refresh()->load('category');
    }
}
