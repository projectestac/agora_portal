<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Manager extends Model {
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
    ];

    public function client(): BelongsTo {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
