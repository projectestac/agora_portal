<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'description',
        'slug',
        'quota',
    ];

    public function clients($orderBy = 'id', $direction = 'asc'): BelongsToMany {
        return $this->belongsToMany(Client::class)
            ->withPivot([
                'id',
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
                'created_at',
                'updated_at',
            ])
            ->orderBy($orderBy, $direction);
    }

    public function requests(): HasMany {
        return $this->hasMany(Request::class);
    }

    public function commands(): HasMany {
        return $this->hasMany(Command::class);
    }

    public function requestTypes(): BelongsToMany {
        return $this->belongsToMany(RequestType::class);
    }

}
