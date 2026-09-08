@extends('admin.layouts.admin')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="card mt-4">
    <div class="card-header card-header-bg text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 dt-heading">Quotations</h6>
        <a href="{{ route('admin.quotations.create') }}" class="btn btn-sm btn-light"><i class="bi bi-plus-lg"></i> New Quotation</a>
    </div>
    <div class="card-body">
        <table id="quotations-table" class="table table-bordered mt-4 dt-style">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
@php $datatableLang = __('cms.datatables'); @endphp

@if(session('success'))
<script>toastr.success("{{ session('success') }}", "Success", { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: 5000 });</script>
@endif

<script>
$(document).ready(function() {
    $('#quotations-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.quotations.data') }}",
            type: "POST",
            data: function(d) { d._token = "{{ csrf_token() }}"; }
        },
        columns: [
            { data: 'invoice_number', name: 'invoice_number' },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'date', name: 'date' },
            { data: 'total', name: 'total', orderable: false },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[2, 'desc']],
        pageLength: 10,
        language: @json($datatableLang)
    });
});
</script>
@endsection
