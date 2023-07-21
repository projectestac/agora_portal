<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Client extends Model {
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'dns',
        'old_dns',
        'url_type',
        'host',
        'old_host',
        'address',
        'city',
        'postal_code',
        'description',
        'status',
        'location_id',
        'type_id',
        'visible',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    public function location(): BelongsTo {
        return $this->belongsTo(Location::class);
    }

    public function type(): BelongsTo {
        return $this->belongsTo(ClientType::class);
    }

    public function managers(): HasMany {
        return $this->hasMany(Manager::class);
    }

    public function requests(): HasMany {
        return $this->hasMany(Request::class);
    }

    public function users(): HasManyThrough {
        return $this->hasManyThrough(User::class, Manager::class);
    }

    public function logs(): HasMany {
        return $this->hasMany(Log::class);
    }

    public function instances(): HasMany {
        return $this->hasMany(Instance::class);
    }

}
