<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Image, video or video poster of an About section. Uploads use `path`
 * (public disk); YouTube/Vimeo videos use `url` (embed URL).
 */
class AboutMedia extends Model
{
    protected $table = 'about_media';

    protected $fillable = ['about_section_id', 'kind', 'path', 'url', 'sort_order'];

    protected $casts = ['sort_order' => 'integer'];

    public function translations()
    {
        return $this->hasMany(AboutMediaTranslation::class);
    }

    public function section()
    {
        return $this->belongsTo(AboutSection::class, 'about_section_id');
    }

    public function publicUrl(): ?string
    {
        return $this->path ? asset('storage/'.$this->path) : $this->url;
    }
}
