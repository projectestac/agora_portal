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

    public function requests(): HasMany {
        return $this->hasMany(Request::class);
    }

    public function instances(): HasMany {
        return $this->hasMany(Instance::class);
    }

    public function modelTypes(): HasMany {
        return $this->hasMany(ModelType::class);
    }

    public function requestTypes(): BelongsToMany {
        return $this->belongsToMany(RequestType::class);
    }

}
