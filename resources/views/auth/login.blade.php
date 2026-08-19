@extends('auth.app')

@section('title', __('Login'))
@section('content')
    <div class="d-flex flex-column flex-lg-row flex-column-fluid">

        <!--begin::Body-->
        <div class="content d-flex flex-column flex-lg-row-fluid py-10">
            <!--begin::Content-->
            <div class="d-flex flex-center flex-column flex-column-fluid">
                <!--begin::Wrapper-->
                <div class="w-lg-500px p-10 p-lg-15 mx-auto card">

                    <!--begin::Form-->
                    <form class="form w-100 fv-plugins-bootstrap5 fv-plugins-framework" novalidate="novalidate"
                        id="kt_sign_in_form" method="POST" action="{{ route('login') }}">
                        @csrf
                        <!--begin::Heading-->
                        <div class="text-center mb-10">
                            <!-- <a href="#" class="mb-5 d-block">
                                <img alt="Logo" src="{{ URL::to('./.png') }}"
                                    class="h-100px h-lg-150px" />
                            </a> -->
                            @include('backend.partials.brand_logo')

                            <!--begin::Title-->
                            <h1 class="text-dark mb-3">
                                {{ __('Sign In to Dashboard') }} </h1>
                            <!--end::Title-->
                        </div>
                        <!--begin::Heading-->

                        <!--begin::Input group-->
                        <div class="fv-row mb-10 fv-plugins-icon-container">
                            <!--begin::Label-->
                            <label class="form-label fs-6 fw-bold text-dark">{{ __('Email') }}</label>
                            <!--end::Label-->

                            <!--begin::Input-->
                            <input class="form-control form-control-lg form-control-solid" type="text" name="email" id="email"
                                autocomplete="off" value="{{ old('email') }}" required autofocus>
                            <!--end::Input-->
                            <div
                                class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                            </div>
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="fv-row mb-10 fv-plugins-icon-container">
                            <!--begin::Wrapper-->
                            <div class="d-flex flex-stack mb-2">
                                <!--begin::Label-->
                                <label class="form-label fw-bold text-dark fs-6 mb-0">{{ __('Password') }}</label>
                                <!--end::Label-->

                                <!--begin::Link-->
                                <a href="{{ route('password.request') }}" class="link-primary fs-6 fw-bold">
                                    {{ __('Forgot Password ?') }}
                                </a>
                                <!--end::Link-->
                            </div>
                            <!--end::Wrapper-->

                            <!--begin::Input-->
                            <div class="input-group">
                                <input class="form-control form-control-lg form-control-solid" type="password" name="password" id="password"
                                    autocomplete="off" required style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                <button class="btn btn-solid btn-active-light-primary border" type="button" id="togglePassword" style="border-top-left-radius: 0; border-bottom-left-radius: 0; background-color: #f1f1f4;">
                                    <i class="fa fa-eye-slash fs-3" id="toggleIcon"></i>
                                </button>
                            </div>
                            <!--end::Input-->
                            <div
                                class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                            </div>
                        </div>
                        <!--end::Input group-->

                        <!--begin::Actions-->
                        <div class="text-center">
                            <!--begin::Submit button-->
                            <button type="submit" class="btn btn-lg btn-primary w-100 mb-5">
                                <span class="indicator-label">
                                    {{ __('Continue') }}
                                </span>

                                <span class="indicator-progress">
                                    {{ __('Please wait...') }} <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                            <!--end::Submit button-->

                            <!--begin::Quick credentials-->
                            <div class="mb-5">
                                <button type="button" class="btn btn-light-success btn-sm w-100 fw-bold" id="btnQuickAdmin">
                                    {{ __('Use Admin Credentials') }}
                                </button>
                            </div>
                            <!--end::Quick credentials-->

                        </div>
                        <!--end::Actions-->
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Content-->

        </div>
        <!--end::Body-->
    </div>
@endsection
@push('scripts')
<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        const passwordInput = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    });

    document.getElementById('btnQuickAdmin').addEventListener('click', function () {
        document.getElementById('email').value = 'admin@admin.com';
        document.getElementById('password').value = '12345678';
    });
</script>
@endpush
