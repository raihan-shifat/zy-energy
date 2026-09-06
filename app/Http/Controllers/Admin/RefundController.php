<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\Http\Request;
use App\Traits\PreventsManagerDelete;
use Yajra\DataTables\Facades\DataTables;

class RefundController extends Controller
{
    use PreventsManagerDelete;
    public function index()
    {
        return view('admin.refunds.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $refunds = Refund::with('payment')->select('refunds.*');

            return DataTables::of($refunds)
                ->addColumn('payment', fn ($row) => $row->payment ? 'Payment #'.$row->payment->id : '—')
        ->addColumn('action', function ($row) {
            $showBtn = '<a href="'.route('admin.refunds.show', $row->id).'" 
                    class="border border-primary dt-show rounded-3 d-inline-block me-1 px-2 py-1">
                    <i class="bi bi-eye-fill text-primary"></i>
                </a>';

            $deleteBtn = '<span class="border border-danger dt-trash rounded-3 d-inline-block" 
                    onclick="deleteRefund('.$row->id.')"> 
                    <i class="bi bi-trash-fill text-danger"></i>
                </span>';

            return $showBtn . ' ' . (auth()->user()->role === 'manager' ? '' : $deleteBtn);
        })
                ->make(true);
        }
    }

    public function show($id)
    {
        $refund = Refund::with('payment.user', 'payment.order', 'payment.gateway')->findOrFail($id);

        return view('admin.refunds.show', compact('refund'));
    }

    public function destroy(Refund $refund)
    {
        if ($guard = $this->rejectManagerDelete()) {
            return $guard;
        }

        $refund->delete();

        return response()->json(['success' => true, 'message' => 'Refund deleted successfully.']);
    }
}
