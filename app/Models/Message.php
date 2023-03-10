<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['from_u', 'to_u', 'body', 'is_read'];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:m:s',
        'is_read' => 'boolean',
    ];

    public function from(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_u', 'id');
    }

    public function to(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_u', 'id');
    }
}
