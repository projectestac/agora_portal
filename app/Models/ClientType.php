<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientType extends Model {
    use HasFactory;

    public const UNDEFINED = 15; // 15 is the id of the undefined client type in the database.

    protected $fillable = [
        'name',
    ];

    public function clients(): HasMany {
        return $this->hasMany(Client::class);
    }
}
