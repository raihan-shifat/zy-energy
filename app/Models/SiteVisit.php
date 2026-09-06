<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    protected $fillable = [
        'session_id',
        'visit_date',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];
}
