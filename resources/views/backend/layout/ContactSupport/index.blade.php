@extends('backend.app')

@section('title', __('Contact Requests'))

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .dataTables_filter {
            display: none !important;
        }
        #support_details_loader.d-none {
            display: none !important;
        }
        /* Ensure action button icons always turn pure white on hover */
        .btn.btn-light-primary:hover i,
        .btn.btn-light-primary:hover .fa-solid,
        .btn.btn-light-primary:focus i,
        .btn.btn-light-primary.active i {
            color: #ffffff !important;
        }
        .btn.btn-light-info:hover i,
        .btn.btn-light-info:hover .fa-solid,
        .btn.btn-light-info:focus i,
        .btn.btn-light-info.active i {
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
                    <h1 class="text-dark fw-bold my-1 fs-2">{{ __('Contact & Support Requests') }}</h1>
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">{{ __('Home') }}</a>
                        </li>
                        <li class="breadcrumb-item text-dark">{{ __('Contact Requests') }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <!--end::Toolbar-->

        <!--begin::Post-->
        <div class="post fs-6 d-flex flex-column-fluid" id="kt_post">
            <div class="container-fluid">
                <!--begin::Card-->
                <div class="card card-flush shadow-sm">
                    <!--begin::Card header-->
                    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                        <div class="card-title">
                            <!--begin::Search-->
                            <div class="d-flex align-items-center position-relative my-1">
                                <i class="ki-duotone ki-magnifier fs-2 position-absolute ms-4">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <input type="text" id="contact_search_input"
                                    class="form-control form-control-solid w-250px ps-12"
                                    placeholder="{{ __('Search email, subject, issue...') }}" />
                            </div>
                            <!--end::Search-->
                        </div>
                        <div class="card-toolbar">
                            <span class="badge badge-light-primary fs-7 fw-bold px-3 py-2">
                                {{ __('Total Requests:') }} <span id="total_supports_badge">{{ $totalSupports }}</span>
                            </span>
                        </div>
                    </div>
                    <!--end::Card header-->

                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_contact_supports">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th class="w-50px">#</th>
                                        <th class="min-w-150px">{{ __('Email') }}</th>
                                        <th class="min-w-150px">{{ __('Subject') }}</th>
                                        <th class="min-w-200px">{{ __('Issue Description') }}</th>
                                        <th class="min-w-100px">{{ __('Attachment') }}</th>
                                        <th class="min-w-120px">{{ __('Date') }}</th>
                                        <th class="text-end min-w-100px">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
        </div>
        <!--end::Post-->
    </div>

    <!--begin::Modal - View Contact Support Details-->
    <div class="modal fade" id="modal_view_support" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold d-flex align-items-center gap-2">
                        <i class="ki-duotone ki-message-text-2 fs-1 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        {{ __('Contact Request Details') }}
                    </h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body py-lg-10 px-lg-10" id="support_details_content">
                    <div class="text-center py-10" id="support_details_loader">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="support_details_body" style="display: none;">
                        <div class="mb-5 p-4 rounded bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <span class="text-muted fs-8 text-uppercase fw-bold">{{ __('From') }}</span>
                                    <div class="fs-6 fw-bold text-gray-800" id="view_support_email"></div>
                                </div>
                                <div class="text-end">
                                    <span class="text-muted fs-8 text-uppercase fw-bold">{{ __('Date & Time') }}</span>
                                    <div class="fs-7 text-gray-600" id="view_support_date"></div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="text-muted fs-8 text-uppercase fw-bold">{{ __('Subject') }}</span>
                                <div class="fs-6 fw-semibold text-primary" id="view_support_subject"></div>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label fw-bold text-gray-700 fs-7 text-uppercase">{{ __('Issue Description') }}</label>
                            <div class="p-4 border rounded bg-white text-gray-800 fs-6" id="view_support_issue" style="white-space: pre-wrap; line-height: 1.6; max-height: 250px; overflow-y: auto;">
                            </div>
                        </div>

                        <div class="mb-5" id="view_support_attachment_container">
                            <label class="form-label fw-bold text-gray-700 fs-7 text-uppercase">{{ __('Attachment') }}</label>
                            <div id="view_support_attachment_content"></div>
                        </div>

                        <div class="separator my-5"></div>

                        <!-- Quick Reply Section Inside View Modal -->
                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
                            <button type="button" class="btn btn-primary" id="open_reply_from_view_btn">
                                <i class="ki-duotone ki-directbox-default fs-4 me-1"></i> {{ __('Reply to User') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal - View Contact Support Details-->

    <!--begin::Modal - Reply to Contact Support-->
    <div class="modal fade" id="modal_reply_support" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-600px">
            <div class="modal-content">
                <form id="reply_support_form">
                    @csrf
                    <input type="hidden" id="reply_support_id" name="support_id">
                    <div class="modal-header">
                        <h2 class="fw-bold">{{ __('Send Email Reply') }}</h2>
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                    </div>
                    <div class="modal-body py-lg-10 px-lg-10">
                        <div class="mb-5">
                            <label class="form-label fw-semibold required">{{ __('Recipient Email') }}</label>
                            <input type="email" id="reply_recipient_email" class="form-control form-control-solid" readonly />
                        </div>
                        <div class="mb-5">
                            <label class="form-label fw-semibold required">{{ __('Subject') }}</label>
                            <input type="text" id="reply_subject" name="subject" class="form-control" required />
                        </div>
                        <div class="mb-5">
                            <label class="form-label fw-semibold required">{{ __('Message') }}</label>
                            <textarea id="reply_message" name="message" class="form-control" rows="6" placeholder="{{ __('Type your reply message here...') }}" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary" id="send_reply_btn">
                            <span class="indicator-label">{{ __('Send Reply') }}</span>
                            <span class="indicator-progress" style="display: none;">{{ __('Sending...') }} <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--end::Modal - Reply to Contact Support-->
@endsection

@push('script')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            var isArabic = "{{ app()->getLocale() }}" === 'ar';
            var currentSupportData = null;

            var table = $('#kt_table_contact_supports').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('contact-support.index', [], false) }}",
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: isArabic ? "بحث..." : "Search...",
                    lengthMenu: isArabic ? "عرض _MENU_ سجلات" : "Show _MENU_ entries",
                    info: isArabic ? "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل" : "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: isArabic ? "لا توجد سجلات متاحة" : "No records available",
                    infoFiltered: isArabic ? "(تمت تصفيته من إجمالي _MAX_ مدخل)" : "(filtered from _MAX_ total entries)",
                    zeroRecords: isArabic ? "لم يتم العثور على سجلات مطابقة" : "No matching records found",
                    paginate: {
                        next: isArabic ? "التالي" : "Next",
                        previous: isArabic ? "السابق" : "Previous"
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'email', name: 'email' },
                    { data: 'subject', name: 'subject' },
                    { data: 'describe_issue', name: 'describe_issue' },
                    { data: 'attachment', name: 'attachment', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
                ],
                order: [[5, 'desc']]
            });

            $('#contact_search_input').on('keyup', function() {
                table.search(this.value).draw();
            });

            // View Details Function
            function openSupportDetails(id) {
                if (!id) return;
                $('#support_details_loader').removeClass('d-none').show();
                $('#support_details_body').hide();

                var modalEl = document.getElementById('modal_view_support');
                if (modalEl) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        var modalObj = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modalObj.show();
                    } else if ($.fn.modal) {
                        $('#modal_view_support').modal('show');
                    }
                }

                $.ajax({
                    url: "{{ route('contact-support.index', [], false) }}/" + id,
                    type: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function(res) {
                        $('#support_details_loader').addClass('d-none').hide();
                        if (res.success && res.data) {
                            currentSupportData = res.data;
                            $('#view_support_email').html('<a href="mailto:' + res.data.email + '" class="text-primary">' + res.data.email + '</a>');
                            $('#view_support_date').text(res.data.created_at);
                            $('#view_support_subject').text(res.data.subject || 'No Subject');
                            $('#view_support_issue').text(res.data.describe_issue);

                            if (res.data.attachment) {
                                $('#view_support_attachment_container').show();
                                var isImg = typeof res.data.attachment_raw === 'string' && res.data.attachment_raw.match(/\.(jpeg|jpg|png|gif|webp)$/i);
                                if (isImg) {
                                    $('#view_support_attachment_content').html(
                                        '<a href="' + res.data.attachment + '" target="_blank" title="' + (isArabic ? 'عرض الصورة' : 'View Image') + '">' +
                                        '<img src="' + res.data.attachment + '" class="img-thumbnail rounded shadow-sm" style="max-height: 220px;" alt="Attachment" />' +
                                        '</a>'
                                    );
                                } else {
                                    $('#view_support_attachment_content').html(
                                        '<a href="' + res.data.attachment + '" target="_blank" class="badge badge-light-primary text-hover-primary fs-7 p-3">' +
                                        '<i class="ki-duotone ki-file fs-5 me-1 text-primary"><span class="path1"></span><span class="path2"></span></i> ' + (isArabic ? 'فتح المرفق' : 'Open Attachment') +
                                        '</a>'
                                    );
                                }
                            } else {
                                $('#view_support_attachment_container').hide();
                            }

                            $('#support_details_body').show();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: res.message || 'Could not load details'
                            });
                        }
                    },
                    error: function(err) {
                        $('#support_details_loader').addClass('d-none').hide();
                        var msg = (err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : (isArabic ? 'فشل تحميل التفاصيل' : 'Failed to fetch details.');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });
                    }
                });
            }

            $('#modal_view_support').on('hidden.bs.modal', function() {
                $('#support_details_loader').addClass('d-none').hide();
                $('#support_details_body').hide();
            });

            // Expose globally for header notification quick opening
            window.openSupportDetails = openSupportDetails;

            $(document).on('click', '.view-support-btn', function() {
                var id = $(this).data('id');
                openSupportDetails(id);
            });

            // Open Reply Modal
            function openReplyModal(id, email, subject) {
                $('#reply_support_id').val(id);
                $('#reply_recipient_email').val(email);
                var subText = subject ? 'Re: ' + subject : 'Response to your support request';
                $('#reply_subject').val(subText);
                $('#reply_message').val('');

                var replyModalEl = document.getElementById('modal_reply_support');
                if (replyModalEl) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        var modalObj = bootstrap.Modal.getOrCreateInstance(replyModalEl);
                        modalObj.show();
                    } else if ($.fn.modal) {
                        $('#modal_reply_support').modal('show');
                    }
                }
            }

            $(document).on('click', '.reply-support-btn', function() {
                var id = $(this).data('id');
                var email = $(this).data('email');
                var subject = $(this).data('subject');
                openReplyModal(id, email, subject);
            });

            $('#open_reply_from_view_btn').on('click', function() {
                if (currentSupportData) {
                    $('#modal_view_support').modal('hide');
                    openReplyModal(currentSupportData.id, currentSupportData.email, currentSupportData.subject);
                }
            });

            // Submit Reply
            $('#reply_support_form').on('submit', function(e) {
                e.preventDefault();
                var id = $('#reply_support_id').val();
                var subject = $('#reply_subject').val();
                var message = $('#reply_message').val();

                $('#send_reply_btn .indicator-label').hide();
                $('#send_reply_btn .indicator-progress').show();
                $('#send_reply_btn').prop('disabled', true);

                $.ajax({
                    url: "{{ route('contact-support.index', [], false) }}/" + id + "/reply",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        subject: subject,
                        message: message
                    },
                    success: function(res) {
                        $('#send_reply_btn .indicator-label').show();
                        $('#send_reply_btn .indicator-progress').hide();
                        $('#send_reply_btn').prop('disabled', false);

                        if (res.success) {
                            $('#modal_reply_support').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: isArabic ? 'تم الإرسال بنجاح' : 'Success',
                                text: res.message
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: res.message
                            });
                        }
                    },
                    error: function(err) {
                        $('#send_reply_btn .indicator-label').show();
                        $('#send_reply_btn .indicator-progress').hide();
                        $('#send_reply_btn').prop('disabled', false);

                        var msg = (err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Failed to send reply.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });
                    }
                });
            });

            // Delete Request
            $(document).on('click', '.delete-support-btn', function() {
                var id = $(this).data('id');

                Swal.fire({
                    title: isArabic ? 'هل أنت متأكد؟' : 'Are you sure?',
                    text: isArabic ? 'لن تتمكن من استرجاع هذا الطلب!' : "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: isArabic ? 'نعم، احذف' : 'Yes, delete it!',
                    cancelButtonText: isArabic ? 'إلغاء' : 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('contact-support.index', [], false) }}/" + id,
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

            // If arrived via Notification click with ?id=X, auto-open details modal!
            var targetId = "{{ $targetId ?? '' }}";
            if (!targetId) {
                var urlParams = new URLSearchParams(window.location.search);
                targetId = urlParams.get('id');
            }
            if (targetId) {
                setTimeout(function() {
                    openSupportDetails(targetId);
                }, 350);
            }
        });
    </script>
@endpush
