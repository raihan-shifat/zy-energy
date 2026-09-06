@extends('admin.layouts.admin')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endsection

@section('content')
    <div class="card mt-4">
        <div class="card-header card-header-bg text-white">
            <h6 class="d-flex align-items-center mb-0 dt-heading">{{ __('cms.users.title_list') }}</h6>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('admin.users.create') }}" class="btn btn-success">
                    <i class="bi bi-person-plus"></i> {{ __('cms.users.add_new') }}
                </a>
            </div>
            <table id="users-table" class="table table-bordered mt-2 dt-style">
                <thead>
                    <tr>
                        <th>{{ __('cms.users.id') }}</th>
                        <th>{{ __('cms.users.name') }}</th>
                        <th>{{ __('cms.users.email') }}</th>
                        <th>{{ __('cms.users.phone') }}</th>
                        <th>{{ __('cms.users.role') }}</th>
                        <th>{{ __('cms.users.status') }}</th>
                        <th>{{ __('cms.users.actions') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel">{{ __('cms.users.modal_confirm_delete_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">{{ __('cms.users.modal_confirm_delete_body') }}</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cms.users.cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteUser">{{ __('cms.users.delete') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

@php
    $datatableLang = __('cms.datatables');
@endphp

@if (session('success'))
<script>
    toastr.success("{{ session('success') }}", "{{ __('cms.users.success') }}", {
        closeButton: true,
        progressBar: true,
        positionClass: "toast-top-right",
        timeOut: 5000
    });
</script>
@endif

<script>
$(document).ready(function() {
    $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.users.data') }}",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'phone', name: 'phone' },
            { data: 'role', name: 'role' },
            {
                data: 'status',
                name: 'status',
                render: function(data) {
                    return data === 'active'
                        ? '<span class="badge bg-success">{{ __('cms.users.active') }}</span>'
                        : '<span class="badge bg-danger">{{ __('cms.users.inactive') }}</span>';
                }
            },
            {
                data: 'action',
                orderable: false,
                searchable: false
            }
        ],
        pageLength: 10,
        language: @json($datatableLang)
    });
});

let userToDeleteId = null;

function deleteUser(id) {
    userToDeleteId = id;
    $('#deleteUserModal').modal('show');

    $('#confirmDeleteUser').off('click').on('click', function() {
        if (userToDeleteId !== null) {
            $.ajax({
                url: '{{ route('admin.users.destroy', ':id') }}'.replace(':id', userToDeleteId),
                method: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}",
                },
                success: function(response) {
                    if (response.success) {
                        $('#users-table').DataTable().ajax.reload();
                        toastr.success(response.message, "{{ __('cms.users.success') }}", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-top-right",
                            timeOut: 5000
                        });
                        $('#deleteUserModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    let msg = "{{ __('cms.users.error_delete') }}";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    toastr.error(msg, "Error");
                    $('#deleteUserModal').modal('hide');
                }
            });
        }
    });
}
</script>
@endsection
