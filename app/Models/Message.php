<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Translatable\HasTranslations;
use App\Traits\AutoTranslateTranslations;

class Message extends Model
{
    use HasFactory, HasTranslations, AutoTranslateTranslations;

    public array $translatable = ['message'];

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message',
        'attachment',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
