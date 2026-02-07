<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    protected $fillable = [
        'user_id',
        'user_query',
        'generated_sql',
        'ai_response',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
