<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutSectionTranslation extends Model
{
    protected $fillable = ['about_section_id', 'language_code', 'title', 'subtitle', 'body', 'button_text', 'extra'];

    protected $casts = ['extra' => 'array'];

    public function section()
    {
        return $this->belongsTo(AboutSection::class, 'about_section_id');
    }
}
