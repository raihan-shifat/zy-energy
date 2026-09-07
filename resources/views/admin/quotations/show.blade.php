@extends('admin.layouts.admin')

@section('css')
<style>
.quote-header { border-bottom: 3px solid #0e7a4f; padding-bottom: 15px; margin-bottom: 20px; }
.quote-title { font-size: 2rem; font-weight: bold; color: #0e7a4f; }
.info-block { background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 15px; }
.table-quote th { background: #333333; color: white; }
.summary-box { background: #f0f4f8; padding: 20px; border-radius: 6px; }
.total-amount { font-size: 1.2rem; font-weight: bold; color: #0e7a4f; }

/* Responsive quotation table */
.quotation-table-container {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    min-width: 0; /* Fix for flex container */
}
.quotation-table {
    width: 100%;
    table-layout: fixed; /* Fixed layout prevents column width issues */
    border-collapse: collapse;
}
.quotation-table thead,
.quotation-table tbody {
    display: table-header-group;
    display: table-row-group;
}
.quotation-table tbody {
    display: table-row-group;
    overflow: visible;
}
.quotation-table tr {
    display: table-row;
}
.quotation-table th,
.quotation-table td {
    vertical-align: top;
    padding: 8px 12px;
}
.quotation-table .model-name,
.quotation-table .description {
    word-break: break-word;
    white-space: normal;
}
.quotation-table .ref-image {
    max-width: 80px;
    max-height: 80px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}
.quotation-table .text-center {
    white-space: nowrap;
}
.quotation-table .text-end {
    white-space: nowrap;
}

/* Responsive column sizing */
@media (min-width: 1200px) {
    .quotation-table .col-sl { width: 40px; }
    .quotation-table .col-model { width: 120px; }
    .quotation-table .col-desc { width: 200px; }
    .quotation-table .col-img { width: 100px; }
    .quotation-table .col-qty { width: 60px; }
    .quotation-table .col-price { width: 100px; }
    .quotation-table .col-total { width: 100px; }
}
@media (max-width: 768px) {
    .quotation-table th,
    .quotation-table td {
        padding: 6px 8px;
        font-size: 0.85rem;
    }
}
</style>
@endsection

@section('content')
<div class="card mt-4">
    <div class="card-header card-header-bg text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 dt-heading">Quotation — {{ $quotation->invoice_number }}</h6>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.quotations.print', $quotation) }}" target="_blank" class="btn btn-success btn-sm"><i class="bi bi-printer-fill"></i> Print / Save as PDF</a>
            @foreach($quotation->selectedLanguages() as $lang)
                <a href="{{ route('admin.quotations.print', $quotation) }}?lang={{ $lang }}&auto=1" target="_blank" class="btn btn-sm btn-outline-light" title="{{ strtoupper($lang) }} quotation"><i class="bi bi-printer"></i> {{ strtoupper($lang) }}</a>
            @endforeach
            <a href="{{ route('admin.quotations.edit', $quotation) }}" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i> Edit</a>
            <a href="{{ route('admin.quotations.index') }}" class="btn btn-sm btn-light">Back</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row quote-header">
            <div class="col-md-6">
                @if($siteSettings && $siteSettings->logo)
                    <img src="{{ asset('storage/' . $siteSettings->logo) }}" alt="Logo" style="max-height:60px; margin-bottom:10px;">
                @endif
                <h4 class="mb-1">{{ $siteSettings->site_name ?? 'ZY Energy' }}</h4>
                <small class="text-muted">{{ $siteSettings->address ?? '' }}</small><br>
                <small class="text-muted">{{ $siteSettings->contact_email ?? '' }} {{ $siteSettings->contact_phone ? '| ' . $siteSettings->contact_phone : '' }}</small>
            </div>
            <div class="col-md-6 text-end">
                <div class="quote-title">QUOTATION</div>
                <div><strong>Invoice #:</strong> {{ $quotation->invoice_number }}</div>
                <div><strong>Date:</strong> {{ $quotation->date->format('Y-m-d') }}</div>
                <div><span class="badge bg-{{ $quotation->status === 'sent' ? 'success' : 'secondary' }}">{{ ucfirst($quotation->status) }}</span></div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="info-block">
                    <h6 class="fw-bold">Bill To:</h6>
                    <strong>{{ $quotation->customer_name }}</strong><br>
                    @if($quotation->company_name) {{ $quotation->company_name }}<br> @endif
                    @if($quotation->address) {{ $quotation->address }}<br> @endif
                    @if($quotation->phone) Phone: {{ $quotation->phone }}<br> @endif
                    @if($quotation->email) Email: {{ $quotation->email }} @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-block">
                    <div><strong>Languages:</strong> {{ implode(', ', $quotation->selectedLanguages()) }}</div>
                    <div><strong>Currencies:</strong> {{ implode(', ', $currencyData['display_currencies'] ?? []) }}</div>
                    <div><strong>Created by:</strong> {{ $quotation->creator->name ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="quotation-table-container mt-3">
        <table class="table table-bordered table-quote quotation-table">
            <thead>
                <tr style="background-color: #333333; color: white;">
                    <th style="padding: 12px 8px; text-align: left; border: 1px solid #ddd; font-weight: bold; color: white; background-color: #333333;">SL</th>
                    <th style="padding: 12px 8px; text-align: left; border: 1px solid #ddd; font-weight: bold; color: white; background-color: #333333;">Model Name</th>
                    <th style="padding: 12px 8px; text-align: left; border: 1px solid #ddd; font-weight: bold; color: white; background-color: #333333;">Description</th>
                    <th style="padding: 12px 8px; text-align: center; border: 1px solid #ddd; font-weight: bold; color: white; background-color: #333333;">Reference Image</th>
                    <th style="padding: 12px 8px; text-align: center; border: 1px solid #ddd; font-weight: bold; color: white; background-color: #333333;">QTY</th>
                    @foreach($currencyDataForView as $curr)
                        <th style="padding: 12px 8px; text-align: right; border: 1px solid #ddd; font-weight: bold; color: white; background-color: #333333;">Unit Price ({{ is_array($curr) ? $curr['code'] : $curr->code }})</th>
                    @endforeach
                    @foreach($currencyDataForView as $curr)
                        <th style="padding: 12px 8px; text-align: right; border: 1px solid #ddd; font-weight: bold; color: white; background-color: #333333;">Total ({{ is_array($curr) ? $curr['code'] : $curr->code }})</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->items as $idx => $item)
                @php
                    $baseUnit = (float)($item->unit_price ?? 0);
                    $baseQty = (float)($item->qty ?? 0);
                @endphp
                <tr>
                    <td class="col-sl text-center">{{ $idx + 1 }}</td>
                    <td class="col-model"><strong>{{ $item->model_name ?? '' }}</strong></td>
                    <td class="col-desc" style="white-space: pre-wrap;">{{ $item->description ?? '' }}</td>
                    <td class="col-img text-center">
                        @php
                            $imgUrl = null;
                            if (!empty($item->image_path)) $imgUrl = asset('storage/' . $item->image_path);
                            elseif (!empty($item->reference_image)) $imgUrl = Str::startsWith($item->reference_image, ['http://','https://']) ? $item->reference_image : asset('storage/' . $item->reference_image);
                        @endphp
                        @if($imgUrl)
                            <img src="{{ $imgUrl }}" class="ref-image" alt="Ref">
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="col-qty text-center">{{ $item->qty !== null ? $item->qty : '-' }}</td>
                    @foreach($currencyDataForView as $curr)
                        @php
                            $code = is_array($curr) ? $curr['code'] : $curr->code;
                            $sym = is_array($curr) ? $curr['symbol'] : $curr['symbol'];
                            $rate = is_array($curr) ? (float)($curr['rate'] ?? 1) : (float)($curr->rate ?? 1);
                            if ($code === 'RMB' || $code === 'CNY') $rate = 1;
                        @endphp
                        <td class="col-price text-end">{{ $sym }}{{ number_format($baseUnit * $rate, 2) }}</td>
                    @endforeach
                    @foreach($currencyDataForView as $curr)
                        @php
                            $sym = is_array($curr) ? $curr['symbol'] : $curr['symbol'];
                            $rate = is_array($curr) ? (float)($curr['rate'] ?? 1) : (float)($curr->rate ?? 1);
                            $code = is_array($curr) ? $curr['code'] : $curr->code;
                            if ($code === 'RMB' || $code === 'CNY') $rate = 1;
                        @endphp
                        <td class="col-total text-end">{{ $sym }}{{ number_format($baseUnit * $baseQty * $rate, 2) }}</td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        <div class="row">
            <div class="col-md-8"></div>
            <div class="col-md-4">
                <div class="summary-box">
                    @foreach($currencyDataForView as $curr)
                        @php
                            $code = is_array($curr) ? $curr['code'] : $curr->code;
                            $sym = is_array($curr) ? $curr['symbol'] : $curr['symbol'];
                            $rate = is_array($curr) ? (float)($curr['rate'] ?? 1) : (float)($curr->rate ?? 1);
                            if ($code === 'RMB' || $code === 'CNY') $rate = 1;
                            $converted = (float)$quotation->subtotal * $rate;
                        @endphp
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal ({{ $code }}):</span>
                            <span class="fw-bold">{{ $sym }}{{ number_format($converted, 2) }}</span>
                        </div>
                    @endforeach
                    @foreach($currencyDataForView as $curr)
                        @php
                            $code = is_array($curr) ? $curr['code'] : $curr->code;
                            $sym = is_array($curr) ? $curr['symbol'] : $curr['symbol'];
                            $rate = is_array($curr) ? (float)($curr['rate'] ?? 1) : (float)($curr->rate ?? 1);
                            if ($code === 'RMB' || $code === 'CNY') $rate = 1;
                            $converted = (float)$quotation->total * $rate;
                        @endphp
                        <div class="d-flex justify-content-between border-top pt-2">
                            <span class="fw-bold">TOTAL ({{ $code }}):</span>
                            <span class="total-amount">{{ $sym }}{{ number_format($converted, 2) }} {{ $code }}</span>
                        </div>
                    @endforeach
                    <small class="text-muted d-block mt-2">Base (RMB): ¥{{ number_format($quotation->subtotal, 2) }}</small>
                </div>
            </div>
        </div>

        @if($quotation->notes)
        <div class="mt-4">
            <h6 class="fw-bold">Notes:</h6>
            <div class="p-3 bg-light rounded">{!! $quotation->notes !!}</div>
        </div>
        @endif

        @if($quotation->remarks)
        <div class="mt-3">
            <h6 class="fw-bold">Remarks:</h6>
            <div class="p-3 bg-light rounded">{!! $quotation->remarks !!}</div>
        </div>
        @endif
    </div>
</div>
@endsection
