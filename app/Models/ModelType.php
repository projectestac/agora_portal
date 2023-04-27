<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModelType extends Model {
    use HasFactory;

    protected $fillable = [
        'short_code',
        'description',
        'url',
        'db',
    ];

    public function models(): HasMany {
        return $this->hasMany(ClientService::class, 'model_type_id', 'id');
    }

}
