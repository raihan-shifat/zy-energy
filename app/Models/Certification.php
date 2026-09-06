<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Certification extends Model
{
    use HasFactory;

    protected $fillable = ['image_url', 'document_url', 'status'];

    public function translations()
    {
        return $this->hasMany(CertificationTranslation::class);
    }

    public function translation()
    {
        return $this->hasOne(CertificationTranslation::class)
            ->where('language_code', App::getLocale());
    }
}
