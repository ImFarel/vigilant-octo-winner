<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['user_id', 'chatroom_id', 'message', 'attachments'];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chatroom()
    {
        return $this->belongsTo(Chatroom::class);
    }

    public function getAttachmentsAttribute($value)
    {
        $attachments = json_decode($value, true) ?? [];
        return array_map(function ($path) {
            return asset('storage/' . $path);
        }, $attachments);
    }
}
