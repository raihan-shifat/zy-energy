@extends('admin.layouts.admin')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
@endsection

@section('content')
    <div class="card mt-4">
        <div class="card-header card-header-bg text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="d-flex align-items-center mb-0 dt-heading">{{ __('cms.enquiries.heading') }}</h6>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <form class="d-flex align-items-center gap-2" method="GET">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width:auto;">
                        <option value="">{{ __('cms.enquiries.all_status') }}</option>
                        <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                        <option value="contacted" {{ request('status') == 'contacted' ? 'selected' : '' }}>Contacted</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </form>
                <form class="d-flex align-items-center gap-2" method="GET">
                    <input type="text" name="email" class="form-control form-select-sm" placeholder="{{ __('cms.enquiries.filter_email') }}" value="{{ request('email') }}" style="width:200px;">
                    <button type="submit" class="btn btn-sm btn-outline-light">{{ __('cms.enquiries.filter') }}</button>
                    @if(request('email'))
                        <a href="{{ route('admin.enquiries.index') }}" class="btn btn-sm btn-outline-light">{{ __('cms.enquiries.clear') }}</a>
                    @endif
                </form>
                <a href="{{ route('admin.enquiries.export', request()->only('status', 'email')) }}" class="btn btn-sm btn-success ms-2">
                    <i class="bi bi-file-earmark-spreadsheet"></i> {{ __('cms.enquiries.export_csv') }}
                </a>
            </div>
        </div>

        <div class="card-body">
            <table id="enquiries-table" class="table table-bordered mt-4 dt-style">
                <thead>
                    <tr>
                        <th>{{ __('cms.enquiries.id') }}</th>
                        <th>{{ __('cms.enquiries.name') }}</th>
                        <th>{{ __('cms.enquiries.company') }}</th>
                        <th>{{ __('cms.enquiries.email') }}</th>
                        <th>{{ __('cms.enquiries.phone') }}</th>
                        <th>{{ __('cms.enquiries.country') }}</th>
                        <th>Category</th>
                        <th>Source</th>
                        <th>Enquiries</th>
                        <th>{{ __('cms.enquiries.status') }}</th>
                        <th>{{ __('cms.enquiries.date') }}</th>
                        <th>{{ __('cms.enquiries.action') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="deleteEnquiryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('cms.enquiries.confirm_delete') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">{{ __('cms.enquiries.confirm_delete_msg') }}</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cms.enquiries.cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteEnquiry">{{ __('cms.enquiries.delete') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

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
            var filterStatus = "{{ request('status') }}";
            var filterEmail = "{{ request('email') }}";
            $('#enquiries-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.enquiries.data') }}",
                    type: 'POST',
                    data: function(d) {
                        d._token = "{{ csrf_token() }}";
                        d.status = filterStatus;
                        d.email = filterEmail;
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'company', name: 'company' },
                    { data: 'email', name: 'email' },
                    { data: 'phone', name: 'phone', orderable: false },
                    { data: 'country', name: 'country' },
                    { data: 'product', name: 'product_name', orderable: false },
                    { data: 'source_page', name: 'source_page', orderable: false },
                    { data: 'customer_enquiry_count', name: 'customer_enquiry_count', orderable: false },
                    { data: 'status_badge', name: 'status', orderable: false },
                    {
                        data: 'created_at',
                        render: function(data) {
                            if (data) {
                                // Chinese date format YYYY-MM-DD + time
                                var d = new Date(data);
                                var pad = function(n) { return String(n).padStart(2, '0'); };
                                return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate())
                                     + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
                            }
                            return '';
                        }
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [[0, 'desc']],
                pageLength: 10,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'csvHtml5',
                        text: '<i class="bi bi-file-earmark-spreadsheet"></i> CSV',
                        className: 'btn btn-sm btn-success',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                        }
                    }
                ]
            });
        });

        let enquiryToDeleteId = null;

        function deleteEnquiry(id) {
            enquiryToDeleteId = id;
            $('#deleteEnquiryModal').modal('show');
            $('#confirmDeleteEnquiry').off('click').on('click', function() {
                if (enquiryToDeleteId !== null) {
                    $.ajax({
                        url: '{{ route('admin.enquiries.destroy', ':id') }}'.replace(':id', enquiryToDeleteId),
                        method: 'DELETE',
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(response) {
                            if (response.success) {
                                $('#enquiries-table').DataTable().ajax.reload();
                                toastr.success(response.message);
                                $('#deleteEnquiryModal').modal('hide');
                            }
                        },
                        error: function() {
                            $('#deleteEnquiryModal').modal('hide');
                        }
                    });
                }
            });
        }
    </script>
@endsection
