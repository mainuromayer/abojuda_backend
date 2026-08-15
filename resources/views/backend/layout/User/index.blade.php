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
                        {{ __('Users List') }} </h1>
                    <!--end::Title-->

                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">{{ __('Home') }} </a>
                        </li>

                        <li class="breadcrumb-item text-dark">
                            {{ __('Users') }}
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
            <script src="{{ asset('backend/assets') . '/js/custom/apps/user-management/users/list/export-users.js' }}"></script>
            <script src="{{ asset('backend/assets') . '/js/custom/apps/user-management/users/list/add.js' }}"></script>
            <script src="{{ asset('backend/assets') . '/js/custom/utilities/modals/users-search.js' }}"></script>

            {{-- Include DataTables --}}
            <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

            <script>
                const isArabic = "{{ app()->getLocale() }}" === 'ar';

                $(document).ready(function () {
                    if ($.fn.DataTable.isDataTable('#kt_table_users')) {
                        $('#kt_table_users').DataTable().destroy();
                    }

                    var userTable = $('#kt_table_users').DataTable({
                        destroy: true,
                        processing: true,
                        serverSide: true,
                        language: isArabic ? {
                            "sProcessing": "جاري التحميل...",
                            "sLengthMenu": "أظهر _MENU_ مدخلات",
                            "sZeroRecords": "لم يتم العثور على أية سجلات",
                            "sEmptyTable": "لا توجد بيانات متاحة في الجدول",
                            "sInfo": "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
                            "sInfoEmpty": "يعرض ٠ إلى ٠ من أصل ٠ سجل",
                            "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
                            "sSearch": "ابحث:",
                            "oPaginate": {
                                "sFirst": "الأول",
                                "sPrevious": "السابق",
                                "sNext": "التالي",
                                "sLast": "الأخير"
                            }
                        } : {},
                        ajax: {
                            url: "{{ route('user.index') }}",
                            type: "get",
                        },
                        columns: [
                            { data: 'user', name: 'user' },
                            { data: 'role', name: 'role' },
                            { data: 'joined_date', name: 'joined_date' },
                            { data: 'reports', name: 'reports', orderable: false, searchable: false },
                            { data: 'actions', name: 'actions', orderable: false, searchable: false }
                        ],
                        drawCallback: function () {
                            if (typeof KTMenu !== 'undefined') {
                                KTMenu.createInstances();
                            }
                            if (isArabic) {
                                $('.dataTables_info, .pagination, .dataTables_length label').each(function() {
                                    var $this = $(this);
                                    $this.contents().each(function() {
                                        if (this.nodeType === 3) {
                                            this.nodeValue = this.nodeValue.replace(/[0-9]/g, function(d) {
                                                return ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'][d];
                                            });
                                        }
                                    });
                                    $this.find('option, a, span').each(function() {
                                        var node = this;
                                        for (var i = 0; i < node.childNodes.length; i++) {
                                            if (node.childNodes[i].nodeType === 3) {
                                                node.childNodes[i].nodeValue = node.childNodes[i].nodeValue.replace(/[0-9]/g, function(d) {
                                                    return ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'][d];
                                                });
                                            }
                                        }
                                    });
                                });
                            }
                        }
                    });

                    $('input[data-kt-user-table-filter="search"]').on('keyup', function () {
                        userTable.search(this.value).draw();
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
                document.getElementById('togglePassword').addEventListener('click', function () {
                    togglePasswordVisibility('passwordInput', 'eyeIcon');
                });

                // Initialize password generation for Add User
                document.getElementById('generatePassword').addEventListener('click', function () {
                    generateRandomPassword('passwordInput');
                });

                // Initialize password toggle for Edit User
                document.getElementById('edit_togglePassword').addEventListener('click', function () {
                    togglePasswordVisibility('edit_passwordInput', 'edit_eyeIcon');
                });

                // Initialize password generation for Edit User
                document.getElementById('edit_generatePassword').addEventListener('click', function () {
                    generateRandomPassword('edit_passwordInput');
                });





                // edit modal show
                $(document).on('click', '.edit-user-btn', function (e) {
                    e.preventDefault();
                    const userId = $(this).data('id');
                    $.ajax({
                        url: "{{ route('user.edit', ['id' => '__id__']) }}".replace('__id__', userId),
                        type: "GET",
                        success: function (response) {
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
                        error: function (xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to fetch user data.', 'error');
                        }
                    });
                });

                // Handle edit form submission
                $('#kt_modal_edit_user_form').on('submit', function (e) {
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
                        success: function (response) {
                            Swal.fire('Success!', response.message || 'User updated successfully', 'success');
                            $('#kt_modal_edit_user').modal('hide');
                            $('#kt_table_users').DataTable().ajax.reload();
                        },
                        error: function (xhr) {
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
                $('#kt_modal_edit_user').on('hidden.bs.modal', function () {
                    $('#kt_modal_edit_user_form')[0].reset();
                    $('#edit_avatar_preview').css('background-image', 'url({{ asset('backend/assets/media/avatars/300-1.jpg') }})');
                    $('#edit_passwordInput').val('');
                    $('#edit_role').val('');
                    $('#edit_name').val('');
                    $('#edit_province').val('');
                    $('#edit_city').val('');
                    $('#edit_address').val('');
                });

                // approve delete user
                $(document).on('click', '.approve-delete-btn', function (e) {
                    e.preventDefault();
                    let userId = $(this).data('id');

                    Swal.fire({
                        title: "{{ __('Approve Deletion?') }}",
                        text: "{{ __('Approve user account deletion request. This will delete user posts, wishes, reports and account permanently/soft-deleted.') }}",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: "{{ __('Yes, Approve!') }}",
                        cancelButtonText: "{{ __('Cancel') }}"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let approveUrl = "{{ route('user.approve-delete', ['id' => '___id___']) }}".replace('___id___', userId);
                            $.ajax({
                                url: approveUrl,
                                type: "POST",
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function (response) {
                                    Swal.fire("{{ __('Success!') }}", response.message, 'success');
                                    $('#kt_table_users').DataTable().ajax.reload();
                                },
                                error: function (xhr) {
                                    Swal.fire("{{ __('Error!') }}", xhr.responseJSON?.message || "{{ __('Something went wrong.') }}", 'error');
                                }
                            });
                        }
                    });
                });

                // reject delete user
                $(document).on('click', '.reject-delete-btn', function (e) {
                    e.preventDefault();
                    let userId = $(this).data('id');

                    Swal.fire({
                        title: "{{ __('Reject Deletion?') }}",
                        text: "{{ __('Reject user account deletion request. User status will be restored to Active.') }}",
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: "{{ __('Yes, Reject Request!') }}",
                        cancelButtonText: "{{ __('Cancel') }}"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let rejectUrl = "{{ route('user.reject-delete', ['id' => '___id___']) }}".replace('___id___', userId);
                            $.ajax({
                                url: rejectUrl,
                                type: "POST",
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function (response) {
                                    Swal.fire("{{ __('Success!') }}", response.message, 'success');
                                    $('#kt_table_users').DataTable().ajax.reload();
                                },
                                error: function (xhr) {
                                    Swal.fire("{{ __('Error!') }}", xhr.responseJSON?.message || "{{ __('Something went wrong.') }}", 'error');
                                }
                            });
                        }
                    });
                });

                // delete user
                $(document).on('click', '.delete-user-btn', function (e) {
                    e.preventDefault();
                    let userId = $(this).data('id');

                    Swal.fire({
                        title: "{{ __('Are you sure?') }}",
                        text: "{{ __('This action cannot be undone!') }}",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: "{{ __('Yes, delete it!') }}",
                        cancelButtonText: "{{ __('Cancel') }}"
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
                                success: function (response) {
                                    Swal.fire(
                                        'Deleted!',
                                        response.message,
                                        'success'
                                    );
                                    $('#kt_table_users').DataTable().ajax.reload();
                                },
                                error: function (xhr) {
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

                // Function to load and show reports
                function loadUserReports(userId, userName) {
                    $('#reported_user_title').text('Reports Against ' + userName);
                    $('#user_reports_table_body').html('<tr><td colspan="7" class="text-center text-muted py-5">Loading reports...</td></tr>');
                    $('#block_reported_user_btn').hide().attr('data-user-id', userId);
                    $('#email_reported_user_footer_btn').hide();
                    $('#mark_all_reports_read_btn').hide();
                    $('#delete_selected_reports_btn').hide();
                    $('#select_all_reports').prop('checked', false);
                    $('#kt_modal_user_reports').modal('show');

                    $.ajax({
                        url: '/admin/user/' + userId + '/reports',
                        type: 'GET',
                        success: function (res) {
                            if (res.success && res.reports.length > 0) {
                                // Show accused user name and email in modal header
                                $('#reported_user_title').text('Reports Against: ' + res.user.name + ' (' + res.user.email + ')');
                                // Setup block and email buttons in footer
                                if (res.user.status === 'suspended') {
                                    $('#block_reported_user_btn')
                                        .html('<i class="ki-duotone ki-user fs-4"><span class="path1"></span><span class="path2"></span></i> Unsuspend')
                                        .removeClass('btn-danger')
                                        .addClass('btn-success')
                                        .attr('data-action', 'unsuspend')
                                        .show();
                                } else {
                                    $('#block_reported_user_btn')
                                        .html('<i class="ki-duotone ki-user-block fs-4"><span class="path1"></span><span class="path2"></span></i> Suspend')
                                        .removeClass('btn-success')
                                        .addClass('btn-danger')
                                        .attr('data-action', 'suspend')
                                        .show();
                                }
                                $('#email_reported_user_footer_btn').data('email', res.user.email).data('name', res.user.name).show();

                                // Setup Mark all as read button
                                let hasUnread = res.reports.some(rep => !rep.is_read);
                                if (hasUnread) {
                                    $('#mark_all_reports_read_btn').show().attr('data-user-id', userId);
                                }

                                let html = '';
                                res.reports.forEach(function (rep) {
                                    let date = new Date(rep.created_at).toLocaleString();

                                    // Highlight if not read yet
                                    let isNew = !rep.is_read;
                                    let newBadge = isNew ? '<span class="badge badge-light-danger fs-9 ms-1 text-uppercase new-badge">NEW</span>' : '';
                                    let rowStyle = isNew ? 'style="background-color: rgba(241, 65, 108, 0.05);"' : '';

                                    let reporterName = rep.user ? rep.user.name : 'Anonymous';
                                    let reporterEmail = rep.user ? rep.user.email : '';
                                    let readButton = isNew ? `<button class="btn btn-icon btn-sm btn-light-success mark-report-read-btn me-1" data-id="${rep.id}" title="Mark as read"><i class="ki-duotone ki-check fs-6"></i></button>` : '';

                                    html += `
                                                <tr id="report_row_${rep.id}" ${rowStyle}>
                                                    <td>
                                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                            <input class="form-check-input select-report-checkbox" type="checkbox" data-id="${rep.id}">
                                                        </div>
                                                    </td>
                                                    <td>${reporterName} ${newBadge}</td>
                                                    <td>${reporterEmail || '-'}</td>
                                                    <td><span class="badge badge-light-danger">${rep.reason}</span></td>
                                                    <td>${rep.other_note || '-'}</td>
                                                    <td>${date}</td>
                                                    <td class="text-end">
                                                        ${readButton}
                                                        <button class="btn btn-icon btn-sm btn-light-danger delete-single-report-btn ms-1" data-id="${rep.id}" title="Delete report">
                                                            <i class="ki-duotone ki-trash fs-6"><span class="path1"></span><span class="path2"></span></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            `;
                                });
                                $('#user_reports_table_body').html(html);
                            } else {
                                $('#user_reports_table_body').html('<tr><td colspan="7" class="text-center text-muted py-5">No reports found against this user.</td></tr>');
                            }
                        },
                        error: function (xhr) {
                            let msg = xhr.responseJSON?.message || 'Failed to load reports.';
                            $('#user_reports_table_body').html('<tr><td colspan="7" class="text-center text-danger py-5">' + msg + '</td></tr>');
                        }
                    });
                }

                // Handle click on View User Reports Button
                $(document).on('click', '.view-user-reports-btn', function (e) {
                    e.preventDefault();
                    let userId = $(this).data('id');
                    let userName = $(this).data('name');
                    loadUserReports(userId, userName);
                });

                // Bind single report read
                $(document).on('click', '.mark-report-read-btn', function(e) {
                    e.preventDefault();
                    let btn = $(this);
                    let reportId = btn.data('id');
                    let row = $('#report_row_' + reportId);

                    $.ajax({
                        url: '/admin/reports/' + reportId + '/read',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            if (res.success) {
                                row.css('background-color', '');
                                row.find('.new-badge').remove();
                                btn.fadeOut(300, function() { $(this).remove(); });
                                
                                // Check if any unread reports left in the modal
                                if ($('.mark-report-read-btn').length <= 1) {
                                    $('#mark_all_reports_read_btn').fadeOut(300);
                                }
                            }
                        }
                    });
                });

                // Bind click for mark all reports read
                $(document).on('click', '#mark_all_reports_read_btn', function(e) {
                    e.preventDefault();
                    let userId = $(this).attr('data-user-id');

                    $.ajax({
                        url: '/admin/user/' + userId + '/reports/read-all',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            if (res.success) {
                                $('[id^="report_row_"]').css('background-color', '');
                                $('.new-badge').remove();
                                $('.mark-report-read-btn').fadeOut(300, function() { $(this).remove(); });
                                $('#mark_all_reports_read_btn').fadeOut(300);
                            }
                        }
                    });
                });

                // Bind email accused from footer
                $(document).on('click', '#email_reported_user_footer_btn', function(e) {
                    e.preventDefault();
                    let email = $(this).data('email');
                    let name = $(this).data('name');
                    
                    $('#admin_send_email_form')[0].reset();
                    $('#email_recipient_display').val(name + ' (' + email + ')');
                    $('#email_recipient_hidden').val(email);
                    
                    $('#kt_modal_send_custom_email').modal('show');
                });

                // Bind Suspend User Button in Modal
                $(document).on('click', '#block_reported_user_btn', function (e) {
                    e.preventDefault();
                    let userId = $(this).attr('data-user-id');
                    let action = $(this).attr('data-action');
                    
                    let titleText = action === 'unsuspend' ? "{{ __('Are you sure?') }}" : "{{ __('Are you sure?') }}";
                    let descText = action === 'unsuspend' ? "{{ __('This will restore user account access. They will be able to log in and use all features.') }}" : "{{ __('This will suspend this user account. They will not be able to log in or use the app, but their ID and profile will remain.') }}";
                    let confirmText = action === 'unsuspend' ? "{{ __('Yes, unsuspend user!') }}" : "{{ __('Yes, suspend user!') }}";
                    let ajaxUrl = action === 'unsuspend' ? "{{ route('user.unsuspend', ['id' => '___id___']) }}".replace('___id___', userId) : "{{ route('user.suspend', ['id' => '___id___']) }}".replace('___id___', userId);

                    Swal.fire({
                        title: titleText,
                        text: descText,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: action === 'unsuspend' ? '#28a745' : '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: confirmText,
                        cancelButtonText: "{{ __('Cancel') }}"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: ajaxUrl,
                                type: "POST",
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function (response) {
                                    if (response.status === 'success') {
                                        Swal.fire(action === 'unsuspend' ? "{{ __('Unsuspended!') }}" : "{{ __('Suspended!') }}", response.message, 'success');
                                        $('#kt_modal_user_reports').modal('hide');
                                        $('#kt_table_users').DataTable().ajax.reload();
                                    } else {
                                        Swal.fire("{{ __('Error!') }}", response.message, 'error');
                                    }
                                },
                                error: function (xhr) {
                                    Swal.fire("{{ __('Error!') }}", xhr.responseJSON?.message || "{{ __('Something went wrong.') }}", 'error');
                                }
                            });
                        }
                    });
                });

                // Trigger Send Email Modal
                $(document).on('click', '.send-direct-email-btn', function (e) {
                    e.preventDefault();
                    let email = $(this).data('email');
                    let name = $(this).data('name');

                    $('#admin_send_email_form')[0].reset();
                    $('#email_recipient_display').val(name + ' (' + email + ')');
                    $('#email_recipient_hidden').val(email);

                    $('#kt_modal_send_custom_email').modal('show');
                });

                // Submit Send Email Form
                $('#admin_send_email_form').on('submit', function (e) {
                    e.preventDefault();
                    let btn = $('#send_email_submit_btn');
                    btn.prop('disabled', true).text('Sending...');

                    $.ajax({
                        url: "{{ route('user.send-email') }}",
                        type: "POST",
                        data: $(this).serialize(),
                        success: function (res) {
                            if (res.success) {
                                Swal.fire('Sent!', res.message, 'success');
                                $('#kt_modal_send_custom_email').modal('hide');
                            } else {
                                Swal.fire('Failed', res.message, 'error');
                            }
                        },
                        error: function (xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to send email.', 'error');
                        },
                        complete: function () {
                            btn.prop('disabled', false).text('Send Email');
                        }
                    });
                });

                // Toggle Bulk Delete Button Visibility
                function toggleBulkDeleteButton() {
                    let checkedCount = $('.select-report-checkbox:checked').length;
                    if (checkedCount > 0) {
                        $('#delete_selected_reports_btn').show();
                    } else {
                        $('#delete_selected_reports_btn').hide();
                        $('#select_all_reports').prop('checked', false);
                    }
                }

                // Handle select all checkbox change
                $(document).on('change', '#select_all_reports', function() {
                    let checked = $(this).is(':checked');
                    $('.select-report-checkbox').prop('checked', checked);
                    toggleBulkDeleteButton();
                });

                // Handle individual checkbox change
                $(document).on('change', '.select-report-checkbox', function() {
                    toggleBulkDeleteButton();
                });

                // Delete Single Report
                $(document).on('click', '.delete-single-report-btn', function(e) {
                    e.preventDefault();
                    let reportId = $(this).data('id');
                    let row = $('#report_row_' + reportId);

                    Swal.fire({
                        title: "{{ __('Delete Report') }}",
                        text: "{{ __('Are you sure you want to delete this report?') }}",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: "{{ __('Yes, delete it!') }}",
                        cancelButtonText: "{{ __('Cancel') }}"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '/admin/reports/' + reportId,
                                type: 'DELETE',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(res) {
                                    if (res.success) {
                                        Swal.fire("{{ __('Deleted!') }}", res.message, 'success');
                                        row.fadeOut(300, function() { 
                                            $(this).remove(); 
                                            if ($('.select-report-checkbox').length === 0) {
                                                $('#user_reports_table_body').html('<tr><td colspan="7" class="text-center text-muted py-5">No reports found against this user.</td></tr>');
                                                $('#select_all_reports').prop('checked', false);
                                                $('#delete_selected_reports_btn').hide();
                                            } else {
                                                toggleBulkDeleteButton();
                                            }
                                        });
                                    }
                                }
                            });
                        }
                    });
                });

                // Delete Selected Reports (Bulk)
                $(document).on('click', '#delete_selected_reports_btn', function(e) {
                    e.preventDefault();
                    let selectedIds = [];
                    $('.select-report-checkbox:checked').each(function() {
                        selectedIds.push($(this).data('id'));
                    });

                    if (selectedIds.length === 0) return;

                    Swal.fire({
                        title: "{{ __('Delete Selected Reports') }}",
                        text: "{{ __('Are you sure you want to delete the selected reports?') }}",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: "{{ __('Yes, delete them!') }}",
                        cancelButtonText: "{{ __('Cancel') }}"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '/admin/reports/bulk-delete',
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    ids: selectedIds
                                },
                                success: function(res) {
                                    if (res.success) {
                                        Swal.fire("{{ __('Deleted!') }}", res.message, 'success');
                                        selectedIds.forEach(function(id) {
                                            $('#report_row_' + id).fadeOut(300, function() {
                                                $(this).remove();
                                                if ($('.select-report-checkbox').length === 0) {
                                                    $('#user_reports_table_body').html('<tr><td colspan="7" class="text-center text-muted py-5">No reports found against this user.</td></tr>');
                                                    $('#select_all_reports').prop('checked', false);
                                                    $('#delete_selected_reports_btn').hide();
                                                }
                                            });
                                        });
                                        $('#delete_selected_reports_btn').hide();
                                        $('#select_all_reports').prop('checked', false);
                                    }
                                }
                            });
                        }
                    });
                });

                // Bind Suspend/Unsuspend Button from Datatables Actions Row
                $(document).on('click', '.dt-suspend-btn', function (e) {
                    e.preventDefault();
                    let userId = $(this).data('id');
                    let action = $(this).data('action');
                    
                    let titleText = "{{ __('Are you sure?') }}";
                    let descText = action === 'unsuspend' ? "{{ __('This will restore user account access. They will be able to log in and use all features.') }}" : "{{ __('This will suspend this user account. They will not be able to log in or use the app, but their ID and profile will remain.') }}";
                    let confirmText = action === 'unsuspend' ? "{{ __('Yes, unsuspend user!') }}" : "{{ __('Yes, suspend user!') }}";
                    let ajaxUrl = action === 'unsuspend' ? "{{ route('user.unsuspend', ['id' => '___id___']) }}".replace('___id___', userId) : "{{ route('user.suspend', ['id' => '___id___']) }}".replace('___id___', userId);

                    Swal.fire({
                        title: titleText,
                        text: descText,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: action === 'unsuspend' ? '#28a745' : '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: confirmText,
                        cancelButtonText: "{{ __('Cancel') }}"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: ajaxUrl,
                                type: "POST",
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function (response) {
                                    if (response.status === 'success') {
                                        Swal.fire(action === 'unsuspend' ? "{{ __('Unsuspended!') }}" : "{{ __('Suspended!') }}", response.message, 'success');
                                        $('#kt_table_users').DataTable().ajax.reload();
                                    } else {
                                        Swal.fire("{{ __('Error!') }}", response.message, 'error');
                                    }
                                },
                                error: function (xhr) {
                                    Swal.fire("{{ __('Error!') }}", xhr.responseJSON?.message || "{{ __('Something went wrong.') }}", 'error');
                                }
                            });
                        }
                    });
                });

                // Auto-trigger from URL query parameter (e.g. ?reported_user_id=3)
                const urlParams = new URLSearchParams(window.location.search);
                const autoReportedUserId = urlParams.get('reported_user_id');
                if (autoReportedUserId) {
                    setTimeout(function () {
                        loadUserReports(autoReportedUserId, 'User #' + autoReportedUserId);
                    }, 500);
                }
            </script>
        @endpush

        <!--begin::Modal - View User Reports-->
        <div class="modal fade" id="kt_modal_user_reports" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="fw-bold" id="reported_user_title">Reports Against User</h2>
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                    </div>
                    <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 fs-6 gy-5">
                                <thead>
                                    <tr class="fw-bold text-muted text-uppercase gs-0">
                                        <th class="w-10px pr-0">
                                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                <input class="form-check-input" type="checkbox" id="select_all_reports">
                                            </div>
                                        </th>
                                        <th>Reporter</th>
                                        <th>Reporter Email</th>
                                        <th>Reason</th>
                                        <th>Additional Note</th>
                                        <th>Date</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="user_reports_table_body" class="text-gray-600 fw-semibold">
                                    <!-- Dynamic content -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-success btn-sm me-auto" id="mark_all_reports_read_btn" style="display: none;">
                            <i class="ki-duotone ki-double-check fs-4"><span class="path1"></span><span class="path2"></span></i> Mark all as read
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" id="delete_selected_reports_btn" style="display: none;">
                            <i class="ki-duotone ki-trash fs-4"><span class="path1"></span><span class="path2"></span></i> Delete Selected
                        </button>
                        <button type="button" class="btn btn-light-primary btn-sm" id="email_reported_user_footer_btn" style="display: none;">
                            <i class="ki-duotone ki-sms fs-4"><span class="path1"></span><span class="path2"></span></i> Send Email
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" id="block_reported_user_btn"
                            style="display: none;">
                            <i class="ki-duotone ki-user-block fs-4"><span class="path1"></span><span class="path2"></span></i>
                            Suspend User
                        </button>
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Modal - View User Reports-->

        <!--begin::Modal - Send Custom Email-->
        <div class="modal fade" id="kt_modal_send_custom_email" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="fw-bold">Send Email to User</h2>
                        <div class="btn btn-icon btn-sm btn-active-icon-primary"
                            onclick="$('#kt_modal_send_custom_email').modal('hide')">
                            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                    </div>
                    <div class="modal-body scroll-y mx-5 my-3">
                        <form id="admin_send_email_form">
                            @csrf
                            <div class="mb-5">
                                <label class="form-label fw-semibold">Recipient</label>
                                <input type="text" id="email_recipient_display" class="form-control form-control-solid"
                                    readonly>
                                <input type="hidden" name="email" id="email_recipient_hidden">
                            </div>
                            <div class="mb-5">
                                <label class="form-label fw-semibold">Subject</label>
                                <input type="text" name="subject" class="form-control form-control-solid"
                                    placeholder="Enter subject" required>
                            </div>
                            <div class="mb-5">
                                <label class="form-label fw-semibold">Message</label>
                                <textarea name="message" class="form-control form-control-solid" rows="5"
                                    placeholder="Write your email body here..." required></textarea>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-light btn-sm me-2"
                                    onclick="$('#kt_modal_send_custom_email').modal('hide')">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm" id="send_email_submit_btn">Send
                                    Email</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Modal - Send Custom Email-->
@endsection