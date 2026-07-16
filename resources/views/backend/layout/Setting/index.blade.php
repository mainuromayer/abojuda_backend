@extends('backend.app')

@section('title', 'Account Setting')

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
                        Account Setting </h1>
                    <!--end::Title-->

                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home </a>
                        </li>

                        <li class="breadcrumb-item text-dark">
                            Account Setting
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
            <div class=" container-xxl ">
                <!--begin::Basic info-->
                <div class="card mb-5 mb-xl-10">
                    <!--begin::Card header-->
                    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_settings_profile_details" aria-expanded="true" aria-controls="kt_account_settings_profile_details">
                        <!--begin::Card title-->
                        <div class="card-title m-0">
                            <h3 class="fw-bold m-0">Account Details</h3>
                        </div>
                        <!--end::Card title-->
                    </div>
                    <!--begin::Card header-->

                    <!--begin::Content-->
                    <div id="kt_account_settings_profile_details" class="collapse show">
                        <!--begin::Form-->
                        <form action="{{ route('setting.store') }}" method="POST" enctype="multipart/form-data" class="form fv-plugins-bootstrap5 fv-plugins-framework" novalidate="novalidate">
                            @csrf
                            <!--begin::Card body-->
                            <div class="card-body border-top p-9">

                                <!-- First Row: Title & Copyright -->
                                <div class="d-flex flex-column flex-md-row gap-5 mb-6">
                                    <!-- Title -->
                                    <div class="fv-row flex-row-fluid">
                                        <label class="required form-label">System Title</label>
                                        <input class="form-control form-control-solid" type="text" name="title" placeholder="Enter system title" value="{{ old('title', $setting->system_title ?? '') }}">
                                    </div>

                                    <!-- Copyright -->
                                    <div class="fv-row flex-row-fluid">
                                        <label class="required form-label">Copyright Text</label>
                                        <input class="form-control form-control-solid" type="text" name="copyright" placeholder="Enter copyright" value="{{ old('copyright', $setting->copyright_text ?? '') }}">
                                    </div>
                                </div>


                                <!-- 2nd Row: logo & favicon -->
                                <div class="d-flex flex-column flex-md-row gap-5 mb-6">
                                    <x-backend.setting.image-upload
                                        title="Logo"
                                        name="logo"
                                        :imageUrl="$setting?->logo"
                                        description="Set the logo image. Only *.png, *.jpg and *.jpeg image files are accepted"
                                    />

                                    <x-backend.setting.image-upload
                                        title="Favicon"
                                        name="favicon"
                                        :imageUrl="$setting?->favicon"
                                        description="Set the Favicon image. Only *.png, *.jpg and *.jpeg image files are accepted"
                                    />
                                </div>


                            </div>
                            <!--end::Card body-->

                            <!--begin::Actions-->
                            <div class="card-footer d-flex justify-content-end py-6 px-9">
                                <button type="reset" class="btn btn-light btn-active-light-primary me-2">Discard</button>
                                <button type="submit" class="btn btn-primary" id="kt_account_profile_details_submit">Save Changes</button>
                            </div>
                            <!--end::Actions-->
                            <input type="hidden"></form>
                        <!--end::Form-->
                    </div>

                    <!--end::Content-->
                </div>
                <!--end::Basic info-->

                <!--end::Container-->
            </div>
            <!--end::Post-->
        </div>
        <!--end::Content-->

        @push('script')

    @endpush
@endsection
