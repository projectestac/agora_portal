<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HttpAccessLog extends Model
{
    use HasFactory;

    protected $table = 'http_access_logs';

    protected $fillable = [
        'accessed_at',
        'ip',
        'user_agent',
        'url',
        'method',
        'payload',
    ];

    public $timestamps = false;
}
