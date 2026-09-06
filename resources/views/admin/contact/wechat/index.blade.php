@extends('admin.layouts.admin')

@section('content')
<div class="card mt-4">
    <div class="card-header card-header-bg text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 dt-heading">WeChat Contacts</h6>
        <a href="{{ route('admin.contact.wechat.create') }}" class="btn btn-sm btn-light"><i class="bi bi-plus-lg"></i> Add WeChat</a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered dt-style" id="wechatTable">
                <thead>
                    <tr>
                        <th>QR Code</th>
                        <th>Name</th>
                        <th>WeChat ID</th>
                        <th>Purpose</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wechats as $wechat)
                        <tr>
                            <td>
                                @if($wechat->qr_code)
                                    <img src="{{ $wechat->qr_code_url }}" alt="{{ $wechat->name }}" style="max-width: 80px; max-height: 80px;">
                                @else
                                    <span class="text-muted">No QR Code</span>
                                @endif
                            </td>
                            <td>{{ $wechat->name }}</td>
                            <td>{{ $wechat->wechat_id ?? 'N/A' }}</td>
                            <td>{{ $wechat->purpose ?? 'N/A' }}</td>
                            <td>{{ $wechat->sort_order }}</td>
                            <td>
                                <span class="badge badge-{{ $wechat->is_active ? 'success' : 'secondary' }}">
                                    {{ $wechat->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.contact.wechat.edit', $wechat) }}" class="btn btn-sm btn-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('admin.contact.wechat.destroy', $wechat) }}" method="POST" style="display:inline-block;" class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure?')"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No WeChat contacts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        $('#wechatTable').DataTable({
            pageLength: 10,
            order: [[4, 'asc']],
            language: @json(__('cms.datatables'))
        });
    });
</script>
@endsection