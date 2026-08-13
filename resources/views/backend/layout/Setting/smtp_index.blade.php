@extends('backend.app')

@section('title', __('SMTP Details'))

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
                        {{ __('SMTP Details') }} </h1>
                    <!--end::Title-->

                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">{{ __('Home') }} </a>
                        </li>

                        <li class="breadcrumb-item text-dark">
                            {{ __('SMTP Details') }}
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
                            <h3 class="fw-bold m-0">{{ __('SMTP Details') }}</h3>
                        </div>
                        <!--end::Card title-->
                    </div>
                    <!--begin::Card header-->

                    <!--begin::Content-->
                 @include('backend.partials.setting.smtp_content')
                    <!--end::Content-->
                </div>
                <!--end::Basic info-->

                <!--end::Container-->
            </div>
            <!--end::Post-->
        </div>
        <!--end::Content-->

    @push('script')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const togglePassword = document.getElementById('togglePassword');
                    const passwordField = document.getElementById('mail_password');
                    const passwordIcon = document.getElementById('passwordIcon');

                    togglePassword.addEventListener('click', function () {
                        if (passwordField.type === 'password') {
                            passwordField.type = 'text';
                            passwordIcon.classList.remove('fa-eye');
                            passwordIcon.classList.add('fa-eye-slash');
                        } else {
                            passwordField.type = 'password';
                            passwordIcon.classList.remove('fa-eye-slash');
                            passwordIcon.classList.add('fa-eye');
                        }
                    });
                });
            </script>
    @endpush
@endsection
