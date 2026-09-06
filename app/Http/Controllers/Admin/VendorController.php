<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Traits\PreventsManagerDelete;
use Yajra\DataTables\Facades\DataTables;

class VendorController extends Controller
{
    use PreventsManagerDelete;
    public function index()
    {
        return view('admin.vendors.index');
    }

    public function getVendorData()
    {
        $vendors = Vendor::select(['id', 'name', 'email', 'phone', 'status']);

        return DataTables::of($vendors)
        ->addColumn('action', function ($vendor) {
            $deleteBtn = '<span class="border border-danger dt-trash rounded-3 d-inline-block" onclick="deleteVendor('.$vendor->id.')"><i class="bi bi-trash-fill text-danger"></i></span>';

            return auth()->user()->role === 'manager' ? '' : $deleteBtn;
        })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        return view('admin.vendors.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:vendors,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->symbols(),
            ],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s\-]+$/'],
            'status' => ['required', 'in:active,inactive,banned'],
        ]);

        Vendor::create([
            'name' => trim($validatedData['name']),
            'email' => strtolower(trim($validatedData['email'])),
            'password' => Hash::make($validatedData['password']),
            'phone' => $validatedData['phone'] ?? null,
            'status' => $validatedData['status'],
        ]);

        return redirect()->route('admin.vendors.index')
            ->with('success', 'Vendor registered successfully!');
    }

    public function destroy($id)
    {
        if ($guard = $this->rejectManagerDelete()) {
            return $guard;
        }

        $vendor = Vendor::findOrFail($id);

        // FK chain: vendors → shops (cascade) → products (cascade) → order_details (cascade).
        // Deleting a vendor with products would silently destroy customers' order line items.
        $productCount = \App\Models\Product::where('vendor_id', $vendor->id)
            ->orWhereHas('shop', fn ($q) => $q->where('vendor_id', $vendor->id))
            ->count();

        if ($productCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete: this vendor has {$productCount} product(s). Remove them first.",
            ]);
        }

        $vendor->delete();

        return response()->json([
            'success' => true,
            'message' => __('cms.vendors.success_delete'),
        ]);
    }
}
