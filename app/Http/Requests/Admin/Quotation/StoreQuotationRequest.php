<?php

namespace App\Http\Requests\Admin\Quotation;

use App\Models\ExchangeRate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Quotation::class);
    }

    public function rules(): array
    {
        $currencyCodes = ExchangeRate::active()->pluck('code')
            ->map(fn ($code) => strtoupper($code))
            ->push('RMB')
            ->unique()
            ->values()
            ->all();

        return [
            'customer_id'      => ['nullable', 'exists:customers,id'],
            'customer_name'    => ['required_if:customer_id,null', 'string', 'max:255'],
            'company'          => ['nullable', 'string', 'max:255'],
            'address'          => ['nullable', 'string', 'max:500'],
            'phone'            => ['nullable', 'string', 'max:50'],
            'email'            => ['nullable', 'email', 'max:255'],
            'date'             => ['required', 'date'],
            'languages'         => ['required', 'array', 'min:1'],
            'languages.*'       => ['in:en,zh'],
            'currencies'        => ['required', 'array', 'min:1', 'max:2'],
            'currencies.*'      => [Rule::in($currencyCodes)],
            'currency_extra'    => ['nullable', Rule::in(array_values(array_diff($currencyCodes, ['RMB'])))],
            'notes'            => ['nullable', 'string'],
            'remarks'          => ['nullable', 'string'],
            'line_items'       => ['array'],
            'line_items.*.category_id'     => ['nullable', 'exists:categories,id'],
            'line_items.*.product_id'      => ['nullable', 'exists:products,id'],
            'line_items.*.model_name'      => ['nullable', 'string', 'max:255'],
            'line_items.*.description'     => ['nullable', 'string'],
            'line_items.*.qty'             => ['required', 'numeric', 'min:0.01'],
            'line_items.*.unit_price'      => ['required', 'numeric', 'min:0'],
            'line_items.*.reference_image' => ['nullable', 'image', 'max:5120'],
            'line_items.*.image'           => ['nullable', 'image', 'max:5120'],
            'items.*.image'                => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required_if' => 'Customer name is required when no customer is selected.',
            'languages.required'        => 'At least one language must be selected.',
            'currencies.required'       => 'RMB is required. Select no more than one additional display currency.',
            'currencies.max'            => 'RMB is required. Select no more than one additional display currency.',
            'line_items.*.qty.required' => 'Quantity is required for each line item.',
            'line_items.*.unit_price.required' => 'Unit price is required for each line item.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'company' => $this->input('company', $this->input('company_name')),
            'line_items' => (array) $this->input('line_items', $this->input('items', [])),
            'languages' => array_values(array_unique((array) $this->input('languages', []))),
            'currencies' => $this->normalizeCurrencies(),
        ]);
    }

    private function normalizeCurrencies(): array
    {
        $currencies = array_values(array_unique(array_map('strtoupper', (array) $this->input('currencies', []))));
        $extra = strtoupper((string) $this->input('currency_extra', ''));

        if ($extra && $extra !== 'RMB') {
            $currencies[] = $extra;
        }

        $currencies = array_values(array_unique($currencies));
        if (! in_array('RMB', $currencies, true)) {
            array_unshift($currencies, 'RMB');
        }

        return array_values(array_unique(array_merge(['RMB'], array_slice(array_diff($currencies, ['RMB']), 0, 1))));
    }
}
