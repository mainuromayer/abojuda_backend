@extends('backend.app')

@section('title', 'Post Management')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .dataTables_filter { display: none !important; }
    </style>
@endpush

@section('content')
<div class="content fs-6 d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="toolbar" id="kt_toolbar">
        <div class="container-fluid d-flex flex-stack flex-wrap flex-sm-nowrap">
            <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                <h1 class="text-dark fw-bold my-1 fs-2">{{ __('Post Management') }}</h1>
                <ul class="breadcrumb fw-semibold fs-base my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">{{ __('Home') }}</a>
                    </li>
                    <li class="breadcrumb-item text-dark">{{ __('Posts') }}</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="post fs-6 d-flex flex-column-fluid" id="kt_post">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="ki-duotone ki-magnifier fs-3 position-absolute {{ app()->getLocale() == 'ar' ? 'me-5 end-0' : 'ms-5 start-0' }}">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            <input type="text" data-kt-post-table-filter="search"
                                class="form-control form-control-solid w-100 w-sm-250px {{ app()->getLocale() == 'ar' ? 'pe-13 ps-5' : 'ps-13 pe-5' }}"
                                placeholder="{{ __('Search posts...') }}" />
                        </div>
                    </div>
                    <div class="card-toolbar">
                        <select id="category_filter" class="form-select form-select-solid w-150px me-3">
                            <option value="">{{ __('All Categories') }}</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ __($cat->name) }}</option>
                            @endforeach
                        </select>
                        <select id="status_filter" class="form-select form-select-solid w-150px me-3">
                            <option value="">{{ __('All Status') }}</option>
                            <option value="pending">{{ __('Pending') }}</option>
                            <option value="active">{{ __('Active') }}</option>
                            <option value="rejected">{{ __('Rejected') }}</option>
                        </select>
                    </div>
                </div>
                <div class="card-body py-4">
                    <style>
                        .table tr.bg-light-warning td {
                            background-color: #fff8dd !important;
                        }
                    </style>
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_posts">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th>#</th>
                                <th>{{ __('Thumbnail') }}</th>
                                <th>{{ __('Title / Category') }}</th>
                                <th>{{ __('User') }}</th>
                                <th>{{ __('Price') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Featured') }}</th>
                                <th>{{ __('Reports') }}</th>
                                <th>{{ __('Created At') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
const isArabic = "{{ app()->getLocale() }}" === 'ar';

$(document).ready(function () {
    var table = $('#kt_table_posts').DataTable({
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
            url: "{{ route('post.index') }}",
            type: "GET",
            data: function(d) {
                d.status = $('#status_filter').val();
                d.category_id = $('#category_filter').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'thumbnail', name: 'thumbnail', orderable: false, searchable: false },
            { data: 'title', name: 'title' },
            { data: 'user', name: 'user' },
            { data: 'price', name: 'price' },
            { data: 'status', name: 'status' },
            { data: 'featured', name: 'featured', orderable: false, searchable: false },
            { data: 'reports', name: 'reports', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        drawCallback: function () {
            if (typeof KTMenu !== 'undefined') KTMenu.createInstances();
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

    $('input[data-kt-post-table-filter="search"]').on('keyup', function () {
        table.search(this.value).draw();
    });

    $('#status_filter, #category_filter').on('change', function () {
        table.ajax.reload();
    });

    // Toggle featured
    $(document).on('change', '.toggle-featured', function () {
        var id = $(this).data('id');
        var featured = $(this).is(':checked');
        $.ajax({
            url: '/admin/posts/' + id + '/featured',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', featured: featured ? 1 : 0 },
            success: function (res) {
                if (res.success) toastr.success(res.message);
                else toastr.error(res.message);
            }
        });
    });

    // Approve post
    $(document).on('click', '.approve-post-btn', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        Swal.fire({
            text: "{{ __('Approve this post?') }}",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: "{{ __('Yes, approve!') }}",
            cancelButtonText: "{{ __('Cancel') }}",
            buttonsStyling: false,
            customClass: { confirmButton: 'btn btn-success', cancelButton: 'btn btn-light ms-2' }
        }).then(function (result) {
            if (result.value) {
                $.post('/admin/posts/' + id + '/approve', { _token: '{{ csrf_token() }}' }, function (res) {
                    if (res.success) { toastr.success(res.message); table.ajax.reload(); }
                    else toastr.error(res.message);
                });
            }
        });
    });

    // Reject post
    $(document).on('click', '.reject-post-btn', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        Swal.fire({
            text: "{{ __('Reject this post?') }}",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: "{{ __('Yes, reject!') }}",
            cancelButtonText: "{{ __('Cancel') }}",
            buttonsStyling: false,
            customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light ms-2' }
        }).then(function (result) {
            if (result.value) {
                $.post('/admin/posts/' + id + '/reject', { _token: '{{ csrf_token() }}' }, function (res) {
                    if (res.success) { toastr.success(res.message); table.ajax.reload(); }
                    else toastr.error(res.message);
                });
            }
        });
    });

    // Delete post
    $(document).on('click', '.delete-post-btn', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        Swal.fire({
            text: "{{ __('Are you sure you want to delete this post?') }}",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: "{{ __('Yes, delete!') }}",
            cancelButtonText: "{{ __('No, cancel') }}",
            buttonsStyling: false,
            customClass: { confirmButton: 'btn fw-bold btn-danger', cancelButton: 'btn fw-bold btn-active-light-primary' }
        }).then(function (result) {
            if (result.value) {
                $.ajax({
                    url: '/admin/posts/' + id + '/delete',
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        if (res.success) { toastr.success(res.message); table.ajax.reload(); }
                        else toastr.error(res.message);
                    }
                });
            }
        });
    });

    // Function to load and show post reports
    function loadPostReports(postId, postTitle) {
        $('#reported_post_title').text('Reports Against ' + postTitle);
        $('#post_reports_table_body').html('<tr><td colspan="7" class="text-center text-muted py-5">Loading reports...</td></tr>');
        $('#reject_reported_post_btn').hide().attr('data-post-id', postId);
        $('#mark_all_post_reports_read_btn').hide();
        $('#delete_selected_post_reports_btn').hide();
        $('#select_all_post_reports').prop('checked', false);
        $('#kt_modal_post_reports').modal('show');

        $.ajax({
            url: '/admin/posts/' + postId + '/reports',
            type: 'GET',
            success: function (res) {
                if (res.success && res.reports.length > 0) {
                    $('#reported_post_title').text('Reports Against: ' + res.post.title + ' (' + res.post.status + ')');
                    if (res.post.status !== 'rejected') {
                        $('#reject_reported_post_btn').show();
                    }

                    let hasUnread = res.reports.some(rep => !rep.is_read);
                    if (hasUnread) {
                        $('#mark_all_post_reports_read_btn').show().attr('data-post-id', postId);
                    }

                    let html = '';
                    res.reports.forEach(function (rep) {
                        let date = new Date(rep.created_at).toLocaleString();
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
                    $('#post_reports_table_body').html(html);
                } else {
                    $('#post_reports_table_body').html('<tr><td colspan="7" class="text-center text-muted py-5">No reports found against this listing.</td></tr>');
                }
            },
            error: function (xhr) {
                let msg = xhr.responseJSON?.message || 'Failed to load reports.';
                $('#post_reports_table_body').html('<tr><td colspan="7" class="text-center text-danger py-5">' + msg + '</td></tr>');
            }
        });
    }

    // Bind click to open reports modal
    $(document).on('click', '.view-post-reports-btn', function (e) {
        e.preventDefault();
        loadPostReports($(this).data('id'), $(this).data('title'));
    });

    // Toggle bulk delete button visibility
    function toggleBulkDeleteButton() {
        let checkedCount = $('.select-report-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#delete_selected_post_reports_btn').show();
        } else {
            $('#delete_selected_post_reports_btn').hide();
            $('#select_all_post_reports').prop('checked', false);
        }
    }

    // Select all post reports
    $(document).on('change', '#select_all_post_reports', function() {
        let checked = $(this).is(':checked');
        $('.select-report-checkbox').prop('checked', checked);
        toggleBulkDeleteButton();
    });

    $(document).on('change', '.select-report-checkbox', function() {
        toggleBulkDeleteButton();
    });

    // Mark single report as read
    $(document).on('click', '.mark-report-read-btn', function (e) {
        e.preventDefault();
        let reportId = $(this).data('id');
        let btn = $(this);
        let row = $('#report_row_' + reportId);

        $.post('/admin/reports/' + reportId + '/read', { _token: '{{ csrf_token() }}' }, function (res) {
            if (res.success) {
                row.css('background-color', '').find('.new-badge').remove();
                btn.fadeOut(300, function() { $(this).remove(); });
                // Check if any unread left
                let hasUnread = false;
                $('.mark-report-read-btn').each(function() {
                    if ($(this).data('id') !== reportId) hasUnread = true;
                });
                if (!hasUnread) $('#mark_all_post_reports_read_btn').fadeOut(300);
            }
        });
    });

    // Mark all post reports as read
    $(document).on('click', '#mark_all_post_reports_read_btn', function (e) {
        e.preventDefault();
        let postId = $(this).attr('data-post-id');
        $.post('/admin/posts/' + postId + '/reports/read-all', { _token: '{{ csrf_token() }}' }, function (res) {
            if (res.success) {
                toastr.success(res.message);
                $('#mark_all_post_reports_read_btn').fadeOut(300);
                $('.mark-report-read-btn').fadeOut(300, function() { $(this).remove(); });
                $('.new-badge').remove();
                $('tr[id^="report_row_"]').css('background-color', '');
            }
        });
    });

    // Delete single report
    $(document).on('click', '.delete-single-report-btn', function (e) {
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
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire("{{ __('Deleted!') }}", res.message, 'success');
                            row.fadeOut(300, function() {
                                $(this).remove();
                                if ($('.select-report-checkbox').length === 0) {
                                    $('#post_reports_table_body').html('<tr><td colspan="7" class="text-center text-muted py-5">No reports found against this listing.</td></tr>');
                                    $('#select_all_post_reports').prop('checked', false);
                                    $('#delete_selected_post_reports_btn').hide();
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

    // Bulk Delete Selected Reports
    $(document).on('click', '#delete_selected_post_reports_btn', function (e) {
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
                    data: { _token: '{{ csrf_token() }}', ids: selectedIds },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire("{{ __('Deleted!') }}", res.message, 'success');
                            selectedIds.forEach(function(id) {
                                $('#report_row_' + id).fadeOut(300, function() {
                                    $(this).remove();
                                    if ($('.select-report-checkbox').length === 0) {
                                        $('#post_reports_table_body').html('<tr><td colspan="7" class="text-center text-muted py-5">No reports found against this listing.</td></tr>');
                                        $('#select_all_post_reports').prop('checked', false);
                                        $('#delete_selected_post_reports_btn').hide();
                                    }
                                });
                            });
                            $('#delete_selected_post_reports_btn').hide();
                            $('#select_all_post_reports').prop('checked', false);
                        }
                    }
                });
            }
        });
    });

    // Reject Post Action from Modal
    $(document).on('click', '#reject_reported_post_btn', function (e) {
        e.preventDefault();
        let postId = $(this).attr('data-post-id');

        Swal.fire({
            text: "{{ __('Are you sure you want to reject this post?') }}",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: "{{ __('Yes, reject!') }}",
            cancelButtonText: "{{ __('Cancel') }}",
            buttonsStyling: false,
            customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light ms-2' }
        }).then(function (result) {
            if (result.value) {
                $.post('/admin/posts/' + postId + '/reject', { _token: '{{ csrf_token() }}' }, function (res) {
                    if (res.success) {
                        toastr.success(res.message);
                        $('#kt_modal_post_reports').modal('hide');
                        table.ajax.reload();
                    } else toastr.error(res.message);
                });
            }
        });
    });

    // Auto-trigger from URL query parameter (e.g. ?reported_post_id=3)
    const urlParams = new URLSearchParams(window.location.search);
    const autoReportedPostId = urlParams.get('reported_post_id');
    if (autoReportedPostId) {
        setTimeout(function () {
            loadPostReports(autoReportedPostId, 'Listing #' + autoReportedPostId);
        }, 500);
    }
});
</script>
@endpush

@section('modal')
    <!--begin::Modal - View Post Reports-->
    <div class="modal fade" id="kt_modal_post_reports" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold" id="reported_post_title">Reports Against Post</h2>
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
                                            <input class="form-check-input" type="checkbox" id="select_all_post_reports">
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
                            <tbody id="post_reports_table_body" class="text-gray-600 fw-semibold">
                                <!-- Dynamic content -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-success btn-sm me-auto" id="mark_all_post_reports_read_btn" style="display: none;">
                        <i class="ki-duotone ki-double-check fs-4"><span class="path1"></span><span class="path2"></span></i> Mark all as read
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="delete_selected_post_reports_btn" style="display: none;">
                        <i class="ki-duotone ki-trash fs-4"><span class="path1"></span><span class="path2"></span></i> Delete Selected
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="reject_reported_post_btn" style="display: none;">
                        <i class="ki-duotone ki-cross-circle fs-4"><span class="path1"></span><span class="path2"></span></i> Reject Post
                    </button>
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal - View Post Reports-->
@endsection
@endsection
