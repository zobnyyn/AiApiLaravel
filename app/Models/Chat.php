<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'messages',
        'model',
    ];

    protected $casts = [
        'messages' => 'array',
    ];

    /**
     * Получить пользователя, которому принадлежит чат.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
