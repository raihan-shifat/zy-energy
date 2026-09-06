<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialMediaLink extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'platform', 'link', 'wechat_qr_image'];

    // Relationship with translations
    public function translations()
    {
        return $this->hasMany(SocialMediaLinkTranslation::class);
    }

    protected static function booted(): void
    {
        static::saved(function () {
            if (function_exists('forgetFooterCache')) {
                forgetFooterCache();
            }
        });

        static::deleted(function () {
            if (function_exists('forgetFooterCache')) {
                forgetFooterCache();
            }
        });
    }
}
