<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Quotation\StoreQuotationRequest;
use App\Http\Requests\Admin\Quotation\UpdateQuotationRequest;
use App\Models\Category;
use App\Models\Customer;
use App\Models\ExchangeRate;
use App\Models\Language;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationSetting;
use App\Models\SiteSetting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class QuotationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->authorizeResource(Quotation::class, 'quotation');
    }

    public function index()
    {
        $quotations = Quotation::with(['customer', 'items.product', 'creator'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.quotations.index', compact('quotations'));
    }

    public function create()
    {
        $data = $this->getFormData();
        $data['quotation'] = new Quotation();

        return view('admin.quotations.create', $data);
    }

    public function store(StoreQuotationRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $validated = $request->validated();

            $customerId = $this->resolveCustomer($validated);

            $invoiceNumber = $this->generateInvoiceNumber();

            $quotation = Quotation::create([
                'customer_id'       => $customerId,
                'customer_name'     => $validated['customer_name'] ?? null,
                'company_name'      => $validated['company'] ?? null,
                'address'           => $validated['address'] ?? null,
                'phone'             => $validated['phone'] ?? null,
                'email'             => $validated['email'] ?? null,
                'date'              => $validated['date'],
                'language'          => $validated['languages'][0],
                'languages'         => array_values(array_unique($validated['languages'])),
                'display_currency'  => $validated['currencies'][0],
                'currencies'        => array_values(array_unique(array_merge(['RMB'], array_slice(array_diff($validated['currencies'], ['RMB']), 0, 1)))),
                'notes'             => $validated['notes'] ?? null,
                'remarks'           => $validated['remarks'] ?? null,
                'invoice_number'    => $invoiceNumber,
                'created_by'        => auth()->id(),
                'status'            => 'draft',
            ]);

            $this->saveLineItems($quotation, $validated, $request);

            return redirect()
                ->route('admin.quotations.show', $quotation)
                ->with('success', 'Quotation created successfully.');
        });
    }

    public function show(Quotation $quotation)
    {
        $quotation->load([
            'items.product.translations',
            'items.category.translations',
            'creator',
        ]);

        $data = $this->getViewData($quotation);

        return view('admin.quotations.show', $data);
    }

    public function edit(Quotation $quotation)
    {
        $quotation->load(['items.product', 'items.category']);

        $data = $this->getFormData();
        $data['quotation'] = $quotation;

        return view('admin.quotations.edit', $data);
    }

    public function update(UpdateQuotationRequest $request, Quotation $quotation)
    {
        return DB::transaction(function () use ($request, $quotation) {
            $validated = $request->validated();

            $customerId = $this->resolveCustomer($validated);

            $quotation->update([
                'customer_id'       => $customerId,
                'customer_name'     => $validated['customer_name'] ?? null,
                'company_name'      => $validated['company'] ?? null,
                'address'           => $validated['address'] ?? null,
                'phone'             => $validated['phone'] ?? null,
                'email'             => $validated['email'] ?? null,
                'date'              => $validated['date'],
                'language'          => $validated['languages'][0],
                'languages'         => array_values(array_unique($validated['languages'])),
                'display_currency'  => $validated['currencies'][0],
                'currencies'        => array_values(array_unique(array_merge(['RMB'], array_slice(array_diff($validated['currencies'], ['RMB']), 0, 1)))),
                'notes'             => $validated['notes'] ?? null,
                'remarks'           => $validated['remarks'] ?? null,
                'status'            => 'draft',
            ]);

            $quotation->items()->delete();

            $this->saveLineItems($quotation, $validated, $request);

            return redirect()
                ->route('admin.quotations.show', $quotation)
                ->with('success', 'Quotation updated successfully.');
        });
    }

    public function destroy(Quotation $quotation)
    {
        DB::transaction(function () use ($quotation) {
            $quotation->items()->delete();
            $quotation->delete();
        });

        return redirect()
            ->route('admin.quotations.index')
            ->with('success', 'Quotation deleted successfully.');
    }

    public function getData(Request $request)
    {
        $query = Quotation::with(['customer', 'creator'])
            ->orderByDesc('created_at');

        return DataTables::of($query)
            ->addColumn('invoice_number', fn ($q) => $q->invoice_number)
            ->addColumn('customer_name', fn ($q) => $q->customer?->name ?? $q->customer_name)
            ->addColumn('date', fn ($q) => $q->date?->format('Y-m-d') ?? '-')
            ->addColumn('total', fn ($q) => $q->total ? '¥' . number_format($q->total, 2) : '-')
            ->addColumn('status', fn ($q) => '<span class="badge badge-' . ($q->status === 'draft' ? 'secondary' : ($q->status === 'sent' ? 'primary' : 'success')) . '">' . ucfirst($q->status) . '</span>')
            ->addColumn('action', function ($quotation) {
                $viewBtn = '<a href="' . route('admin.quotations.show', $quotation) . '" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>';
                $editBtn = '<a href="' . route('admin.quotations.edit', $quotation) . '" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>';
                $printBtn = '<a href="' . route('admin.quotations.print', $quotation) . '" class="btn btn-sm btn-success" title="Print / Save as PDF" target="_blank"><i class="fas fa-print"></i></a>';
                $deleteBtn = '<form action="' . route('admin.quotations.destroy', $quotation) . '" method="POST" style="display:inline-block;" class="delete-quotation-form">
                    ' . csrf_field() . method_field('DELETE') . '
                    <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm(\'Are you sure?\')"><i class="fas fa-trash"></i></button>
                </form>';

                return $viewBtn . ' ' . $editBtn . ' ' . $printBtn . ' ' . $deleteBtn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function products(Request $request)
    {
        $search = $request->input('search', '');
        $products = Product::with(['translations' => function ($q) {
            $q->where('language_code', app()->getLocale());
        }])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('translations', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->limit(20)
            ->get(['id', 'slug'])
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->translations->first()?->name ?? $product->slug,
                ];
            });

        return response()->json($products);
    }

    public function duplicate(Quotation $quotation)
    {
        return DB::transaction(function () use ($quotation) {
            $quotation->load('items');

            $newQuotation = $quotation->replicate();
            $newQuotation->invoice_number = $this->generateInvoiceNumber();
            $newQuotation->status = 'draft';
            $newQuotation->user_id = auth()->id();
            $newQuotation->save();

            foreach ($quotation->items as $item) {
                $newItem = $item->replicate();
                $newItem->quotation_id = $newQuotation->id;
                $newItem->save();
            }

            $newQuotation->load(['items', 'customer', 'creator']);

            return redirect()
                ->route('admin.quotations.edit', $newQuotation)
                ->with('success', 'Quotation duplicated successfully. Please review and update as needed.');
        });
    }

    /**
     * Render a browser-friendly, print-optimized quotation view.
     *
     * This replaces the old server-side Dompdf generation. The page is printed
     * with the browser's native "Print / Save as PDF" dialog (client-side), so
     * no PHP GD extension or PDF library is required on the server.
     */
    public function printQuotation(Request $request, Quotation $quotation)
    {
        $quotation->load([
            'items.product.translations',
            'items.category.translations',
            'creator',
        ]);

        $data = $this->getViewData($quotation);
        $data['labels'] = $this->getLabels($quotation, $request->query('lang'));

        return view('admin.quotations.print-template', $data);
    }

    private function getFormData(): array
    {
        $exchangeRates = getExchangeRates();
        $currencySymbols = $this->getCurrencySymbolMap($exchangeRates);

        return [
            'customers'        => Customer::orderBy('name')->get(['id', 'name', 'email', 'phone', 'address']),
            'categories'       => Category::where('status', 1)->with('translation')->get(),
            'exchangeRates'    => $exchangeRates,
            'siteSettings'     => getSiteSettings(),
            'currencySymbols'  => $currencySymbols,
            'currencyRates'    => $exchangeRates->pluck('rate', 'code')->toArray(),
            'languages'        => Language::where('active', 1)->get(['code', 'name']),
            'defaultNotes'     => QuotationSetting::where('key', 'default_notes')->value('value'),
            'defaultRemarks'   => QuotationSetting::where('key', 'default_remarks')->value('value'),
        ];
    }

    private function getViewData(Quotation $quotation): array
    {
        $exchangeRates = getExchangeRates();
        $currencySymbols = $this->getCurrencySymbolMap($exchangeRates);

        return [
            'quotation'            => $quotation,
            'exchangeRates'        => $exchangeRates,
            'siteSettings'         => getSiteSettings(),
            'currencySymbols'      => $currencySymbols,
            'currencyRates'        => $exchangeRates->pluck('rate', 'code')->toArray(),
            'currencyData'         => $this->getCurrencyData($quotation),
            'currencyDataForView'  => $exchangeRates->filter(fn ($rate) => in_array(strtoupper($rate->code), $quotation->selectedCurrencies(), true))->map(function ($rate) use ($currencySymbols) {
                return [
                    'code'   => $rate->code,
                    'symbol' => $currencySymbols[$rate->code] ?? $rate->code,
                    'rate'   => $rate->rate,
                ];
            })->toArray(),
            'languages'            => Language::whereIn('code', $quotation->selectedLanguages())->get(['code', 'name']),
            'labels'               => $this->getLabels($quotation),
        ];
    }

    private function getCurrencySymbolMap($exchangeRates): array
    {
        $symbolMap = [
            'RMB' => '¥',
            'CNY' => '¥',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'INR' => '₹',
            'HKD' => 'HK$',
            'SGD' => 'S$',
            'AUD' => 'A$',
            'CAD' => 'C$',
        ];

        $result = [];
        foreach ($exchangeRates as $rate) {
            $result[$rate->code] = $symbolMap[$rate->code] ?? $rate->code;
        }

        return $result;
    }

    private function getCurrencyData(Quotation $quotation): array
    {
        $displayCurrencies = $quotation->selectedCurrencies();
        $exchangeRate      = $quotation->exchange_rate ?? 1;
        $baseCurrency      = 'RMB';

        $symbolMap = [
            'RMB' => '¥',
            'CNY' => '¥',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'INR' => '₹',
            'HKD' => 'HK$',
            'SGD' => 'S$',
            'AUD' => 'A$',
            'CAD' => 'C$',
        ];

        $symbol = $symbolMap[$baseCurrency] ?? $baseCurrency;
        if (!empty($displayCurrencies)) {
            $firstCurrency = $displayCurrencies[0];
            $symbol = $symbolMap[$firstCurrency] ?? $firstCurrency;
        }

        return [
            'display_currencies' => $displayCurrencies,
            'exchange_rate'      => $exchangeRate,
            'base_currency'      => $baseCurrency,
            'symbol'             => $symbol,
        ];
    }

    private function getLabels(Quotation $quotation, ?string $requestedLanguage = null): array
    {
        $selectedLanguages = $quotation->selectedLanguages();
        $language = in_array($requestedLanguage, $selectedLanguages, true)
            ? $requestedLanguage
            : ($selectedLanguages[0] ?? 'en');
        $isZh = $language === 'zh';

        return [
            'quote_title'      => $isZh ? 'æŠ¥ä»·å•' : 'QUOTATION',
            'invoice_no'       => $isZh ? 'å•å·' : 'Invoice No.',
            'date_label'       => $isZh ? 'æ—¥æœŸ' : 'Date',
            'bill_to'          => $isZh ? 'æ”¶æ¬¾æ–¹' : 'Bill To',
            'model_name'       => $isZh ? 'åž‹å·' : 'Model Name',
            'description'      => $isZh ? 'æè¿°' : 'Description',
            'reference_image'  => $isZh ? 'å‚è€ƒå›¾' : 'Ref Image',
            'qty'              => $isZh ? 'æ•°é‡' : 'QTY',
            'unit_price'       => $isZh ? 'å•ä»·' : 'Unit Price',
            'total'            => $isZh ? 'åˆè®¡' : 'Total',
            'subtotal'         => $isZh ? 'å°è®¡' : 'Subtotal',
            'total_label'      => $isZh ? 'æ€»è®¡' : 'TOTAL',
            'notes'            => $isZh ? 'å¤‡æ³¨' : 'Notes',
            'remarks'          => $isZh ? 'è¯´æ˜Ž' : 'Remarks',
            'sl'               => $isZh ? 'åºå·' : 'SL',
            'no_image'         => $isZh ? 'æ— å›¾ç‰‡' : 'No Image',
            'img_attached'     => $isZh ? 'å›¾ç‰‡é™„ä»¶' : 'Image Attached',
            'no_items'         => $isZh ? 'æš‚æ— æ˜Žç»†' : 'No line items',
            'base_rate'        => $isZh ? 'åŸºä»·' : 'Base',
        ];
    }

    private function resolveCustomer(array $validated): ?int
    {
        $customerId = $validated['customer_id'] ?? null;

        if (!$customerId && !empty($validated['email'])) {
            $customer = Customer::firstOrCreate(
                ['email' => $validated['email']],
                [
                    'name'     => $validated['customer_name'],
                    'email'    => $validated['email'],
                    'phone'    => $validated['phone'] ?? null,
                    'address'  => $validated['address'] ?? null,
                    'company'  => $validated['company'] ?? null,
                ]
            );

            $customerId = $customer->id;
        }

        return $customerId;
    }

    private function saveLineItems(Quotation $quotation, array $validated, \Illuminate\Http\Request $request): void
    {
        $subtotal  = 0;
        $basePrice = 0;

        foreach ($validated['line_items'] ?? [] as $index => $item) {
            if (empty($item['qty']) || empty($item['unit_price'])) {
                continue;
            }

            $itemTotal = $item['qty'] * $item['unit_price'];
            $subtotal  += $itemTotal;
            $basePrice += $itemTotal;

            $lineItem = $quotation->items()->create([
                'product_id'       => $item['product_id'] ?? null,
                'category_id'      => $item['category_id'] ?? null,
                'model_name'       => $item['model_name'] ?? null,
                'description'      => $item['description'] ?? null,
                'qty'              => $item['qty'],
                'unit_price'       => $item['unit_price'],
                'line_total'       => $itemTotal,
                'sort_order'       => $index,
            ]);

            $fileKey = "line_items.{$index}.reference_image";
            if (! $request->hasFile($fileKey)) {
                $fileKey = "line_items.{$index}.image";
            }
            if (! $request->hasFile($fileKey)) {
                $fileKey = "items.{$index}.reference_image";
            }
            if (! $request->hasFile($fileKey)) {
                $fileKey = "items.{$index}.image";
            }
            if ($request->hasFile($fileKey)) {
                $file = $request->file($fileKey);
                if ($file && $file->isValid()) {
                    $path = $file->store('quotations/images', 'public');
                    $lineItem->update(['reference_image' => $path]);
                }
            }
        }

        $quotation->update([
            'subtotal'   => $subtotal,
            'base_price' => $basePrice,
            'total'      => $subtotal,
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        return DB::transaction(function () {
            $today = now()->format('Ymd');

            $lastQuotation = Quotation::whereDate('created_at', today())
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $sequence = 1;
            if ($lastQuotation && preg_match('/(\d+)$/', $lastQuotation->invoice_number, $matches)) {
                $sequence = (int)$matches[1] + 1;
            }

            return 'QTY-' . $today . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
        });
    }
}
