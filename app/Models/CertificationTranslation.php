<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificationTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['certification_id', 'language_code', 'name'];

    public function certification()
    {
        return $this->belongsTo(Certification::class);
    }
}
