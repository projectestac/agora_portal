<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Instance extends Model {

    use HasFactory;

    protected $fillable = [
        'client_id',
        'service_id',
        'status',
        'db_id',
        'db_host',
        'quota',
        'used_quota',
        'visible',
        'model_type_id',
        'contact_name',
        'contact_profile',
        'observations',
        'annotations',
        'requested_at',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_DENIED = 'denied';
    public const STATUS_WITHDRAWN = 'withdrawn';
    public const STATUS_BLOCKED = 'blocked';

    public function service(): BelongsTo {
        return $this->belongsTo(Service::class);
    }

    public function client(): BelongsTo {
        return $this->belongsTo(Client::class);
    }

    public function modelType(): BelongsTo {
        return $this->belongsTo(ModelType::class);
    }

}
