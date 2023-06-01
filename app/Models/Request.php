<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Request extends Model {

    use HasFactory;

    protected $fillable = [
        'request_type_id',
        'service_id',
        'client_id',
        'user_id',
        'status',
        'user_comment',
        'admin_comment',
        'private_note',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_UNDER_STUDY = 'under_study';
    public const STATUS_SOLVED = 'solved';
    public const STATUS_DENIED = 'denied';

    public function requestType(): BelongsTo {
        return $this->belongsTo(RequestType::class);
    }

    public function service(): BelongsTo {
        return $this->belongsTo(Service::class);
    }

    public function client(): BelongsTo {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

}
