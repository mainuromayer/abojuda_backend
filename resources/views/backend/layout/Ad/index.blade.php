@extends('backend.app')

@section('title', 'Ad Management')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .dataTables_filter {
            display: none !important;
        }
        .dropify-wrapper .dropify-message p {
            font-size: 14px;
        }
    </style>
@endpush

@section('content')
    <!--begin::Content-->
    <div class="content fs-6 d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Toolbar-->
        <div class="toolbar" id="kt_toolbar">
            <div class=" container-fluid d-flex flex-stack flex-wrap flex-sm-nowrap">
                <!--begin::Info-->
                <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                    <!--begin::Title-->
                    <h1 class="text-dark fw-bold my-1 fs-2">{{ __('Ad Management') }}</h1>
                    <!--end::Title-->

                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">{{ __('Home') }}</a>
                        </li>
                        <li class="breadcrumb-item text-dark">{{ __('Ads') }}</li>
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
            @include('backend.partials.ad.index_main')
            <!--end::Post-->
        </div>
    </div>
    <!--end::Content-->

    @push('script')
        {{-- Include DataTables --}}
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

        <script>
            const isArabic = "{{ app()->getLocale() }}" === 'ar';

            $(document).ready(function() {
                var blankImage = "{{ asset('backend/assets/media/svg/files/blank-image.svg') }}";

                // Function to toggle between Image and Video media fields
                function toggleMediaGroup(type) {
                    if (type === 'video') {
                        $('#image_media_group').addClass('d-none');
                        $('#video_media_group').removeClass('d-none');
                    } else {
                        $('#video_media_group').addClass('d-none');
                        $('#image_media_group').removeClass('d-none');
                    }
                }

                // Media type dropdown change
                $('#ad_media_type').on('change', function() {
                    toggleMediaGroup($(this).val());
                });

                // Video input file change preview
                $('#ad_media_video').on('change', function(e) {
                    var file = e.target.files[0];
                    if (file) {
                        var videoUrl = URL.createObjectURL(file);
                        $('#ad_video_preview').attr('src', videoUrl).removeClass('d-none');
                        $('#ad_video_placeholder').addClass('d-none');
                        $('#media_remove').val('0');
                    }
                });

                // Remove image icon button
                $(document).on('click', '#remove_ad_image_btn', function(e) {
                    e.preventDefault();
                    $('#media_remove').val('1');
                    $('#ad_image_wrapper').css('background-image', 'url(' + blankImage + ')');
                    $('#ad_image_input').removeClass('image-input-changed').addClass('image-input-empty');
                    $('#ad_media_image').val('');
                });

                // Remove video button
                $(document).on('click', '#remove_ad_video_btn', function(e) {
                    e.preventDefault();
                    $('#media_remove').val('1');
                    $('#ad_video_preview').attr('src', '').addClass('d-none');
                    $('#ad_video_placeholder').removeClass('d-none');
                    $('#ad_media_video').val('');
                });

                // Initialize DataTable
                var table = $('#kt_table_ads').DataTable({
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
                        url: "{{ route('ad.index') }}",
                        type: "GET",
                    },
                    columns: [
                        { data: 'media', name: 'media', orderable: false, searchable: false },
                        { data: 'title', name: 'title' },
                        { data: 'media_type', name: 'media_type' },
                        { data: 'back_link', name: 'back_link' },
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

                // Custom search input
                $('input[data-kt-ad-table-filter="search"]').on('keyup', function() {
                    table.search(this.value).draw();
                });

                // Open modal for adding a new ad
                $('#open_add_ad_modal_btn').on('click', function() {
                    $('#kt_modal_add_ad_form')[0].reset();
                    $('#ad_id').val('');
                    $('#media_remove').val('0');

                    $('#ad_media_type').val('image');
                    toggleMediaGroup('image');

                    $('#ad_image_wrapper').css('background-image', 'url(' + blankImage + ')');
                    $('#ad_image_input').removeClass('image-input-changed').addClass('image-input-empty');
                    $('#ad_video_preview').attr('src', '').addClass('d-none');
                    $('#ad_video_placeholder').removeClass('d-none');

                    $('#ad_modal_title').text("{{ __('Add New Ad') }}");
                    $('#ad_submit_btn').find('.indicator-label').text("{{ __('Submit') }}");
                    $('#kt_modal_add_ad').modal('show');
                });

                // Open modal for editing an ad
                $(document).on('click', '.edit-ad-btn', function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');

                    // Fetch ad data
                    $.ajax({
                        url: "{{ url('/admin/ads') }}/" + id + "/edit",
                        type: "GET",
                        success: function(response) {
                            var ad = response.ad;
                            $('#ad_id').val(ad.id);
                            $('#ad_title').val(ad.title);
                            $('#ad_media_type').val(ad.media_type || 'image');
                            $('#ad_back_link').val(ad.back_link);
                            $('#ad_status').val(ad.status);
                            $('#media_remove').val('0');

                            toggleMediaGroup(ad.media_type || 'image');

                            if (ad.media_type === 'video') {
                                if (ad.media_url) {
                                    $('#ad_video_preview').attr('src', ad.media_url).removeClass('d-none');
                                    $('#ad_video_placeholder').addClass('d-none');
                                } else {
                                    $('#ad_video_preview').attr('src', '').addClass('d-none');
                                    $('#ad_video_placeholder').removeClass('d-none');
                                }
                            } else {
                                if (ad.media_url) {
                                    $('#ad_image_wrapper').css('background-image', 'url(' + ad.media_url + ')');
                                    $('#ad_image_input').addClass('image-input-changed').removeClass('image-input-empty');
                                } else {
                                    $('#ad_image_wrapper').css('background-image', 'url(' + blankImage + ')');
                                    $('#ad_image_input').removeClass('image-input-changed').addClass('image-input-empty');
                                }
                            }

                            $('#ad_modal_title').text("{{ __('Edit Ad') }}");
                            $('#ad_submit_btn').find('.indicator-label').text("{{ __('Save Changes') }}");
                            $('#kt_modal_add_ad').modal('show');
                        },
                        error: function(xhr) {
                            toastr.error("{{ __('Failed to fetch ad data.') }}");
                        }
                    });
                });

                // Handle form submission (Create and Update)
                $('#kt_modal_add_ad_form').on('submit', function(e) {
                    e.preventDefault();
                    var id = $('#ad_id').val();
                    var url = id ? "{{ url('/admin/ads') }}/" + id + "/update" : "{{ route('ad.store') }}";
                    var formData = new FormData(this);

                    // Show loading indicator
                    $('#ad_submit_btn').attr('data-kt-indicator', 'on').prop('disabled', true);

                    $.ajax({
                        url: url,
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                $('#kt_modal_add_ad').modal('hide');
                                $('#kt_modal_add_ad_form')[0].reset();
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
                                toastr.error(xhr.responseJSON?.message || "{{ __('Failed to submit form.') }}");
                            }
                        },
                        complete: function() {
                            // Hide loading indicator
                            $('#ad_submit_btn').removeAttr('data-kt-indicator').prop('disabled', false);
                        }
                    });
                });

                // Handle ad deletion
                $(document).on('click', '.delete-ad-btn', function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');

                    Swal.fire({
                        text: "{{ __('Are you sure you want to delete this ad?') }}",
                        icon: "warning",
                        showCancelButton: true,
                        buttonsStyling: false,
                        confirmButtonText: "{{ __('Yes, delete!') }}",
                        cancelButtonText: "{{ __('No, cancel') }}",
                        customClass: {
                            confirmButton: "btn fw-bold btn-danger",
                            cancelButton: "btn fw-bold btn-active-light-primary"
                        }
                    }).then(function (result) {
                        if (result.value) {
                            $.ajax({
                                url: "{{ url('/admin/ads') }}/" + id + "/delete",
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
                                    toastr.error(xhr.responseJSON?.message || 'Failed to delete ad.');
                                }
                            });
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
