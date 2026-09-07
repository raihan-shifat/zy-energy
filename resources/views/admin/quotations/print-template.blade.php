@php
    // Client-side print template. Opened in the browser and converted to PDF
    // via the native "Print / Save as PDF" dialog — no server-side PDF library.
    $isZh = ($labels['quote_title'] ?? 'QUOTATION') === '报价单';
    $requestedLang = request()->query('lang');
    $activeLang = in_array($requestedLang, $quotation->selectedLanguages(), true)
        ? $requestedLang
        : ($quotation->selectedLanguages()[0] ?? 'en');
    $logo = null;
    if ($siteSettings && $siteSettings->logo) {
        $logo = \Illuminate\Support\Str::startsWith($siteSettings->logo, ['http://', 'https://'])
            ? $siteSettings->logo
            : asset('storage/' . ltrim($siteSettings->logo, '/'));
    } elseif (is_file(public_path('logo.png'))) {
        $logo = asset('logo.png');
    }
    $logo = $logo ?: null;

    $itemImageMap = [];
    foreach ($quotation->items as $li) {
        $src = null;
        if (! empty($li->image_path)) {
            $src = asset('storage/' . $li->image_path);
        } elseif (! empty($li->reference_image)) {
            $src = \Illuminate\Support\Str::startsWith($li->reference_image, ['http://', 'https://'])
                ? $li->reference_image
                : asset('storage/' . $li->reference_image);
        }
        $itemImageMap[$li->id] = $src;
    }

    $activeLang = in_array($requestedLang, $quotation->selectedLanguages(), true)
        ? $requestedLang
        : ($quotation->selectedLanguages()[0] ?? 'en');

    $dateLine = $quotation->date
        ? $quotation->date->format('Y-m-d')
        : \Carbon\Carbon::parse($quotation->created_at)->format('Y-m-d');
