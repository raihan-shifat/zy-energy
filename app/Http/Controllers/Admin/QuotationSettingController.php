<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuotationSetting;
use App\Traits\PreventsManagerDelete;
use Illuminate\Http\Request;

class QuotationSettingController extends Controller
{
    use PreventsManagerDelete;

    public function edit()
    {
        $defaultNotes = QuotationSetting::getValue('default_notes', '');
        $defaultRemarks = QuotationSetting::getValue('default_remarks', '');

        return view('admin.quotation-settings.edit', compact('defaultNotes', 'defaultRemarks'));
    }

    public function update(Request $request)
    {
        if ($guard = $this->rejectManagerDelete()) {
            return $guard;
        }

        $request->validate([
            'default_notes' => 'nullable|string',
            'default_remarks' => 'nullable|string',
        ]);

        QuotationSetting::set('default_notes', $request->input('default_notes'));
        QuotationSetting::set('default_remarks', $request->input('default_remarks'));

        return redirect()->route('admin.quotation-settings.edit')->with('success', 'Quotation settings updated.');
    }
}
