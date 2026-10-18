@extends('backend.app')

@section('title', __('Announcements'))

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .dataTables_filter {
            display: none !important;
        }
        /* Ensure action button icons always turn pure white on hover */
        .btn.btn-light-primary:hover i,
        .btn.btn-light-primary:hover .fa-solid,
        .btn.btn-light-primary:focus i,
        .btn.btn-light-primary.active i {
            color: #ffffff !important;
        }
        .btn.btn-light-danger:hover i,
        .btn.btn-light-danger:hover .fa-solid,
        .btn.btn-light-danger:focus i,
        .btn.btn-light-danger.active i {
            color: #ffffff !important;
        }
    </style>
@endpush

@section('content')
    <div class="content fs-6 d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Toolbar-->
        <div class="toolbar" id="kt_toolbar">
            <div class="container-fluid d-flex flex-stack flex-wrap flex-sm-nowrap">
                <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                    <h1 class="text-dark fw-bold my-1 fs-2">{{ __('Announcements') }}</h1>
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">{{ __('Home') }}</a>
                        </li>
                        <li class="breadcrumb-item text-dark">{{ __('Announcements') }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <!--end::Toolbar-->

        <!--begin::Post-->
        <div class="post fs-6 d-flex flex-column-fluid" id="kt_post">
            <div class="container-fluid">
                <div class="row g-5 g-xl-8">
                    <!--begin::Left Col: Compose Form-->
                    <div class="col-xl-5">
                        <div class="card card-flush shadow-sm mb-5">
                            <div class="card-header border-0 pt-6">
                                <div class="card-title">
                                    <h3 class="fw-bold d-flex align-items-center gap-2">
                                        <i class="ki-duotone ki-notification-on fs-1 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                        {{ __('Send Announcement') }}
                                    </h3>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <!--begin::Reachable Info Banner-->
                                <div class="alert bg-light-primary d-flex align-items-center justify-content-between p-3 mb-5">
                                    <div class="d-flex align-items-center">
                                        <i class="ki-duotone ki-notification-status fs-2 text-primary me-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                        <div class="fs-7 text-gray-800 fw-semibold">
                                            {{ __('Audience:') }}
                                            <span class="badge badge-primary ms-1 me-2">{{ $reachableUsersCount ?? 0 }} {{ __('Users') }}</span>
                                            <span class="badge badge-light-primary border border-primary fs-8">
                                                <i class="ki-duotone ki-devices fs-8 me-1 text-primary"></i>{{ $reachableDevicesCount ?? 0 }} {{ __('Push Devices') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Reachable Info Banner-->

                                <form id="send_announcement_form">
                                    @csrf
                                    <!--begin::Title Field-->
                                    <div class="mb-5">
                                        <label class="form-label required fw-bold text-gray-700">{{ __('Title') }}</label>
                                        <input type="text" name="title" id="announcement_title" class="form-control form-control-solid"
                                            placeholder="{{ __('Title') }}" maxlength="255" required />
                                    </div>
                                    <!--end::Title Field-->

                                    <!--begin::Description Field-->
                                    <div class="mb-5">
                                        <label class="form-label required fw-bold text-gray-700">{{ __('Description') }}</label>
                                        <textarea name="description" id="announcement_description" rows="5" class="form-control form-control-solid"
                                            placeholder="{{ __('Description') }}" required></textarea>
                                    </div>
                                    <!--end::Description Field-->

                                    <!--begin::Submit Button-->
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" id="btn_send_announcement" class="btn btn-primary w-100">
                                            <span class="indicator-label">
                                                <i class="ki-duotone ki-send fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                                {{ __('Send Notification') }}
                                            </span>
                                            <span class="indicator-progress" style="display: none;">
                                                {{ __('Sending...') }} <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
                                        </button>
                                    </div>
                                    <!--end::Submit Button-->
                                </form>
                            </div>
                        </div>
                    </div>
                    <!--end::Left Col: Compose Form-->

                    <!--begin::Right Col: Sent History-->
                    <div class="col-xl-7">
                        <div class="card card-flush shadow-sm">
                            <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                                <div class="card-title">
                                    <h3 class="fw-bold">{{ __('Announcement History') }}</h3>
                                </div>
                                <div class="card-toolbar">
                                    <div class="d-flex align-items-center position-relative my-1">
                                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-3">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                        <input type="text" id="announcement_search_input"
                                            class="form-control form-control-solid form-control-sm w-175px ps-9"
                                            placeholder="{{ __('Search...') }}" />
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-7 gy-4" id="kt_table_announcements">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-8 text-uppercase gs-0">
                                                <th class="w-30px">#</th>
                                                <th class="min-w-140px">{{ __('Title') }}</th>
                                                <th class="min-w-160px">{{ __('Description') }}</th>
                                                <th class="min-w-80px">{{ __('Sent To') }}</th>
                                                <th class="min-w-100px">{{ __('Date') }}</th>
                                                <th class="text-end min-w-70px">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Right Col: Sent History-->
                </div>
            </div>
        </div>
        <!--end::Post-->
    </div>

    <!--begin::Modal - View Announcement Details-->
    <div class="modal fade" id="modal_view_announcement" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-550px">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="fw-bold d-flex align-items-center gap-2">
                        <i class="ki-duotone ki-notification-on fs-2 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                        {{ __('Announcement Details') }}
                    </h3>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body py-6 px-8">
                    <div class="mb-4">
                        <span class="text-muted fs-8 text-uppercase fw-bold">{{ __('Title') }}</span>
                        <h4 class="fw-bold text-gray-800 mt-1" id="modal_announcement_title"></h4>
                    </div>

                    <div class="d-flex justify-content-between p-3 rounded bg-light mb-5">
                        <div>
                            <span class="text-muted fs-8 text-uppercase fw-bold">{{ __('Sent To') }}</span>
                            <div class="fw-bold text-primary" id="modal_announcement_recipients"></div>
                        </div>
                        <div class="text-end">
                            <span class="text-muted fs-8 text-uppercase fw-bold">{{ __('Sent At') }}</span>
                            <div class="text-gray-700 fs-8" id="modal_announcement_date"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-gray-700 fs-8 text-uppercase">{{ __('Full Message / Description') }}</label>
                        <div class="p-4 border rounded bg-white text-gray-800 fs-6" id="modal_announcement_desc" style="white-space: pre-wrap; line-height: 1.6; max-height: 250px; overflow-y: auto;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-3">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal - View Announcement Details-->
@endsection

@push('script')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            var isArabic = "{{ app()->getLocale() }}" === 'ar';

            var table = $('#kt_table_announcements').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('announcement.index', [], false) }}",
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: isArabic ? "بحث..." : "Search...",
                    lengthMenu: isArabic ? "عرض _MENU_ سجلات" : "Show _MENU_ entries",
                    info: isArabic ? "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل" : "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: isArabic ? "لا توجد سجلات متاحة" : "No records available",
                    zeroRecords: isArabic ? "لم يتم العثور على سجلات مطابقة" : "No matching records found",
                    paginate: {
                        next: isArabic ? "التالي" : "Next",
                        previous: isArabic ? "السابق" : "Previous"
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'title', name: 'title' },
                    { data: 'description', name: 'description' },
                    { data: 'recipients_count', name: 'recipients_count', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
                ],
                order: [[4, 'desc']]
            });

            $('#announcement_search_input').on('keyup', function() {
                table.search(this.value).draw();
            });

            // Submit Send Announcement Form
            $('#send_announcement_form').on('submit', function(e) {
                e.preventDefault();

                var title = $('#announcement_title').val().trim();
                var description = $('#announcement_description').val().trim();

                if (!title || !description) {
                    Swal.fire({
                        icon: 'warning',
                        title: isArabic ? 'تنبيه' : 'Warning',
                        text: isArabic ? 'يرجى ملء جميع الحقول المطلوبة' : 'Please fill all required fields.'
                    });
                    return;
                }

                Swal.fire({
                    title: isArabic ? 'هل تريد إرسال الإشعار؟' : 'Send Notification?',
                    text: isArabic ? 'سيتم إرسال هذا الإشعار لجميع المستخدمين.' : 'Broadcast to all users?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: isArabic ? 'إرسال' : 'Send',
                    cancelButtonText: isArabic ? 'إلغاء' : 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#btn_send_announcement .indicator-label').hide();
                        $('#btn_send_announcement .indicator-progress').show();
                        $('#btn_send_announcement').prop('disabled', true);

                        $.ajax({
                            url: "{{ route('announcement.send', [], false) }}",
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                title: title,
                                description: description
                            },
                            success: function(res) {
                                $('#btn_send_announcement .indicator-label').show();
                                $('#btn_send_announcement .indicator-progress').hide();
                                $('#btn_send_announcement').prop('disabled', false);

                                if (res.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: isArabic ? 'تم الإرسال بنجاح' : 'Sent Successfully!',
                                        text: res.message
                                    });
                                    $('#announcement_title').val('');
                                    $('#announcement_description').val('');
                                    table.ajax.reload(null, false);
                                } else {
                                    Swal.fire('Error', res.message, 'error');
                                }
                            },
                            error: function(err) {
                                $('#btn_send_announcement .indicator-label').show();
                                $('#btn_send_announcement .indicator-progress').hide();
                                $('#btn_send_announcement').prop('disabled', false);

                                var msg = (err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Failed to send notification.';
                                Swal.fire('Error', msg, 'error');
                            }
                        });
                    }
                });
            });

            // View Details Modal
            $(document).on('click', '.view-announcement-btn', function() {
                var title = $(this).data('title');
                var desc = $(this).data('desc');
                var recipients = $(this).data('recipients');
                var date = $(this).data('date');

                $('#modal_announcement_title').text(title);
                $('#modal_announcement_desc').text(desc);
                $('#modal_announcement_recipients').text(recipients + ' ' + (isArabic ? 'مستخدم' : 'Users'));
                $('#modal_announcement_date').text(date);

                $('#modal_view_announcement').modal('show');
            });

            // Delete Announcement Record
            $(document).on('click', '.delete-announcement-btn', function() {
                var id = $(this).data('id');

                Swal.fire({
                    title: isArabic ? 'هل أنت متأكد؟' : 'Are you sure?',
                    text: isArabic ? 'سيتم حذف هذا السجل من تاريخ الإشعارات.' : 'This record will be deleted from history.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: isArabic ? 'نعم، احذف' : 'Yes, delete it!',
                    cancelButtonText: isArabic ? 'إلغاء' : 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('announcement.index', [], false) }}/" + id,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                if (res.success) {
                                    Swal.fire(
                                        isArabic ? 'تم الحذف!' : 'Deleted!',
                                        res.message,
                                        'success'
                                    );
                                    table.ajax.reload(null, false);
                                } else {
                                    Swal.fire('Error', res.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'Failed to delete record.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
