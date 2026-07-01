@extends('backend.app')

@section('title', ' List of User')

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
                            Users
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
            @include('backend.partials.user.index_main')
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
                $('input[data-kt-user-table-filter="search"]').on('keyup', function() {
                    $('#kt_table_users').DataTable().search(this.value).draw();
                });

                $(document).ready(function() {
                    $('#kt_table_users').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ route('user.index') }}",
                            type: "get",
                        },
                        columns: [
                            // {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                            {
                                data: 'user',
                                name: 'user'
                            },
                            {
                                data: 'role',
                                name: 'role'
                            },
                            {
                                data: 'joined_date',
                                name: 'joined_date'
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




                // // Toggle password visibility
                // document.getElementById('togglePassword').addEventListener('click', function() {
                //     const passwordInput = document.getElementById('passwordInput');
                //     const eyeIcon = document.getElementById('eyeIcon');
                //     if (passwordInput.type === 'password') {
                //         passwordInput.type = 'text';
                //         eyeIcon.classList.remove('ki-eye');
                //         eyeIcon.classList.add('ki-eye-slash');
                //     } else {
                //         passwordInput.type = 'password';
                //         eyeIcon.classList.remove('ki-eye-slash');
                //         eyeIcon.classList.add('ki-eye');
                //     }
                // });
                //
                // // Generate random password
                // document.getElementById('generatePassword').addEventListener('click', function() {
                //     const passwordInput = document.getElementById('passwordInput');
                //     const chars = '0123456789abcdefghijklmnopqrstuvwxyz!@#$%^&*()ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                //     let password = '';
                //     for (let i = 0; i < 12; i++) {
                //         password += chars[Math.floor(Math.random() * chars.length)];
                //     }
                //     passwordInput.value = password;
                // });



                // Reusable function to toggle password visibility
                function togglePasswordVisibility(passwordInputId, eyeIconId) {
                    const passwordInput = document.getElementById(passwordInputId);
                    const eyeIcon = document.getElementById(eyeIconId);
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        eyeIcon.classList.remove('ki-eye');
                        eyeIcon.classList.add('ki-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        eyeIcon.classList.remove('ki-eye-slash');
                        eyeIcon.classList.add('ki-eye');
                    }
                }

                // Reusable function to generate random password
                function generateRandomPassword(passwordInputId) {
                    const passwordInput = document.getElementById(passwordInputId);
                    const chars = '0123456789abcdefghijklmnopqrstuvwxyz!@#$%^&*()ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    let password = '';
                    for (let i = 0; i < 12; i++) {
                        password += chars[Math.floor(Math.random() * chars.length)];
                    }
                    passwordInput.value = password;
                }

                // Initialize password toggle for Add User
                document.getElementById('togglePassword').addEventListener('click', function() {
                    togglePasswordVisibility('passwordInput', 'eyeIcon');
                });

                // Initialize password generation for Add User
                document.getElementById('generatePassword').addEventListener('click', function() {
                    generateRandomPassword('passwordInput');
                });

                // Initialize password toggle for Edit User
                document.getElementById('edit_togglePassword').addEventListener('click', function() {
                    togglePasswordVisibility('edit_passwordInput', 'edit_eyeIcon');
                });

                // Initialize password generation for Edit User
                document.getElementById('edit_generatePassword').addEventListener('click', function() {
                    generateRandomPassword('edit_passwordInput');
                });





                // edit modal show
                $(document).on('click', '.edit-user-btn', function(e) {
                    e.preventDefault();
                    const userId = $(this).data('id');
                    $.ajax({
                        url: "{{ route('user.edit', ['id' => '__id__']) }}".replace('__id__', userId),
                        type: "GET",
                        success: function(response) {
                            console.log(response);
                            // Update form action
                            $('#kt_modal_edit_user_form').attr('action', "{{ route('user.update', ['id' => '__id__']) }}".replace('__id__', userId));
                            $('#edit_name').val(response.user.name);
                            $('#edit_province').val(response.user.province);
                            $('#edit_city').val(response.user.city);
                            $('#edit_address').val(response.user.address);
                            $('#edit_email').val(response.user.email);
                            $('#edit_role').val(response.user.role);
                            // Set avatar preview
                            $('#edit_avatar_preview').css('background-image', 'url(' + (response.user.avatar ? response.user.avatar : "{{ asset('backend/assets/media/avatars/300-1.jpg') }}") + ')');
                            // Reset avatar remove input
                            $('input[name="avatar_remove"]').val('');
                            // Show modal
                            $('#kt_modal_edit_user').modal('show');
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to fetch user data.', 'error');
                        }
                    });
                });

                // Handle edit form submission
                $('#kt_modal_edit_user_form').on('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    formData.append('_method', 'PUT'); // Simulate PUT request
                    $.ajax({
                        url: $(this).attr('action'),
                        type: "POST", // Use POST to simulate PUT
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire('Success!', response.message || 'User updated successfully', 'success');
                            $('#kt_modal_edit_user').modal('hide');
                            $('#kt_table_users').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
                            let message = xhr.responseJSON?.message || 'Failed to update user.';
                            if (xhr.responseJSON?.errors) {
                                // Format validation errors
                                const errors = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                                message = `Validation failed:<br>${errors}`;
                            }
                            Swal.fire({
                                title: 'Error!',
                                html: message,
                                icon: 'error'
                            });
                        }
                    });
                });

                // Reset edit modal on close
                $('#kt_modal_edit_user').on('hidden.bs.modal', function() {
                    $('#kt_modal_edit_user_form')[0].reset();
                    $('#edit_avatar_preview').css('background-image', 'url({{ asset('backend/assets/media/avatars/300-1.jpg') }})');
                    $('#edit_passwordInput').val('');
                    $('#edit_role').val('');
                    $('#edit_name').val('');
                    $('#edit_province').val('');
                    $('#edit_city').val('');
                    $('#edit_address').val('');
                });






                // delete user
                $(document).on('click', '.delete-user-btn', function(e) {
                    e.preventDefault();
                    let userId = $(this).data('id');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This action cannot be undone!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let deleteUrl = "{{ route('user.delete', ['id' => '___id___']) }}".replace('___id___',
                                userId);
                            $.ajax({
                                url: deleteUrl,
                                type: "DELETE",
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    Swal.fire(
                                        'Deleted!',
                                        response.message,
                                        'success'
                                    );
                                    $('#kt_table_users').DataTable().ajax.reload();
                                },
                                error: function(xhr) {
                                    Swal.fire(
                                        'Error!',
                                        xhr.responseJSON?.message || 'Something went wrong.',
                                        'error'
                                    );
                                }
                            });
                        }
                    });
                });
            </script>
        @endpush
    @endsection
