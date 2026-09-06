<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * We specify it just to be safe, though Laravel infers it.
     */
    protected $table = 'notifications';

    public $timestamps = true;

    protected $fillable = [
        'sender_id',
        'recipient_type',
        'recipient_id',
        'type',
        'title',
        'message',
        'related_entity_id',
        'is_read',
        'created_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
