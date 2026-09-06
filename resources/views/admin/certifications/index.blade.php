@extends('admin.layouts.admin')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
@endsection

@section('content')
    <div class="card mt-4">
        <div class="card-header card-header-bg text-white d-flex justify-content-between align-items-center">
            <h6 class="d-flex align-items-center mb-0 dt-heading">{{ __('cms.certifications.heading') }}</h6>
            <a href="{{ route('admin.certifications.create') }}" class="btn btn-sm btn-success">{{ __('cms.certifications.add_new') }}</a>
        </div>

        <div class="card-body">
            <table id="certifications-table" class="table table-bordered mt-4 dt-style">
                <thead>
                    <tr>
                        <th>{{ __('cms.certifications.id') }}</th>
                        <th>{{ __('cms.certifications.name') }}</th>
                        <th>{{ __('cms.certifications.logo') }}</th>
                        <th>{{ __('cms.certifications.document') }}</th>
                        <th>{{ __('cms.certifications.status') }}</th>
                        <th>{{ __('cms.certifications.action') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="deleteCertificationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('cms.certifications.confirm_delete') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">{{ __('cms.certifications.confirm_delete_msg') }}</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cms.certifications.cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteCertification">{{ __('cms.certifications.delete') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    @if (session('success'))
        <script>
            toastr.success("{{ session('success') }}", "Success", {
                closeButton: true,
                progressBar: true,
                positionClass: "toast-top-right",
                timeOut: 5000
            });
        </script>
    @endif

    <script>
        $(document).ready(function() {
            var isManager = {{ auth()->user()->role === 'manager' ? 'true' : 'false' }};

            $('#certifications-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.certifications.data') }}",
                    type: 'POST',
                    data: function(d) {
                        d._token = "{{ csrf_token() }}";
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    {
                        data: 'image_url',
                        render: function(data) {
                            if (data) {
                                var path = data.startsWith('http') ? data : '/storage/' + data;
                                return '<img src="' + path + '" alt="Logo" width="50">';
                            }
                            return '{{ __('cms.certifications.no_logo') }}';
                        }
                    },
                    {
                        data: 'document_url',
                        render: function(data) {
                            if (data) {
                                var path = data.startsWith('http') ? data : '/storage/' + data;
                                return '<a href="' + path + '" target="_blank" class="btn btn-sm btn-outline-primary">PDF</a>';
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'status',
                        render: function(data, type, row) {
                            var isChecked = data ? 'checked' : '';
                            return `<label class="switch">
                                        <input type="checkbox" class="toggle-status" data-id="${row.id}" ${isChecked}>
                                        <span class="slider round"></span>
                                    </label>`;
                        }
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            var editBtn = '<span class="border border-edit dt-trash rounded-3 d-inline-block"><a href="/admin/certifications/' + row.id + '/edit" class=""><i class="bi bi-pencil-fill pencil-edit-color"></i></a></span>';
                            var deleteBtn = isManager ? '' : '<span class="border border-danger dt-trash rounded-3 d-inline-block" onclick="deleteCertification(' + row.id + ')"> <i class="bi bi-trash-fill text-danger"></i> </span>';
                            return editBtn + ' ' + deleteBtn;
                        }
                    }
                ],
                pageLength: 10
            });

            $(document).on('change', '.toggle-status', function() {
                var id = $(this).data('id');
                var isActive = $(this).prop('checked') ? 1 : 0;
                $.ajax({
                    url: '{{ route('admin.certifications.updateStatus') }}',
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id,
                        status: isActive
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        alert('Error updating status!');
                    }
                });
            });
        });

        let certificationToDeleteId = null;

        function deleteCertification(id) {
            certificationToDeleteId = id;
            $('#deleteCertificationModal').modal('show');
            $('#confirmDeleteCertification').off('click').on('click', function() {
                if (certificationToDeleteId !== null) {
                    $.ajax({
                        url: '{{ route('admin.certifications.destroy', ':id') }}'.replace(':id', certificationToDeleteId),
                        method: 'DELETE',
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(response) {
                            if (response.success) {
                                $('#certifications-table').DataTable().ajax.reload();
                                toastr.success(response.message);
                                $('#deleteCertificationModal').modal('hide');
                            }
                        },
                        error: function() {
                            $('#deleteCertificationModal').modal('hide');
                        }
                    });
                }
            });
        }
    </script>
@endsection
