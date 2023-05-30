<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model
{
    use HasFactory;

    protected $table = 'standard_logs';

    protected $fillable = [
        'client_id',
        'user_id',
        'action_type',
        'action_description',
    ];

    public const ACTION_TYPE_ADMIN = -1;
    public const ACTION_TYPE_ADD = 1;
    public const ACTION_TYPE_EDIT = 2;
    public const ACTION_TYPE_DELETE = 3;
    public const ACTION_TYPE_ERROR = 4;

    public function client(): BelongsTo {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

}
