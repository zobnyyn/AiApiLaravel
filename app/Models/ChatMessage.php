<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
 protected $table = 'chat_messages';

    protected $fillable = [
        'user_id',
        'chat_id',
        'role',
        'content',
        'model',
    ];

    public $timestamps = true;
}
