<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Notification
 */
class NotificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User|null $user */
        $user = $request->user();
        $isRead = $user ? $this->isReadByUser($user) : (bool) $this->is_read;

        return [
            'id' => $this->id,
            'type' => $this->type,
            'category' => $this->category,
            'priority' => $this->priority ?? 'normal',
            'title' => $this->title,
            'message' => $this->message,
            'action_url' => $this->action_url,
            'data' => $this->data,
            'recipient_type' => $this->recipient_type,
            'recipient_id' => $this->recipient_id,
            'sender_id' => $this->sender_id,
            'related_entity_id' => $this->related_entity_id,
            'is_read' => $isRead,
            'created_at' => $this->created_at?->toISOString() ?? now()->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'time_ago' => $this->created_at?->diffForHumans() ?? 'just now',
        ];
    }
}
