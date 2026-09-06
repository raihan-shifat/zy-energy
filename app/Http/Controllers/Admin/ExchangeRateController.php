<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use App\Traits\PreventsManagerDelete;
use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    use PreventsManagerDelete;

    public function index()
    {
        $rates = ExchangeRate::orderBy('is_base', 'desc')->orderBy('code')->get();

        return view('admin.exchange-rates.index', compact('rates'));
    }

    public function store(Request $request)
    {
        if ($guard = $this->rejectManagerDelete()) {
            return $guard;
        }

        // Uppercase BEFORE validation so the unique rule matches how codes are stored
        $request->merge(['code' => strtoupper($request->input('code'))]);

        $request->validate([
            'code' => 'required|string|max:10|unique:exchange_rates,code',
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0',
        ]);

        ExchangeRate::create([
            'code' => strtoupper($request->code),
            'name' => $request->name,
            'rate' => $request->rate,
            'is_base' => false,
            'is_active' => true,
            'last_updated_at' => now(),
        ]);

        return redirect()->route('admin.exchange-rates.index')->with('success', 'Exchange rate added.');
    }

    public function update(Request $request, ExchangeRate $exchangeRate)
    {
        if ($guard = $this->rejectManagerDelete()) {
            return $guard;
        }

        $request->validate([
            'rate' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($exchangeRate->is_base && ! $request->boolean('is_active', true)) {
            return back()->with('error', 'Cannot deactivate the base currency.');
        }

        $exchangeRate->update([
            'rate' => $request->rate,
            'is_active' => $request->boolean('is_active', true),
            'last_updated_at' => now(),
        ]);

        return redirect()->route('admin.exchange-rates.index')->with('success', 'Exchange rate updated.');
    }

    public function destroy(ExchangeRate $exchangeRate)
    {
        if ($guard = $this->rejectManagerDelete()) {
            return $guard;
        }

        if ($exchangeRate->is_base) {
            return back()->with('error', 'Cannot delete the base currency.');
        }

        $exchangeRate->delete();

        return redirect()->route('admin.exchange-rates.index')->with('success', 'Exchange rate deleted.');
    }
}
