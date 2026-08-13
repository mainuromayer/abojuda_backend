@extends('backend.app')

@section('title', 'Dynamic Page')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.quilljs.com/1.3.6/quill.snow.css">
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
                        {{ __('Dynamic Pages') }} </h1>
                    <!--end::Title-->

                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">{{ __('Home') }} </a>
                        </li>

                        <li class="breadcrumb-item text-dark">
                            {{ __('Dynamic Pages') }}
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
            @include('backend.partials.dynamic_pages.index_main')
            <!--end::Post-->
        </div>
        <!--end::Content-->

        @push('script')
            <script src="{{ asset('backend/assets') . '/js/custom/apps/user-management/users/list/table.js' }}"></script>
            <script src="{{ asset('backend/assets') . '/js/custom/apps/user-management/users/list/add.js' }}"></script>
            <script src="{{ asset('backend/assets') . '/js/custom/utilities/modals/users-search.js' }}"></script>

            {{--  Include DataTables --}}
            <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

            <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

            <script>
                const isArabic = "{{ app()->getLocale() }}" === 'ar';

                $('input[data-kt-user-table-filter="search"]').on('keyup', function() {
                    $('#kt_table_users').DataTable().search(this.value).draw();
                });

                // DataTables initialization
                $(document).ready(function() {
                    $('#kt_table_users').DataTable({
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
                            url: "{{ route('dynamic-pages.index') }}",
                            type: "get",
                        },
                        columns: [
                            { data: 'title', name: 'title' },
                            { data: 'slug', name: 'slug'},
                            { data: 'actions', name: 'actions', orderable: false, searchable: false }
                        ],
                        drawCallback: function() {
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


                    // Delete Page
                    $(document).on('click', '.delete-page-btn', function(e) {
                        e.preventDefault();
                        const id = $(this).data('id');

                        Swal.fire({
                            title: 'Are you sure?',
                            text: 'This action will permanently delete the page. This cannot be undone!',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, delete it!',
                            cancelButtonText: 'Cancel',
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'btn btn-danger',
                                cancelButton: 'btn btn-secondary'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: "{{ route('dynamic-pages.delete', ['id' => '__id__']) }}".replace('__id__', id),
                                    type: 'DELETE',
                                    data: {
                                        _token: '{{ csrf_token() }}'
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            $('#kt_table_users').DataTable().ajax.reload(null, false);
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Deleted!',
                                                text: response.message,
                                                timer: 1500,
                                                showConfirmButton: false
                                            });
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error',
                                                text: response.message,
                                            });
                                        }
                                    },
                                    error: function(xhr) {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: xhr.responseJSON?.message || 'Failed to delete the page.',
                                        });
                                    }
                                });
                            }
                        });
                    });
                });
            </script>
    @endpush
@endsection
