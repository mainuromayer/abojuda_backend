
@extends('backend.app')

@section('title', __('Dynamic Page Create'))

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css">
    <style>

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
                        {{ __('Dynamic Page') }} </h1>
                    <!--end::Title-->

                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">{{ __('Home') }} </a>
                        </li>

                        <li class="breadcrumb-item text-dark">
                            {{ __('Dynamic Page Create') }}
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
            @include('backend.partials.dynamic_pages.create_main')
            <!--end::Post-->
        </div>
        <!--end::Content-->

        @push('script')
            <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
            <script>
                // DataTables initialization
                $(document).ready(function() {

                    // Initialize Summernote editor
                    $('#kt_add_page_content').summernote({
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

                    // Add Page
                    $('#add-page-form').on('submit', function(e) {
                        e.preventDefault();
                        var formData = new FormData(this);

                        $.ajax({
                            url: '{{ route('dynamic-pages.store') }}',
                            type: 'POST',
                            data: formData,
                            contentType: false,
                            processData: false,
                            success: function(response) {
                                if (response.success) {
                                    toastr.success(response.message);
                                    setTimeout(function() {
                                        window.location.href = "{{ route('dynamic-pages.index') }}";
                                    }, 1000);
                                } else {
                                    toastr.error(response.message);
                                }
                            },
                            error: function(xhr) {
                                let errors = xhr.responseJSON?.errors || {};
                                let errorMsg = '';
                                $.each(errors, function(key, value) {
                                    errorMsg += value[0] + '\n';
                                });
                                toastr.error(errorMsg || xhr.responseJSON?.message || 'Something went wrong!');
                            }
                        });
                    });


                });
            </script>
    @endpush
@endsection
