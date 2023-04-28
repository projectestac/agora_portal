<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RequestType extends Model {
    use HasFactory;

    protected $table = 'request_types';

    protected $fillable = [
        'name',
        'description',
        'prompt',
    ];

    public function services(): BelongsToMany {
        return $this->belongsToMany(Service::class);
    }

}
