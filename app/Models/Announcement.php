<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'sent_by',
        'recipients_count',
    ];

    protected $casts = [
        'recipients_count' => 'integer',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
