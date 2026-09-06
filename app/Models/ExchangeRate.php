<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'rate',
        'is_base',
        'is_active',
        'last_updated_at',
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'is_base' => 'boolean',
        'is_active' => 'boolean',
        'last_updated_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function convert(float $amount, string $fromCurrency, string $toCurrency): float
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        $fromRate = static::where('code', $fromCurrency)->value('rate') ?? 1;
        $toRate = static::where('code', $toCurrency)->value('rate') ?? 1;

        $inBase = $amount * $fromRate;

        return $inBase / $toRate;
    }
}
