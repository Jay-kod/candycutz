<?php

namespace App\Modules\SuperAdmin\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'user_role' => $this->user_role,
            'action' => $this->action,
            'module' => $this->module,
            'target_type' => $this->target_type,
            'target_id' => $this->target_id,
            'old_value' => $this->old_value,
            'new_value' => $this->new_value,
            'ip_address' => $this->ip_address,
            'created_at' => $this->created_at,
        ];
    }
}
