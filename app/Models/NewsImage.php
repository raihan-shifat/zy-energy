<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsImage extends Model
{
    protected $fillable = ['news_id', 'path', 'alt', 'sort_order', 'is_primary'];

    protected $casts = [
        'sort_order' => 'integer',
        'is_primary' => 'boolean',
    ];

    public function news()
    {
        return $this->belongsTo(News::class);
    }
}
