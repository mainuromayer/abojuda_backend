@extends('backend.app')

@section('title', __('Dynamic Page Edit'))

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css">
@endpush

@section('content')
    <!--begin::Content-->
    <div class="content fs-6 d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Toolbar-->
        <div class="toolbar" id="kt_toolbar">
            <div class="container-fluid d-flex flex-stack flex-wrap flex-sm-nowrap">
                <!--begin::Info-->
                <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                    <!--begin::Title-->
                    <h1 class="text-dark fw-bold my-1 fs-2">{{ __('Dynamic Page') }}</h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">{{ __('Home') }}</a>
                        </li>
                        <li class="breadcrumb-item text-dark">{{ __('Dynamic Page Edit') }}</li>
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
            @include('backend.partials.dynamic_pages.edit_main')
            <!--end::Container-->
        </div>
        <!--end::Post-->
    </div>
    <!--end::Content-->

    @push('script')
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
        <script>
            $(document).ready(function () {
                // Initialize Summernote editor
                $('#kt_edit_page_content').summernote({
                    placeholder: @json(__('Type your content here...')),
                    tabsize: 2,
                    height: 300,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'underline', 'clear']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture', 'video']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ]
                });

                // Edit Page Form Submission
                $('#edit-page-form').on('submit', function (e) {
                    e.preventDefault();

                    // Sync Summernote content to textarea
                    $('#kt_edit_page_content').val($('#kt_edit_page_content').summernote('code'));

                    var formData = new FormData(this);

                    $.ajax({
                        url: '{{ route('dynamic-pages.update', $page->id) }}',
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.success) {
                                toastr.success(response.message);
                                setTimeout(function() {
                                    window.location.href = "{{ route('dynamic-pages.index') }}";
                                }, 1000);
                            } else {
                                toastr.error(response.message || 'Update failed.');
                            }
                        },
                        error: function (xhr) {
                            let errors = xhr.responseJSON?.errors || {};
                            let errorMsg = xhr.responseJSON?.message || 'An error occurred.';
                            if (Object.keys(errors).length > 0) {
                                errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += `${value[0]}\n`;
                                });
                            }
                            toastr.error(errorMsg);
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
