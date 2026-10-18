<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Product extends Model
{
    use HasFactory;

    /**
     * Test/placeholder names that must never appear on public pages.
     */
    const PLACEHOLDER_NAMES = ['name', 'edit test product', 'test product'];

    protected $fillable = [
        'category_id',  'brand_id', 'seller_id', 'shop_id', 'price', 'stock', 'status', 'slug', 'currency', 'SKU',
        'weight', 'dimensions', 'product_type',  'image_url', 'vendor_id', 'series', 'type',

    ];

    /**
     * Only active products that have a real (non-empty, non-placeholder) name
     * and a short description or description in the same translation.
     * Used by public storefront queries; the admin area does not use it.
     */
    public function scopePublic($query)
    {
        $placeholders = implode(',', array_fill(0, count(self::PLACEHOLDER_NAMES), '?'));

        return $query->where('status', 1)
            ->whereHas('translations', function ($q) use ($placeholders) {
                $q->whereRaw("TRIM(COALESCE(name, '')) <> ''")
                    ->whereRaw("LOWER(TRIM(name)) NOT IN ({$placeholders})", self::PLACEHOLDER_NAMES)
                    ->where(function ($d) {
                        $d->whereRaw("TRIM(COALESCE(short_description, '')) <> ''")
                            ->orWhereRaw("TRIM(COALESCE(description, '')) <> ''");
                    });
            });
    }

    /**
     * Get the translations for the product.
     */
    public function translations()
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function translation()
    {
        return $this->hasOne(ProductTranslation::class)
            ->where('language_code', App::getLocale());
    }

    /**
     * Get the category for the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // One-to-many relationship with ProductImage
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Get the brand for the product.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getTranslation($field, $locale = 'en')
    {
        $translation = $this->translations->firstWhere('language_code', $locale);

        return $translation ? $translation->$field : null;
    }

    public function thumbnail()
    {
        return $this->hasOne(ProductImage::class)->where('type', 'thumb');
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class)->approved()->latest();
    }

    public function averageRating()
    {
        return $this->reviews()->avg('rating') ?: 0;
    }

    public function getConvertedPriceAttribute()
    {
        return convert_price($this->price);
    }

    public function getConvertedDiscountPriceAttribute()
    {
        return $this->discount_price ? convert_price($this->discount_price) : null;
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    /*public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_values', 'product_id', 'attribute_value_id');
    }*/
    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_values')
            ->with('attribute', 'translations');
    }

    public function primaryVariant()
    {
        return $this->hasOne(ProductVariant::class)->where('is_primary', true);
    }

    public function wishlistedBy()
    {
        return $this->belongsToMany(Customer::class, 'wishlists');
    }
}