@endphp
<!DOCTYPE html>
<html lang="{{ $isZh ? 'zh-CN' : 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($labels['quote_title'] ?? 'QUOTATION') . ' - ' . ($quotation->invoice_number ?? 'Draft') }}</title>
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, 'Microsoft YaHei', 'PingFang SC', sans-serif;
            color: #222;
            font-size: 13px;
            line-height: 1.45;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ---- A4 sizing for both screen & print ---- */
        @page {
            size: A4 portrait;
            margin: 14mm 11mm 14mm 11mm;
        }

        .print-sheet {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            background: #fff;
            padding: 0 2mm;
        }

        /* ---- On-screen toolbar (hidden when printing) ---- */
        .no-print {
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            background: #0b3b2c;
            color: #fff;
            border-radius: 0 0 8px 8px;
        }
        .no-print .brand { font-weight: 700; letter-spacing: 0.3px; }
        .no-print .spacer { flex: 1 1 auto; }
        .no-print a, .no-print button {
            text-decoration: none;
            cursor: pointer;
            border-radius: 6px;
            border: 1px solid transparent;
            font-size: 13px;
            padding: 7px 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .no-print .btn-print { background: #ffffff; color: #0b3b2c; font-weight: 700; }
        .no-print .btn-print:hover { background: #e6f4ee; }
        .no-print .lang-link { background: transparent; color: #cfe9de; border-color: #3f7a63; }
        .no-print .lang-link.active { background: #12894f; color: #fff; border-color: #12894f; }
        .no-print .btn-back { background: transparent; color: #cfe9de; }

        /* ---- Document header ---- */
        .doc-header { display: flex; justify-content: space-between; gap: 20px; padding: 6px 0 14px; }
        .doc-header .left { flex: 1 1 auto; }
        .doc-header .logo-img { max-height: 64px; max-width: 180px; object-fit: contain; margin-bottom: 8px; }
        .company-name { font-size: 24px; font-weight: 800; color: #0e7a4f; line-height: 1.15; margin: 0 0 4px; }
        .company-address { font-size: 11px; color: #666; margin: 0; line-height: 1.5; }
        .doc-header .right { flex: 0 0 auto; text-align: right; }
        .quote-title { font-size: 30px; font-weight: 800; color: #0e7a4f; letter-spacing: 1px; line-height: 1.1; margin: 0 0 10px; }
        .meta-box {
            display: inline-block;
            text-align: left;
            border: 1.5px solid #0e7a4f;
            background: #f4faf7;
            border-radius: 6px;
            font-size: 12px;
        }
        .meta-box .meta-row { display: flex; gap: 14px; padding: 5px 12px; }
        .meta-box .meta-row + .meta-row { border-top: 1px solid #e2ece7; }
        .meta-box .k { font-weight: 700; color: #333; }
        .meta-box .v { color: #111; }

        /* ---- Bill To ---- */
        .bill-to {
            margin: 12px 0 16px;
            padding: 10px 14px;
            background: #f7f9f8;
            border: 1px solid #dbe4e0;
            border-radius: 6px;
        }
        .bill-to h3 {
            margin: 0 0 6px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #0e7a4f;
        }
        .bill-to p { margin: 2px 0; line-height: 1.5; }
        .bill-to .bname { font-weight: 700; font-size: 14px; }

        /* ---- Items table ---- */
        table.items { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 4px; }
        table.items thead { display: table-header-group; }
        table.items th {
            background: #0e7a4f;
            color: #fff;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            padding: 8px 5px;
            border: 1px solid #0a5c3d;
        }
        table.items td {
            padding: 6px 5px;
            border: 1px solid #d4dcd8;
            vertical-align: top;
            word-break: break-word;
            overflow-wrap: anywhere;
            font-size: 12px;
        }
        table.items tbody tr:nth-child(even) td { background: #f7faf8; }
        table.items tbody tr { break-inside: avoid; page-break-inside: avoid; }
        .c-sl { width: 5%; text-align: center; }
        .c-model { width: 13%; }
        .c-desc { width: 30%; white-space: pre-wrap; }
        .c-img { width: 12%; text-align: center; }
        .c-qty { width: 7%; text-align: center; }
        .c-price { width: 16%; text-align: right; }
        .c-total { width: 17%; text-align: right; }
        .c-desc p, .c-desc div { margin: 0; }
        .cell-model { font-weight: 600; }
        .ref-img { max-width: 62px; max-height: 62px; object-fit: contain; border: 1px solid #dbe4e0; border-radius: 3px; }
        .no-image { font-size: 10px; color: #999; font-style: italic; }
        td.empty-row { text-align: center !important; color: #888 !important; padding: 24px !important; }

        /* ---- Totals ---- */
        .totals-wrap { margin-top: 14px; display: flex; justify-content: flex-end; }
        table.totals { border-collapse: collapse; min-width: 46%; font-size: 12.5px; }
        table.totals td { padding: 6px 14px; border-bottom: 1px solid #e0e6e3; }
        table.totals tr.subtotal-row td { background: #f4faf7; font-weight: 600; }
        table.totals tr.total-row td { background: #0e7a4f; color: #fff; font-weight: 800; font-size: 13.5px; border-bottom: none; }
        table.totals .td-label { text-align: right; font-weight: 600; }
        table.totals tr.subtotal-row .td-label, table.totals tr.total-row .td-label { font-weight: 700; }
        table.totals .td-amount { text-align: right; font-family: 'Consolas', 'Menlo', monospace; }

        /* ---- Notes / Remarks ---- */
        .section { margin-top: 18px; page-break-inside: avoid; }
        .section h4 {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            color: #0e7a4f;
            margin: 0 0 4px;
            padding-bottom: 3px;
            border-bottom: 1px solid #0e7a4f;
        }
        .section .body { font-size: 12px; line-height: 1.6; }
        .section .body p { margin: 4px 0; }
        .section .body ul, .section .body ol { margin: 4px 0; padding-left: 18px; }

        .foot-note { margin-top: 22px; font-size: 10px; color: #999; text-align: center; }

        @media print {
            .no-print { display: none !important; }
            body { font-size: 12px; }
            .print-sheet { max-width: none; padding: 0; }
            a { color: inherit; }
        }
        @media (max-width: 600px) {
            .doc-header { flex-direction: column; }
            .doc-header .right { text-align: left; }
        }
    </style>
</head>
<body>
    @php
        $autoPrint = request()->query('auto') == 1;
    @endphp

    <div class="no-print">
        <span class="brand">Quotation Print Preview</span>
        <span class="spacer"></span>
        @foreach($quotation->selectedLanguages() as $lang)
            <a class="lang-link {{ $activeLang === $lang ? 'active' : '' }}"
               href="{{ route('admin.quotations.print', ['quotation' => $quotation, 'lang' => $lang]) }}">{{ strtoupper($lang) }}</a>
        @endforeach
        <a class="btn-back" href="{{ route('admin.quotations.show', $quotation) }}">&larr; Back</a>
        <button type="button" class="btn-print" onclick="window.print();">
            <span aria-hidden="true">&#128424;</span> Print / Save as PDF
        </button>
    </div>

    <div class="print-sheet">
        <div class="doc-header">
            <div class="left">
                @if($logo)
                    <div><img src="{{ $logo }}" alt="Logo" class="logo-img"></div>
                @endif
                <div class="company-name">{{ $siteSettings->site_name ?? 'ZY Energy' }}</div>
                <p class="company-address">{{ $siteSettings->address ?? '' }}</p>
                @if($siteSettings && ($siteSettings->contact_email || $siteSettings->contact_phone))
                    <p class="company-address">
                        {{ $siteSettings->contact_email ?? '' }}
                        {{ ($siteSettings->contact_email && $siteSettings->contact_phone) ? ' | ' : '' }}
                        {{ $siteSettings->contact_phone ?? '' }}
                    </p>
                @endif
            </div>
            <div class="right">
                <div class="quote-title">{{ $labels['quote_title'] ?? 'QUOTATION' }}</div>
                <div class="meta-box">
                    <div class="meta-row">
                        <span class="k">{{ $labels['invoice_no'] ?? 'Invoice No.' }}:</span>
                        <span class="v">{{ $quotation->invoice_number ?? 'N/A' }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="k">{{ $labels['date_label'] ?? 'Date' }}:</span>
                        <span class="v">{{ $dateLine }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bill-to">
            <h3>{{ $labels['bill_to'] ?? 'Bill To' }}</h3>
            <p class="bname">{{ $quotation->customer_name ?? '—' }}</p>
            @if($quotation->company_name)<p>{{ $quotation->company_name }}</p>@endif
            @if($quotation->address)<p>{{ $quotation->address }}</p>@endif
            @if($quotation->phone)<p>Tel: {{ $quotation->phone }}</p>@endif
            @if($quotation->email)<p>Email: {{ $quotation->email }}</p>@endif
        </div>

        <table class="items">
            <colgroup>
                <col class="c-sl">
                <col class="c-model">
                <col class="c-desc">
                <col class="c-img">
                <col class="c-qty">
                <col class="c-price">
                <col class="c-total">
            </colgroup>
            <thead>
                <tr>
                    <th>{{ $labels['sl'] ?? 'SL' }}</th>
                    <th>{{ $labels['model_name'] ?? 'Model Name' }}</th>
                    <th>{{ $labels['description'] ?? 'Description' }}</th>
                    <th>{{ $labels['reference_image'] ?? 'Ref Image' }}</th>
                    <th>{{ $labels['qty'] ?? 'QTY' }}</th>
                    <th>{{ $labels['unit_price'] ?? 'Unit Price' }} (¥)</th>
                    <th>{{ $labels['total'] ?? 'Total' }} (¥)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quotation->items as $lineItem)
                    <tr>
                        <td class="c-sl">{{ $loop->iteration }}</td>
                        <td class="c-model cell-model">{{ $lineItem->model_name ?? '—' }}</td>
                        <td class="c-desc">{!! $lineItem->description ?? '' !!}</td>
                        <td class="c-img">
                            @if($itemImageMap[$lineItem->id] ?? null)
                                <img src="{{ $itemImageMap[$lineItem->id] }}" class="ref-img" alt="Ref">
                            @else
                                <span class="no-image">{{ $labels['no_image'] ?? 'No Image' }}</span>
                            @endif
                        </td>
                        <td class="c-qty">{{ $lineItem->qty }}</td>
                        <td class="c-price">¥{{ number_format((float) $lineItem->unit_price, 2) }}</td>
                        <td class="c-total">¥{{ number_format((float) $lineItem->qty * (float) $lineItem->unit_price, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-row">{{ $labels['no_items'] ?? 'No line items' }}</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="totals-wrap">
            <table class="totals">
                @foreach($currencyDataForView ?? [] as $curr)
                    @php
                        $code = is_array($curr) ? $curr['code'] : $curr->code;
                        $sym  = is_array($curr) ? $curr['symbol'] : $curr->symbol;
                        $rate = is_array($curr) ? (float) ($curr['rate'] ?? 1) : (float) ($curr->rate ?? 1);
                        if (in_array(strtoupper($code), ['RMB', 'CNY'], true)) $rate = 1;
                    @endphp
                    <tr class="subtotal-row">
                        <td class="td-label">{{ $labels['subtotal'] ?? 'Subtotal' }} ({{ $code }}):</td>
                        <td class="td-amount">{{ $sym }}{{ number_format((float) $quotation->subtotal * $rate, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td class="td-label">{{ $labels['total_label'] ?? 'TOTAL' }} ({{ $code }}):</td>
                        <td class="td-amount">{{ $sym }}{{ number_format((float) $quotation->total * $rate, 2) }}</td>
                    </tr>
                @endforeach
            </table>
        </div>

        @if($quotation->notes)
            <div class="section">
                <h4>{{ $labels['notes'] ?? 'Notes' }}</h4>
                <div class="body">{!! $quotation->notes !!}</div>
            </div>
        @endif

        @if($quotation->remarks)
            <div class="section">
                <h4>{{ $labels['remarks'] ?? 'Remarks' }}</h4>
                <div class="body">{!! $quotation->remarks !!}</div>
            </div>
        @endif

        @if($quotation->base_price && $quotation->base_price != $quotation->subtotal)
            <div class="section">
                <div class="body" style="color:#666;">
                    {{ $labels['base_rate'] ?? 'Base' }} (RMB): ¥{{ number_format((float) $quotation->base_price, 2) }}
                </div>
            </div>
        @endif

        <p class="foot-note">{{ $siteSettings->site_name ?? '' }} &middot; {{ $dateLine }}</p>
    </div>

    <script>
        // Trigger the browser's native print-to-PDF dialog once the page is
        // fully rendered (used by the language-specific print links).
        @if($autoPrint)
        window.addEventListener('load', function () {
            window.setTimeout(function () { window.print(); }, 400);
        });
        @endif
    </script>
</body>
</html>
