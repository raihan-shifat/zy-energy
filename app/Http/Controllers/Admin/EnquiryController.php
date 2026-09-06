<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\Request;

use App\Traits\PreventsManagerDelete;

class EnquiryController extends Controller
{
    use PreventsManagerDelete;
    public function index()
    {
        return view('admin.enquiries.index');
    }

    public function getData(Request $request)
    {
        // Single subquery for enquiry count per email (fixes N+1)
        $enquiries = Enquiry::query()
            ->selectRaw('enquiries.*, (
                SELECT COUNT(*) FROM enquiries e2 WHERE e2.email = enquiries.email
            ) AS enquiry_count')
            ->with('product.translation');

        if ($request->filled('status')) {
            $enquiries->where('status', $request->status);
        }

        if ($request->filled('email')) {
            $enquiries->where('email', 'like', '%' . $request->email . '%');
        }

        return datatables()->of($enquiries)
            ->addColumn('product', function ($enquiry) {
                // New enquiries carry a Category; older ones may reference a Product
                if ($enquiry->category_name) {
                    return $enquiry->category_name;
                }

                return $enquiry->product_name ?? ($enquiry->product ? $enquiry->product->translation->name ?? 'Product #' . $enquiry->product_id : '-');
            })
            ->addColumn('country', function ($enquiry) {
                return $enquiry->country ?? '-';
            })
            ->addColumn('phone', function ($enquiry) {
                return $enquiry->phone ?? '-';
            })
            ->addColumn('source_page', function ($enquiry) {
                if (empty($enquiry->source_page)) return '-';
                $url = $enquiry->source_page;
                $path = parse_url($url, PHP_URL_PATH) ?? '/';
                $path = $path === '/' ? '/home' : $path;
                return '<a href="' . e($url) . '" target="_blank" title="' . e($url) . '">' . e($path) . '</a>';
            })
            ->addColumn('customer_enquiry_count', function ($enquiry) {
                $count = $enquiry->enquiry_count ?? 1;
                if ($count <= 1) return $count;
                $url = route('admin.enquiries.index') . '?email=' . urlencode($enquiry->email);
                return '<a href="' . $url . '" class="badge bg-info text-decoration-none" title="View all ' . $count . ' enquiries from this customer">' . $count . '</a>';
            })
            ->addColumn('status_badge', function ($enquiry) {
                $color = $enquiry->status == 'new' ? 'warning' : ($enquiry->status == 'contacted' ? 'info' : 'secondary');
                return '<span class="badge bg-' . $color . '">' . ucfirst($enquiry->status) . '</span>';
            })
        ->addColumn('action', function ($enquiry) {
            $viewBtn = '<span class="border border-edit dt-trash rounded-3 d-inline-block"><a href="/admin/enquiries/' . $enquiry->id . '" class=""><i class="bi bi-eye pencil-edit-color"></i></a></span>';
            $deleteBtn = '<span class="border border-danger dt-trash rounded-3 d-inline-block" onclick="deleteEnquiry(' . $enquiry->id . ')"> <i class="bi bi-trash-fill text-danger"></i> </span>';

            return $viewBtn . ' ' . (auth()->user()->role === 'manager' ? '' : $deleteBtn);
        })
            ->rawColumns(['product', 'source_page', 'customer_enquiry_count', 'status_badge', 'action'])
            ->make(true);
    }

    public function export(Request $request)
    {
        $enquiries = Enquiry::query()->with('product.translation');

        if ($request->filled('status')) {
            $enquiries->where('status', $request->status);
        }

        if ($request->filled('email')) {
            $enquiries->where('email', 'like', '%' . $request->email . '%');
        }

        $enquiries = $enquiries->orderBy('created_at', 'desc')->get();

        $filename = 'enquiries_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($enquiries) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID', 'Name', 'Company', 'Email', 'WhatsApp', 'Country',
                'Category', 'Status', 'Source Page', 'Date', 'Message',
            ]);

            foreach ($enquiries as $enquiry) {
                $product = $enquiry->category_name
                    ?? ($enquiry->product_name ?? ($enquiry->product ? ($enquiry->product->translation->name ?? 'Product #' . $enquiry->product_id) : '-'));
                fputcsv($file, [
                    $enquiry->id,
                    $this->csvSafe($enquiry->name),
                    $this->csvSafe($enquiry->company ?? ''),
                    $this->csvSafe($enquiry->email),
                    $this->csvSafe($enquiry->phone ?? ''),
                    $this->csvSafe($enquiry->country ?? ''),
                    $this->csvSafe($product),
                    ucfirst($enquiry->status),
                    $this->csvSafe($enquiry->source_page ?? ''),
                    $enquiry->created_at->format('Y-m-d H:i:s'),
                    $this->csvSafe($enquiry->message ?? ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Neutralize CSV formula injection (cells starting with =, +, -, @, \t, \r).
     */
    protected function csvSafe($value): string
    {
        $value = (string) $value;

        if ($value !== '' && preg_match('/^[=+\-@\t\r]/', $value)) {
            return "'" . $value;
        }

        return $value;
    }

    public function show($id)
    {
        $enquiry = Enquiry::with('product.translation')->findOrFail($id);
        return view('admin.enquiries.show', compact('enquiry'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:new,contacted,closed']);

        $enquiry = Enquiry::findOrFail($id);
        $enquiry->status = $request->status;
        $enquiry->save();

        return response()->json(['success' => true, 'message' => 'Enquiry status updated.']);
    }

    public function destroy($id)
    {
        if ($guard = $this->rejectManagerDelete()) {
            return $guard;
        }

        $enquiry = Enquiry::find($id);
        if ($enquiry) {
            $enquiry->delete();
            return response()->json(['success' => true, 'message' => 'Enquiry deleted successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Error deleting enquiry.']);
    }
}
