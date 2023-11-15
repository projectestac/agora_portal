<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model {
    use HasFactory;

    public const UNDEFINED = 11; // 11 is the id of the undefined location in the database.

    protected $fillable = [
        'name',
    ];

    public function clients(): HasMany {
        return $this->hasMany(Client::class);
    }

}
