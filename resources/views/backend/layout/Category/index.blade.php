@extends('backend.app')

@section('title', 'List of Categories')

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
                    <h1 class="text-dark fw-bold my-1 fs-2">List of Categories</h1>
                    <!--end::Title-->

                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                        </li>
                        <li class="breadcrumb-item text-dark">Categories</li>
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
            @include('backend.partials.category.index_main')
            <!--end::Post-->
        </div>
    </div>
    <!--end::Content-->

    @push('script')
        {{-- Include DataTables --}}
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

        <script>
            $(document).ready(function() {
                // Initialize DataTable
                var table = $('#kt_table_categories').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('category.index') }}",
                        type: "GET",
                    },
                    columns: [
                        { data: 'icon', name: 'icon', orderable: false, searchable: false },
                        { data: 'name', name: 'name' },
                        { data: 'description', name: 'description' },
                        { data: 'status', name: 'status' },
                        { data: 'created_at', name: 'created_at' },
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

                // Custom search input
                $('input[data-kt-category-table-filter="search"]').on('keyup', function() {
                    table.search(this.value).draw();
                });

                // Open modal for adding a new category
                $('#open_add_category_modal_btn').on('click', function() {
                    $('#kt_modal_add_category_form')[0].reset();
                    $('#category_id').val('');
                    $('#category_icon').val('');
                    $('#icon_remove').val('0');
                    var blankImage = "{{ asset('backend/assets/media/svg/files/blank-image.svg') }}";
                    $('#category_icon_wrapper').css('background-image', 'url(' + blankImage + ')');
                    $('#category_icon_image_input').removeClass('image-input-changed').addClass('image-input-empty');
                    $('#category_modal_title').text('Add Category');
                    $('#category_submit_btn').find('.indicator-label').text('Submit');
                    $('#kt_modal_add_category').modal('show');
                });

                // Open modal for editing a category
                $(document).on('click', '.edit-category-btn', function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');

                    // Fetch category data
                    $.ajax({
                        url: "{{ url('/admin/category') }}/" + id + "/edit",
                        type: "GET",
                        success: function(response) {
                            var category = response.category;
                            $('#category_id').val(category.id);
                            $('#category_name').val(category.name);
                            $('#category_description').val(category.shot_desc);
                            $('#category_status').val(category.status);
                            $('#category_icon').val('');
                            $('#icon_remove').val('0');

                            if (category.icon_url) {
                                $('#category_icon_wrapper').css('background-image', 'url(' + category.icon_url + ')');
                                $('#category_icon_image_input').addClass('image-input-changed').removeClass('image-input-empty');
                            } else {
                                var blankImage = "{{ asset('backend/assets/media/svg/files/blank-image.svg') }}";
                                $('#category_icon_wrapper').css('background-image', 'url(' + blankImage + ')');
                                $('#category_icon_image_input').removeClass('image-input-changed').addClass('image-input-empty');
                            }

                            $('#category_modal_title').text('Edit Category');
                            $('#category_submit_btn').find('.indicator-label').text('Save Changes');
                            $('#kt_modal_add_category').modal('show');
                        },
                        error: function(xhr) {
                            toastr.error('Failed to fetch category data.');
                        }
                    });
                });

                // Remove category icon preview
                $(document).on('click', '#remove_category_icon_btn', function(e) {
                    e.preventDefault();
                    $('#icon_remove').val('1');
                    var blankImage = "{{ asset('backend/assets/media/svg/files/blank-image.svg') }}";
                    $('#category_icon_wrapper').css('background-image', 'url(' + blankImage + ')');
                    $('#category_icon_image_input').removeClass('image-input-changed').addClass('image-input-empty');
                    $('#category_icon').val('');
                });

                // Handle form submission (Create and Update)
                $('#kt_modal_add_category_form').on('submit', function(e) {
                    e.preventDefault();
                    var id = $('#category_id').val();
                    var url = id ? "{{ url('/admin/category/update') }}/" + id : "{{ route('category.store') }}";
                    var formData = new FormData(this);

                    // Show loading indicator
                    $('#category_submit_btn').attr('data-kt-indicator', 'on').prop('disabled', true);

                    $.ajax({
                        url: url,
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                $('#kt_modal_add_category').modal('hide');
                                $('#kt_modal_add_category_form')[0].reset();
                                toastr.success(response.message);
                                table.ajax.reload();
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function(xhr) {
                            // Display validation errors
                            if (xhr.status === 422) {
                                var errors = xhr.responseJSON.errors;
                                $.each(errors, function(key, val) {
                                    toastr.error(val[0]);
                                });
                            } else {
                                toastr.error(xhr.responseJSON?.message || 'Failed to submit form.');
                            }
                        },
                        complete: function() {
                            // Hide loading indicator
                            $('#category_submit_btn').removeAttr('data-kt-indicator').prop('disabled', false);
                        }
                    });
                });

                // Handle category deletion
                $(document).on('click', '.delete-category-btn', function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');

                    Swal.fire({
                        text: "Are you sure you want to delete this category?",
                        icon: "warning",
                        showCancelButton: true,
                        buttonsStyling: false,
                        confirmButtonText: "Yes, delete!",
                        cancelButtonText: "No, cancel",
                        customClass: {
                            confirmButton: "btn fw-bold btn-danger",
                            cancelButton: "btn fw-bold btn-active-light-primary"
                        }
                    }).then(function (result) {
                        if (result.value) {
                            $.ajax({
                                url: "{{ url('/admin/category') }}/" + id + "/delete",
                                type: "DELETE",
                                data: {
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(response) {
                                    if (response.success) {
                                        toastr.success(response.message);
                                        table.ajax.reload();
                                    } else {
                                        toastr.error(response.message);
                                    }
                                },
                                error: function(xhr) {
                                    toastr.error(xhr.responseJSON?.message || 'Failed to delete category.');
                                }
                            });
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
