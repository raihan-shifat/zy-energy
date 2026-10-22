<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutMediaTranslation extends Model
{
    protected $fillable = ['about_media_id', 'language_code', 'alt', 'caption'];
}
