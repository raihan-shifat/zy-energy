@extends('admin.layouts.admin')
@section('content')
<div class="card mt-4">
    <div class="card-header card-header-bg text-white">
        <h6 class="d-flex align-items-center mb-0 dt-heading">{{ __('cms.enquiries.view') }} #{{ $enquiry->id }}</h6>
    </div>

    <div class="card-body">
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <span class="badge bg-{{ $enquiry->status == 'new' ? 'warning' : ($enquiry->status == 'contacted' ? 'info' : 'secondary') }}">
                    {{ ucfirst($enquiry->status) }}
                </span>
                @php
                    $customerCount = \App\Models\Enquiry::where('email', $enquiry->email)->count();
                @endphp
                @if($customerCount > 1)
                    <a href="{{ route('admin.enquiries.index') }}?email={{ urlencode($enquiry->email) }}" class="badge bg-info text-decoration-none ms-2">
                        {{ $customerCount }} enquiries from this customer
                    </a>
                @endif
            </div>
            <div>
                <select class="form-select form-select-sm d-inline-block" id="status-select" style="width:auto;">
                    <option value="new" {{ $enquiry->status == 'new' ? 'selected' : '' }}>New</option>
                    <option value="contacted" {{ $enquiry->status == 'contacted' ? 'selected' : '' }}>Contacted</option>
                    <option value="closed" {{ $enquiry->status == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
                <button class="btn btn-sm btn-primary" onclick="updateStatus()">{{ __('cms.enquiries.update_status') }}</button>
            </div>
        </div>

        <table class="table table-bordered">
            <tr><th style="width:200px;">{{ __('cms.enquiries.name') }}</th><td>{{ $enquiry->name }}</td></tr>
            <tr><th>{{ __('cms.enquiries.company') }}</th><td>{{ $enquiry->company ?? '-' }}</td></tr>
            <tr><th>{{ __('cms.enquiries.email') }}</th><td><a href="mailto:{{ $enquiry->email }}">{{ $enquiry->email }}</a></td></tr>
            <tr><th>{{ __('cms.enquiries.phone') }}</th><td>{{ $enquiry->phone ?? '-' }}</td></tr>
            <tr><th>{{ __('cms.enquiries.country') }}</th><td>{{ $enquiry->country ?? '-' }}</td></tr>
            <tr>
                <th>Category</th>
                <td>
                    @if($enquiry->category_name)
                        {{ $enquiry->category_name }}
                    @elseif($enquiry->product_id && $enquiry->product)
                        <a href="{{ route('product.show', $enquiry->product->slug ?? $enquiry->product_id) }}" target="_blank">
                            {{ $enquiry->product_name ?? ($enquiry->product->translation->name ?? 'Product #' . $enquiry->product_id) }}
                        </a>
                    @else
                        {{ $enquiry->product_name ?? '-' }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>Source Page</th>
                <td>
                    @if($enquiry->source_page)
                        <a href="{{ $enquiry->source_page }}" target="_blank">{{ $enquiry->source_page }}</a>
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr><th>{{ __('cms.enquiries.message') }}</th><td style="white-space: pre-wrap;">{{ $enquiry->message ?? '-' }}</td></tr>
            <tr><th>{{ __('cms.enquiries.date') }}</th><td>{{ $enquiry->created_at->format('Y-m-d H:i:s') }}</td></tr>
        </table>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.enquiries.index') }}" class="btn btn-secondary">{{ __('cms.enquiries.back') }}</a>
            @if($customerCount > 1)
                <a href="{{ route('admin.enquiries.index') }}?email={{ urlencode($enquiry->email) }}" class="btn btn-outline-info">
                    <i class="bi bi-people"></i> View all {{ $customerCount }} enquiries from {{ $enquiry->email }}
                </a>
            @endif
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
function updateStatus() {
    var status = document.getElementById('status-select').value;
    fetch('{{ route('admin.enquiries.updateStatus', $enquiry->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': "{{ csrf_token() }}",
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            setTimeout(function() { window.location.reload(); }, 1000);
        } else {
            toastr.error(data.message);
        }
    });
}
</script>
@endsection
