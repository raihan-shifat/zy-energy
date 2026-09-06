@extends('admin.layouts.admin')

@section('content')
<div class="card mt-4">
    <div class="card-header card-header-bg text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 dt-heading">WhatsApp Contacts</h6>
        <a href="{{ route('admin.contact.whatsapp.create') }}" class="btn btn-sm btn-light"><i class="bi bi-plus-lg"></i> Add WhatsApp</a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered dt-style" id="whatsappTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Purpose</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($whatsapps as $whatsapp)
                        <tr>
                            <td>{{ $whatsapp->name }}</td>
                            <td>{{ $whatsapp->phone }}</td>
                            <td>{{ $whatsapp->purpose ?? 'N/A' }}</td>
                            <td>{{ $whatsapp->sort_order }}</td>
                            <td>
                                <span class="badge badge-{{ $whatsapp->is_active ? 'success' : 'secondary' }}">
                                    {{ $whatsapp->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.contact.whatsapp.edit', $whatsapp) }}" class="btn btn-sm btn-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('admin.contact.whatsapp.destroy', $whatsapp) }}" method="POST" style="display:inline-block;" class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure?')"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No WhatsApp contacts found.</td>
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
        $('#whatsappTable').DataTable({
            pageLength: 10,
            order: [[3, 'asc']],
            language: @json(__('cms.datatables'))
        });
    });
</script>
@endsection