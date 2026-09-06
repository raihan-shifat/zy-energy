<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'date',
        'customer_name',
        'company_name',
        'address',
        'phone',
        'email',
        'customer_id',
        'language',
        'languages',
        'display_currency',
        'currencies',
        'exchange_rate',
        'base_currency',
        'subtotal',
        'total',
        'notes',
        'remarks',
        'status',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'languages' => 'array',
        'currencies' => 'array',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');

        // Count same-day quotations instead of parsing the last suffix,
        // so the sequence never wraps after 999
        $count = self::where('invoice_number', 'like', "QTY-{$date}-%")->count() + 1;
        $seq = $count;

        // Safety net against gaps/deleted rows colliding with a unique index
        while (self::where('invoice_number', sprintf('QTY-%s-%03d', $date, $seq))->exists()) {
            $seq++;
        }

        return sprintf('QTY-%s-%03d', $date, $seq);
    }

    public function recalculate(): void
    {
        $this->subtotal = $this->items->sum('line_total');
        $this->total = $this->subtotal;
        $this->save();
    }

    public function getCurrencySymbolAttribute(): string
    {
        return match ($this->display_currency) {
            'RMB' => '¥',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => $this->display_currency . ' ',
        };
    }

    public function selectedLanguages(): array
    {
        $languages = $this->languages;
        if (! is_array($languages)) {
            $languages = json_decode((string) ($languages ?: $this->language ?: 'en'), true);
        }
        if (! is_array($languages)) {
            $languages = [$languages ?: ($this->language ?: 'en')];
        }

        $languages = array_values(array_unique(array_filter(array_map('strtolower', $languages))));
        $languages = array_values(array_intersect($languages, ['en', 'zh']));

        return $languages ?: ['en'];
    }

    public function selectedCurrencies(): array
    {
        $currencies = $this->currencies;
        if (! is_array($currencies)) {
            $currencies = json_decode((string) ($currencies ?: $this->display_currency ?: 'RMB'), true);
        }
        if (! is_array($currencies)) {
            $currencies = [$currencies ?: ($this->display_currency ?: 'RMB')];
        }

        $currencies = array_values(array_unique(array_filter(array_map('strtoupper', $currencies))));
        $extra = array_values(array_diff($currencies, ['RMB', 'CNY']));

        return array_values(array_unique(array_merge(['RMB'], array_slice($extra, 0, 1))));
    }
}
