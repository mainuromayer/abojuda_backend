@extends('backend.app')

@section('title', 'Role & Permission')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <style>
        .dataTables_filter {
            display: none !important;
        }
    </style>
@endpush

@section('content')
    <!--begin::Content-->
    <div class="content fs-6 d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Toolbar-->
        <div class="toolbar" id="kt_toolbar">
            <div class=" container-fluid  d-flex flex-stack flex-wrap flex-sm-nowrap">
                <!--begin::Info-->
                <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                    <!--begin::Title-->
                    <h1 class="text-dark fw-bold my-1 fs-2">
                        List of User </h1>
                    <!--end::Title-->

                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home </a>
                        </li>

                        <li class="breadcrumb-item text-dark">
                            Role
                        </li>

                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Info-->

            </div>
        </div>
        <!--end::Toolbar-->

        <!--begin::Post-->
        <div class="post fs-6 d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            @include('backend.partials.Permisson.index_main')
            <!--end::Post-->
        </div>
        <!--end::Content-->

        @push('script')
            <script src="{{ asset('backend/assets') . '/js/custom/apps/user-management/users/list/table.js' }}"></script>
            <script src="{{ asset('backend/assets') . '/js/custom/apps/user-management/users/list/export-users.js' }}"></script>
            <script src="{{ asset('backend/assets') . '/js/custom/apps/user-management/users/list/add.js' }}"></script>
            <script src="{{ asset('backend/assets') . '/js/custom/utilities/modals/users-search.js' }}"></script>

            {{--  Include DataTables --}}
            <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

            <script>
                // search
                $('input[data-kt-user-table-filter="search"]').on('keyup', function() {
                    $('#kt_table_users').DataTable().search(this.value).draw();
                });

                // fetch data
                $(document).ready(function() {
                    $('#kt_table_users').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ route('user.roles.index') }}",
                            type: "get",
                        },
                        columns: [
                            // {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                            {
                                data: 'user_email',
                                name: 'user_email'
                            },
                            {
                                data: 'roles',
                                name: 'roles'
                            },
                            {
                                data: 'permissions',
                                name: 'permissions'
                            },
                            {
                                data: 'actions',
                                name: 'actions',
                                orderable: false,
                                searchable: false
                            }
                        ],
                        drawCallback: function() {
                            if (typeof KTMenu !== 'undefined') {
                                KTMenu.createInstances();
                            }
                        }
                    });
                });



                // Store userId when opening modal
                $(document).on('click', '.manage-role', function () {
                    window.userId = $(this).data('user'); // Store userId globally
                });

                // Attach role
                $('#attach-role').click(function () {
                    let roleId = $('#role-select').val();
                    if (roleId) {
                        $.ajax({
                            url: "{{ route('user.attach.role', ':id') }}".replace(':id', window.userId),
                            method: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                role: roleId
                            },
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire("{{ __('Success!') }}", response.message, 'success');
                                    $('#role-modal').modal('hide'); // Use Bootstrap's method to hide modal
                                    // Refresh the DataTable
                                    $('#kt_table_users').DataTable().ajax.reload();
                                } else {
                                    Swal.fire("{{ __('Error!') }}", response.message, 'error');
                                }
                            },
                            error: function () {
                                Swal.fire("{{ __('Error!') }}", "{{ __('Something went wrong, please try again.') }}", 'error');
                            }
                        });
                    } else {
                        Swal.fire("{{ __('Error!') }}", "{{ __('Please select a role.') }}", 'error');
                    }
                });

                // Detach role
                $(document).on('click', '.remove-role', function () {
                    let userId = $(this).data('user');
                    let roleId = $(this).data('role');

                    Swal.fire({
                        title: "{{ __('Are you sure?') }}",
                        text: "{{ __('This will remove the role from the user!') }}",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: "{{ __('Yes, remove it!') }}",
                        cancelButtonText: "{{ __('Cancel') }}"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('user.detach.role', ':id') }}".replace(':id', userId),
                                method: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    role: roleId
                                },
                                success: function (response) {
                                    if (response.success) {
                                        Swal.fire("{{ __('Removed!') }}", response.message, 'success');
                                        // Refresh the DataTable
                                        $('#kt_table_users').DataTable().ajax.reload();
                                    } else {
                                        Swal.fire("{{ __('Error!') }}", response.message, 'error');
                                    }
                                },
                                error: function () {
                                    Swal.fire("{{ __('Error!') }}", "{{ __('Something went wrong, please try again.') }}", 'error');
                                }
                            });
                        }
                    });
            </script>
    @endpush
@endsection





