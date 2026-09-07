@extends('admin.layouts.admin')

@section('css')
<style>
.line-item-row { background: #f8f9fa; padding: 12px; margin-bottom: 10px; border-radius: 6px; border: 1px solid #dee2e6; }
.line-item-row .form-label { font-size: 0.85rem; margin-bottom: 2px; }
.remove-line { cursor: pointer; color: #dc3545; font-size: 1.2rem; }
.summary-box { background: #f0f4f8; padding: 15px; border-radius: 6px; }
.summary-box .total-amount { font-size: 1.1rem; font-weight: bold; color: #0e7a4f; }
.company-header-preview { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 15px; }
.currency-disabled { opacity: 0.65; }
.product-search-wrapper { position: relative; }
.product-search-dropdown { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #dee2e6; border-radius: 4px; max-height: 200px; overflow-y: auto; z-index: 1000; display: none; }
.product-search-dropdown.show { display: block; }
.product-search-item { padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; }
.product-search-item:hover { background: #f0f4f8; }
.product-search-item small { color: #666; }

/* Line items table — redesigned for clarity */
.items-table-wrapper { overflow-x: auto; border: 1px solid #dee2e6; border-radius: 6px; -webkit-overflow-scrolling: touch; }
.items-table { min-width: 1500px; margin-bottom: 0; border-collapse: separate; border-spacing: 0; }
.items-table th { white-space: nowrap; font-size: 0.82rem; font-weight: 600; background: #333333; color: white; padding: 12px 12px; border-bottom: 2px solid #222222; vertical-align: middle; }
.items-table td { vertical-align: top; padding: 14px 12px; border-bottom: 1px solid #e9ecef; }
.items-table tbody tr:last-child td { border-bottom: none; }
.items-table .form-control, .items-table .form-select { background: #fff; border: 1px solid #dee2e6; padding: 6px 8px; font-size: 0.85rem; }
.items-table .form-control:focus, .items-table .form-select:focus { border-color: #0e7a4f; box-shadow: 0 0 0 0.15rem rgba(14,122,79,0.15); }
/* Auto-calculated fields — read-only, subtle */
.converted-unit-price, .converted-line-total { background: #eef2f7 !important; color: #495057; font-size: 0.85rem; }
.converted-unit-price .converted-price, .converted-line-total .converted-total { color: #212529; font-weight: 500; }
.description-textarea { min-height: 110px; resize: vertical; }
.image-preview { width: 80px; height: 80px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6; display: block; }
.lang-checkbox-group label, .currency-checkbox-group label { cursor: pointer; }
.selection-card { height: 100%; padding: 1rem; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; }
.selection-card .form-label { margin-bottom: .5rem; }
.selection-options { display: flex; flex-wrap: wrap; gap: .4rem .9rem; align-items: center; }
.selection-options .form-check { margin: 0; }
.selection-helper { margin-top: .5rem; font-size: .78rem; line-height: 1.35; }
.base-currency { background: #eef8f2; border-color: #b9e2ca; }
.currency-extra-options .form-check { min-width: 112px; }
.company-header-preview { display: flex; align-items: center; min-height: 112px; }
.company-logo-placeholder { width: 92px; height: 72px; display: inline-flex; align-items: center; justify-content: center; border: 1px dashed #adb5bd; border-radius: 8px; background: #fff; color: #6c757d; font-size: .75rem; text-align: center; }
/* Subtle placeholders */
.items-table .form-control::placeholder { color: #9ca3af; opacity: 1; }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('content')
<div class="card mt-4">
    <div class="card-header card-header-bg text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 dt-heading">Create Quotation</h6>
        <a href="{{ route('admin.quotations.index') }}" class="btn btn-sm btn-light">Back to List</a>
    </div>
    <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.quotations.store') }}" method="POST" id="quotationForm" enctype="multipart/form-data">
            @csrf

            {{-- Company Header Preview (from Site Settings, not editable here) --}}
            <div class="company-header-preview mb-4">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        @if($siteSettings && $siteSettings->logo)
                            <img src="{{ asset('storage/' . $siteSettings->logo) }}" alt="Logo" style="max-height: 60px; max-width: 100%;">
                        @else
                            <div class="company-logo-placeholder"><i class="bi bi-building me-1"></i> Logo placeholder</div>
                        @endif
                    </div>
                    <div class="col-md-10">
                         <h5 class="mb-1">{{ $siteSettings->site_name ?? 'ZY Energy' }}</h5>
                        @if($siteSettings && $siteSettings->address)
                            <div class="text-muted small">{{ $siteSettings->address }}</div>
                        @endif
                        <div class="text-muted small">
                            @if($siteSettings && $siteSettings->contact_email) {{ $siteSettings->contact_email }} @endif
                            @if($siteSettings && $siteSettings->contact_phone) | {{ $siteSettings->contact_phone }} @endif
                        </div>
                        <small class="text-muted">Logo & address are pulled from <a href="{{ route('admin.site-settings.edit') }}" target="_blank">Site Settings</a>.</small>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="selection-card">
                    <label class="form-label fw-bold">Language *</label>
                    <div class="lang-checkbox-group selection-options" id="languageGroup">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input lang-checkbox" type="checkbox" name="languages[]" value="en" id="lang_en" {{ in_array('en', old('languages', ['en'])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="lang_en">English</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input lang-checkbox" type="checkbox" name="languages[]" value="zh" id="lang_zh" {{ in_array('zh', old('languages', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="lang_zh">中文</label>
                        </div>
                    </div>
                    <small class="text-muted d-block selection-helper">Tick one or both. Both = two separate PDFs.</small>
                    <div class="invalid-feedback" id="langError" hidden></div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="selection-card">
                    <label class="form-label fw-bold">Display Currency <span class="text-muted fw-normal">(RMB + 1 extra)</span></label>
                    <div class="currency-checkbox-group selection-options" id="currencyGroup">
                        @foreach($exchangeRates as $rate)
                            @php $code = strtoupper($rate->code); @endphp
                            @if($code === 'RMB')
                            <div class="form-check form-check-inline">
                                <input type="hidden" name="currencies[]" value="RMB">
                                <input class="form-check-input currency-checkbox" type="checkbox" value="RMB" id="curr_RMB" data-rate="{{ $rate->rate }}" data-symbol="¥" checked disabled>
                                <label class="form-check-label" for="curr_{{ $code }}">{{ $code }} - {{ $rate->name }} ({{ $rate->rate }})</label>
                            </div>
                            @endif
                        @endforeach
                        <div class="currency-extra-options selection-options">
                        @foreach($exchangeRates as $rate)
                            @php $code = strtoupper($rate->code); @endphp
                            @if($code !== 'RMB')
                            <div class="form-check">
                                <input class="form-check-input currency-extra" type="radio" name="currency_extra" value="{{ $code }}" id="curr_{{ $code }}" data-rate="{{ $rate->rate }}" data-symbol="{{ match($code) { 'CNY' => '¥', 'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥', 'INR' => '₹', default => $code.' ' } }}" {{ old('currency_extra') === $code ? 'checked' : '' }}>
                                <label class="form-check-label" for="curr_{{ $code }}">{{ $code }} - {{ $rate->name }}</label>
                            </div>
                            @endif
                        @endforeach
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="clearExtraCurrency">Clear extra currency</button>
                    </div>
                    <small class="text-muted d-block selection-helper">RMB is fixed as the base. Select at most one extra currency.</small>
                    <div class="invalid-feedback" id="currencyError" hidden></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Date * <small class="text-muted">(YYYY-MM-DD)</small></label>
                    <input type="text" id="quotationDate" name="date" class="form-control" value="{{ old('date', now()->format('Y-m-d')) }}" placeholder="YYYY-MM-DD" required>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Customer (optional)</label>
                    <select name="customer_id" id="customerId" class="form-select">
                        <option value="">-- Manual Entry --</option>
                        @foreach($customers as $cust)
                            <option value="{{ $cust->id }}" data-name="{{ $cust->name }}" data-company="{{ $cust->company ?? '' }}" data-address="{{ $cust->address ?? '' }}" data-phone="{{ $cust->phone ?? '' }}" data-email="{{ $cust->email ?? '' }}">{{ $cust->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Customer Name *</label>
                    <input type="text" name="customer_name" id="customerName" class="form-control" value="{{ old('customer_name') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Company</label>
                    <input type="text" name="company_name" id="companyName" class="form-control" value="{{ old('company_name') }}">
                </div>
                <div class="col-md-4 mt-2">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="customerEmail" class="form-control" value="{{ old('email') }}">
                </div>
                <div class="col-md-4 mt-2">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" id="customerPhone" class="form-control" value="{{ old('phone') }}">
                </div>
                <div class="col-md-4 mt-2">
                    <label class="form-label">Address</label>
                    <textarea name="address" id="customerAddress" class="form-control" rows="2">{{ old('address') }}</textarea>
                </div>
            </div>

            <h6 class="fw-bold border-bottom pb-2">Line Items</h6>
            <small class="text-muted">Search a catalog product to auto-fill, or type manually and upload an image. No field is mandatory.</small>
            <div class="items-table-wrapper mt-2">
                <table class="table table-bordered items-table" id="itemsTable">
                    <thead id="itemsTableHead">
                        <tr id="itemsHeaderRow" style="background-color: #333333; color: white;">
                            <th style="width: 30px; padding: 12px 8px; text-align: left; border: 1px solid #ddd; font-weight: bold; color: white; background-color: #333333;">SL</th>
                            <th style="min-width: 130px; padding: 12px 8px; text-align: left; border: 1px solid #ddd; font-weight: bold; color: white; background-color: #333333;">Category</th>
                            <th style="min-width: 150px; padding: 12px 8px; text-align: left; border: 1px solid #ddd; font-weight: bold; color: white; background-color: #333333;">Product</th>
                            <th style="min-width: 150px; padding: 12px 8px; text-align: left; border: 1px solid #ddd; font-weight: bold; color: white; background-color: #333333;">Model Name</th>
                            <th style="width: 110px; padding: 12px 8px; text-align: center; border: 1px solid #ddd; font-weight: bold; color: white; background-color: #333333;">QTY</th>
                            <th style="width: 140px; padding: 12px 8px; text-align: right; border: 1px solid #ddd; font-weight: bold; color: white; background-color: #333333;">Unit Price<br><small style="color: white;">(RMB Base)</small></th>
                            <th style="min-width: 280px; padding: 12px 8px; text-align: left; border: 1px solid #ddd; font-weight: bold; color: white; background-color: #333333;">Description</th>
                            <th style="min-width: 140px; padding: 12px 8px; text-align: center; border: 1px solid #ddd; font-weight: bold; color: white; background-color: #333333;">Reference Image</th>
                            <!-- Dynamic currency columns inserted here by JS -->
                            <th style="width: 40px; padding: 12px 8px; text-align: center; border: 1px solid #ddd; font-weight: bold; color: white; background-color: #333333;"></th>
                        </tr>
                    </thead>
                    <tbody id="lineItems">
                        {{-- Initial row --}}
                        <tr class="line-item-row" data-index="0">
                            <td class="sl-num">1</td>
                            <td>
                                <select class="form-select form-select-sm category-select" data-index="0">
                                    <option value="">-- Category --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->translation->name ?? $cat->translations->firstWhere('language_code','en')->name ?? $cat->slug }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select class="form-select form-select-sm product-select" data-index="0" disabled>
                                    <option value="">-- Select Category First --</option>
                                </select>
                                <input type="hidden" name="items[0][product_id]" class="product-id" value="">
                                <input type="hidden" name="items[0][reference_image]" class="ref-image" value="">
                            </td>
                            <td><input type="text" name="items[0][model_name]" class="form-control form-control-sm model-name" placeholder="Model"></td>
                            <td><input type="number" name="items[0][qty]" class="form-control form-control-sm qty" value="" min="0" step="1" placeholder="0"></td>
                            <td><input type="number" name="items[0][unit_price]" class="form-control form-control-sm unit-price" value="" min="0" step="0.01" placeholder="0.00"></td>
                            <td><textarea name="items[0][description]" class="form-control form-control-sm description-textarea" rows="5" placeholder="Rated power, max power, blade height..."></textarea></td>
                            <td>
                                <div class="image-preview-wrap mb-1" data-index="0" style="display:none;">
                                    <img src="" class="image-preview" alt="Preview">
                                </div>
                                <input type="file" name="items[0][image]" class="form-control form-control-sm image-input" accept="image/*" data-index="0">
                                <small class="text-muted d-block">auto-filled or upload</small>
                            </td>
                            <!-- Dynamic currency Unit Price / Line Total columns -->
                            <td class="text-center"><span class="remove-line" title="Remove"><i class="bi bi-x-circle-fill"></i></span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mb-3 mt-2">
                <button type="button" class="btn btn-sm btn-outline-success" id="addLine"><i class="bi bi-plus-lg"></i> Add Line</button>
            </div>

            <div class="row mb-4">
                <div class="col-md-8"></div>
                <div class="col-md-4">
                    <div class="summary-box" id="summaryBox">
                        {{-- Filled dynamically per currency --}}
                    </div>
                </div>
            </div>

            <h6 class="fw-bold border-bottom pb-2">Notes & Remarks</h6>
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="5">{!! old('notes', $defaultNotes) !!}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="5">{!! old('remarks', $defaultRemarks) !!}</textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg"></i> Create Quotation</button>
        </form>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
let lineIndex = 1;
const currencySymbols = @json($currencySymbols);
const currencyRates = @json($currencyRates);

function getSelectedCurrencies() {
    const vals = [];
    document.querySelectorAll('.currency-checkbox:checked').forEach(el => vals.push(el.value));
    document.querySelectorAll('.currency-extra:checked').forEach(el => vals.push(el.value));
    return vals;
}

function getSelectedLanguages() {
    const vals = [];
    document.querySelectorAll('.lang-checkbox:checked').forEach(el => vals.push(el.value));
    return vals;
}

function updateCurrencyCheckboxes() {
    const extraSelected = document.querySelector('.currency-extra:checked');
    document.querySelectorAll('.currency-extra').forEach(el => {
        el.closest('.form-check').classList.remove('currency-disabled');
    });
    rebuildCurrencyColumns();
    recalcTotals();
}

function rebuildCurrencyColumns() {
    const currencies = getSelectedCurrencies();
    const headerRow = document.getElementById('itemsHeaderRow');
    // Remove old dynamic currency headers
    headerRow.querySelectorAll('.dyn-currency-col').forEach(el => el.remove());
    const removeTh = headerRow.querySelector('th:last-child');
    currencies.forEach(code => {
        const sym = currencySymbols[code] || code + ' ';
        const th1 = document.createElement('th');
        th1.className = 'dyn-currency-col';
        th1.style.minWidth = '135px';
        th1.style.padding = '12px 8px';
        th1.style.textAlign = 'right';
        th1.style.border = '1px solid #ddd';
        th1.style.fontWeight = 'bold';
        th1.style.color = 'white';
        th1.style.backgroundColor = '#333333';
        th1.innerHTML = `Unit Price<br><small style="color:white;">(${code} ${sym})</small>`;
        const th2 = document.createElement('th');
        th2.className = 'dyn-currency-col';
        th2.style.minWidth = '135px';
        th2.style.padding = '12px 8px';
        th2.style.textAlign = 'right';
        th2.style.border = '1px solid #ddd';
        th2.style.fontWeight = 'bold';
        th2.style.color = 'white';
        th2.style.backgroundColor = '#333333';
        th2.innerHTML = `Line Total<br><small style="color:white;">(${code} ${sym})</small>`;
        headerRow.insertBefore(th2, removeTh);
        headerRow.insertBefore(th1, th2);
    });
    // Fix: the above inserts in loop but insertion point shifts. Simpler: rebuild all at once
    // Clear and re-add in correct order
    headerRow.querySelectorAll('.dyn-currency-col').forEach(el => el.remove());
    // Re-insert in order
    const frag1 = document.createDocumentFragment();
    currencies.forEach(code => {
        const sym = currencySymbols[code] || code + ' ';
        const th1 = document.createElement('th');
        th1.className = 'dyn-currency-col';
        th1.style.minWidth = '135px';
        th1.style.padding = '12px 8px';
        th1.style.textAlign = 'right';
        th1.style.border = '1px solid #ddd';
        th1.style.fontWeight = 'bold';
        th1.style.color = 'white';
        th1.style.backgroundColor = '#333333';
        th1.innerHTML = `Unit Price<br><small style="color:white;">(${code} ${sym})</small>`;
        const th2 = document.createElement('th');
        th2.className = 'dyn-currency-col';
        th2.style.minWidth = '135px';
        th2.style.padding = '12px 8px';
        th2.style.textAlign = 'right';
        th2.style.border = '1px solid #ddd';
        th2.style.fontWeight = 'bold';
        th2.style.color = 'white';
        th2.style.backgroundColor = '#333333';
        th2.innerHTML = `Line Total<br><small style="color:white;">(${code} ${sym})</small>`;
        frag1.appendChild(th1);
        frag1.appendChild(th2);
    });
    headerRow.insertBefore(frag1, removeTh);

    // Rebuild body rows
    document.querySelectorAll('#lineItems tr').forEach(row => {
        row.querySelectorAll('.dyn-currency-col').forEach(el => el.remove());
        const removeTd = row.querySelector('td:last-child');
        currencies.forEach(code => {
            const sym = currencySymbols[code] || code + ' ';
            const td1 = document.createElement('td');
            td1.className = 'dyn-currency-col converted-unit-price';
            td1.dataset.currency = code;
            td1.innerHTML = '<span class="converted-price">' + sym + '0.00</span>';
            const td2 = document.createElement('td');
            td2.className = 'dyn-currency-col converted-line-total fw-bold';
            td2.dataset.currency = code;
            td2.innerHTML = '<span class="converted-total">' + sym + '0.00</span>';
            row.insertBefore(td2, removeTd);
            row.insertBefore(td1, td2);
        });
    });
}

function recalcTotals() {
    const currencies = getSelectedCurrencies();
    let baseSubtotal = 0;
    document.querySelectorAll('#lineItems tr').forEach(row => {
        const qty = parseFloat(row.querySelector('.qty')?.value) || 0;
        const price = parseFloat(row.querySelector('.unit-price')?.value) || 0;
        const lineBase = qty * price;
        baseSubtotal += lineBase;

        currencies.forEach(code => {
            const rate = currencyRates[code] !== undefined ? parseFloat(currencyRates[code]) : 1;
            const sym = currencySymbols[code] || code + ' ';
            const convertedUnit = price * rate;
            const convertedTotal = lineBase * rate;
            const unitCell = row.querySelector(`.converted-unit-price[data-currency="${code}"] .converted-price`);
            const totalCell = row.querySelector(`.converted-line-total[data-currency="${code}"] .converted-total`);
            if (unitCell) unitCell.textContent = sym + convertedUnit.toFixed(2);
            if (totalCell) totalCell.textContent = sym + convertedTotal.toFixed(2);
        });

        // Update SL numbers
        document.querySelectorAll('#lineItems tr').forEach((r, i) => {
            const sl = r.querySelector('.sl-num');
            if (sl) sl.textContent = i + 1;
        });
    });

    // Update summary box
    const summaryBox = document.getElementById('summaryBox');
    if (currencies.length === 0) {
        summaryBox.innerHTML = '<span class="text-muted">Select a currency to see totals.</span>';
        return;
    }
    let html = '';
    currencies.forEach(code => {
        const rate = currencyRates[code] !== undefined ? parseFloat(currencyRates[code]) : 1;
        const sym = currencySymbols[code] || code + ' ';
        const convertedSubtotal = baseSubtotal * rate;
        html += `<div class="d-flex justify-content-between mb-2"><span>Subtotal (${code}):</span><span class="fw-bold">${sym}${convertedSubtotal.toFixed(2)}</span></div>`;
    });
    html += '<hr>';
    currencies.forEach(code => {
        const rate = currencyRates[code] !== undefined ? parseFloat(currencyRates[code]) : 1;
        const sym = currencySymbols[code] || code + ' ';
        const convertedTotal = baseSubtotal * rate;
        html += `<div class="d-flex justify-content-between mb-1"><span class="fw-bold">TOTAL (${code}):</span><span class="total-amount">${sym}${convertedTotal.toFixed(2)}</span></div>`;
    });
    // Also show base
    html += `<div class="text-muted small mt-2">Base (RMB): ¥${baseSubtotal.toFixed(2)} — Base price entered in RMB; displayed per selected currency.</div>`;
    summaryBox.innerHTML = html;
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

function loadProductsForCategory(categoryId, productSelect, lang, preselectedId = null) {
    productSelect.innerHTML = '<option value="">-- Select Product --</option>';
    if (!categoryId) {
        productSelect.disabled = true;
        return;
    }
    productSelect.disabled = false;
    productSelect.innerHTML = '<option value="">Loading...</option>';
    const params = new URLSearchParams({ category_id: categoryId, language: lang || 'en' });
    fetch(`{{ route('admin.quotations.products') }}?${params.toString()}`)
        .then(r => r.json())
        .then(products => {
            productSelect.innerHTML = '<option value="">-- Select Product --</option>';
            if (products.length === 0) {
                productSelect.innerHTML = '<option value="">No products in this category</option>';
                productSelect.disabled = true;
                return;
            }
            products.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.model_name;
                opt.dataset.model = p.model_name;
                opt.dataset.desc = p.description || '';
                opt.dataset.image = p.reference_image || '';
                opt.dataset.price = p.price || '';
                if (preselectedId && String(p.id) === String(preselectedId)) opt.selected = true;
                productSelect.appendChild(opt);
            });
            productSelect.disabled = false;
            // If preselected, trigger autofill
            if (preselectedId) {
                autofillRowFromProduct(productSelect.closest('tr'), productSelect.options[productSelect.selectedIndex]);
            }
        });
}

function autofillRowFromProduct(row, selectedOption) {
    if (!row || !selectedOption || !selectedOption.value) return;
    const idx = row.dataset.index;
    const productIdInput = row.querySelector('.product-id');
    const modelInput = row.querySelector('.model-name');
    const descInput = row.querySelector('.description-textarea');
    const refImageInput = row.querySelector('.ref-image');
    const previewWrap = row.querySelector('.image-preview-wrap');
    // Save current manual edits confirmation: only autofill once per selection, not on recalc
    // Ask if row already has manual data? For now, just autofill and allow manual overwrite afterwards
    if (productIdInput) productIdInput.value = selectedOption.value;
    if (modelInput) modelInput.value = selectedOption.dataset.model || '';
    if (descInput) descInput.value = selectedOption.dataset.desc || '';
    if (refImageInput) refImageInput.value = selectedOption.dataset.image || '';
    if (previewWrap && selectedOption.dataset.image) {
        previewWrap.style.display = 'block';
        const img = previewWrap.querySelector('img');
        const src = selectedOption.dataset.image;
        img.src = src.startsWith('http') ? src : '/storage/' + src;
    }
    const priceInput = row.querySelector('.unit-price');
    if (priceInput && selectedOption.dataset.price) priceInput.value = selectedOption.dataset.price;
    recalcTotals();
}

document.addEventListener('DOMContentLoaded', function() {
    // Chinese date format YYYY-MM-DD via flatpickr
    if (typeof flatpickr !== 'undefined') {
        flatpickr("#quotationDate", { dateFormat: "Y-m-d", allowInput: true });
    }
    // Init category -> product linkage for existing rows
    document.querySelectorAll('.category-select').forEach(sel => {
        sel.addEventListener('change', function() {
            const idx = this.dataset.index;
            const row = document.querySelector(`tr[data-index="${idx}"]`);
            const productSelect = row.querySelector('.product-select');
            const langs = getSelectedLanguages();
            const lang = langs[0] || 'en';
            // Clear previous product selection but keep manual fields until new product chosen
            const productIdInput = row.querySelector('.product-id');
            if (productIdInput) productIdInput.value = '';
            loadProductsForCategory(this.value, productSelect, lang);
        });
    });
    document.querySelectorAll('.product-select').forEach(sel => {
        sel.addEventListener('change', function() {
            const row = this.closest('tr');
            const opt = this.options[this.selectedIndex];
            // If user selects placeholder, clear product link
            if (!this.value) {
                row.querySelector('.product-id').value = '';
                return;
            }
            // Confirm if row has manual edits? Simple: if model/desc already filled with different value, still overwrite once
            autofillRowFromProduct(row, opt);
        });
    });

    // Image preview for file inputs
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('image-input')) {
            const idx = e.target.dataset.index;
            const wrap = document.querySelector(`.image-preview-wrap[data-index="${idx}"]`);
            if (e.target.files && e.target.files[0] && wrap) {
                const reader = new FileReader();
                reader.onload = ev => {
                    wrap.style.display = 'block';
                    wrap.querySelector('img').src = ev.target.result;
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        }
    });

    // Language / currency change handlers
    document.querySelectorAll('.lang-checkbox').forEach(cb => cb.addEventListener('change', () => {
        // Refresh product search language context
        recalcTotals();
    }));
document.querySelectorAll('.currency-extra').forEach(cb => cb.addEventListener('change', updateCurrencyCheckboxes));
document.getElementById('clearExtraCurrency')?.addEventListener('click', () => {
    document.querySelectorAll('.currency-extra').forEach(el => el.checked = false);
    updateCurrencyCheckboxes();
});

    // Add line
    document.getElementById('addLine').addEventListener('click', function() {
        const idx = lineIndex++;
        const row = document.createElement('tr');
        row.className = 'line-item-row';
        row.dataset.index = idx;
        row.innerHTML = `
            <td class="sl-num">${idx + 1}</td>
            <td>
                <select class="form-select form-select-sm category-select" data-index="${idx}">
                    <option value="">-- Category --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->translation->name ?? $cat->translations->firstWhere('language_code','en')->name ?? $cat->slug }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select class="form-select form-select-sm product-select" data-index="${idx}" disabled>
                    <option value="">-- Select Category First --</option>
                </select>
                <input type="hidden" name="items[${idx}][product_id]" class="product-id" value="">
                <input type="hidden" name="items[${idx}][reference_image]" class="ref-image" value="">
            </td>
            <td><input type="text" name="items[${idx}][model_name]" class="form-control form-control-sm model-name" placeholder="Model"></td>
            <td><input type="number" name="items[${idx}][qty]" class="form-control form-control-sm qty" value="" min="0" step="1" placeholder="0"></td>
            <td><input type="number" name="items[${idx}][unit_price]" class="form-control form-control-sm unit-price" value="" min="0" step="0.01" placeholder="0.00"></td>
            <td><textarea name="items[${idx}][description]" class="form-control form-control-sm description-textarea" rows="5" placeholder="Rated power, max power, blade height..."></textarea></td>
            <td>
                <div class="image-preview-wrap mb-1" data-index="${idx}" style="display:none;">
                    <img src="" class="image-preview" alt="Preview">
                </div>
                <input type="file" name="items[${idx}][image]" class="form-control form-control-sm image-input" accept="image/*" data-index="${idx}">
                <small class="text-muted d-block" style="font-size: 0.7rem;">auto or upload</small>
            </td>
            <td class="text-center"><span class="remove-line" title="Remove"><i class="bi bi-x-circle-fill"></i></span></td>
        `;
        document.getElementById('lineItems').appendChild(row);
        // Wire up new row's category and product selects
        const newCatSel = row.querySelector('.category-select');
        const newProdSel = row.querySelector('.product-select');
        newCatSel.addEventListener('change', function() {
            const langs = getSelectedLanguages();
            const lang = langs[0] || 'en';
            const productSelect = row.querySelector('.product-select');
            const pidInput = row.querySelector('.product-id');
            if (pidInput) pidInput.value = '';
            loadProductsForCategory(this.value, productSelect, lang);
        });
        newProdSel.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (!this.value) {
                row.querySelector('.product-id').value = '';
                return;
            }
            autofillRowFromProduct(row, opt);
        });
        // Rebuild currency columns for new row
        rebuildCurrencyColumns();
        recalcTotals();
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-line')) {
            const row = e.target.closest('tr');
            if (document.querySelectorAll('#lineItems tr').length > 1) {
                row.remove();
                recalcTotals();
            }
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('qty') || e.target.classList.contains('unit-price')) {
            recalcTotals();
        }
    });

    // Customer select autofill
    document.getElementById('customerId')?.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt && opt.value) {
            document.getElementById('customerName').value = opt.dataset.name || '';
            document.getElementById('companyName').value = opt.dataset.company || '';
            document.getElementById('customerEmail').value = opt.dataset.email || '';
            document.getElementById('customerPhone').value = opt.dataset.phone || '';
            document.getElementById('customerAddress').value = opt.dataset.address || '';
        }
    });

    // Form validation before submit
    document.getElementById('quotationForm').addEventListener('submit', function(e) {
        const langs = getSelectedLanguages();
        const currs = getSelectedCurrencies();
        let valid = true;
        document.getElementById('langError').hidden = true;
        document.getElementById('currencyError').hidden = true;
        if (langs.length === 0) {
            document.getElementById('langError').textContent = 'Select at least one language.';
            document.getElementById('langError').hidden = false;
            valid = false;
        }
        if (currs.length === 0) {
            document.getElementById('currencyError').textContent = 'Select at least one currency.';
            document.getElementById('currencyError').hidden = false;
            valid = false;
        }
        if (!valid) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    // Initial build
    updateCurrencyCheckboxes();
    recalcTotals();
});
</script>
@endsection
