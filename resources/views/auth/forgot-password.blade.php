{{-- <x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout> --}}



@extends('auth.app')

@section('title', __('Forgot your password'))
@section('content')
    <div class="d-flex flex-column flex-lg-row flex-column-fluid">

        <!--begin::Body-->
        <div class="content d-flex flex-column flex-lg-row-fluid py-10">
            <!--begin::Content-->
            <div class="d-flex flex-center flex-column flex-column-fluid">
                <!--begin::Wrapper-->
                <div class="w-lg-500px p-10 p-lg-15 mx-auto card">

                    <!--begin::Form-->
                    <form class="form w-100 fv-plugins-bootstrap5 fv-plugins-framework" novalidate="novalidate" method="POST" action="{{ route('password.email') }}">
                        @csrf
                        @include('backend.partials.brand_logo')
                        <!--begin::Heading-->
                        <div class="text-center mb-10">
                            <!--begin::Title-->
                            <h1 class="text-dark mb-3">
                                {{ __('Forgot Password ?') }}
                            </h1>
                            <!--end::Title-->

                            <!--begin::Link-->
                            <div class="text-gray-400 fw-semibold fs-4">
                                {{ __('Enter your email to reset your password.') }}
                            </div>
                            <!--end::Link-->
                        </div>
                        <!--begin::Heading-->

                        <!--begin::Input group-->
                        <div class="fv-row mb-10 fv-plugins-icon-container">
                            <label class="form-label fw-bold text-gray-900 fs-6">{{ __('Email') }}</label>
                            <input class="form-control form-control-solid" type="email" placeholder="" name="email"
                                autocomplete="off">
                            <div
                                class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                            </div>
                        </div>
                        <!--end::Input group-->

                        <!--begin::Actions-->
                        <div class="d-flex flex-wrap justify-content-center pb-lg-0">
                            <button type="submit" id="kt_password_reset_submit"
                                class="btn btn-lg btn-primary fw-bold me-4">
                                <span class="indicator-label">
                                    {{ __('Email Password Reset Link') }}
                                </span>
                                <span class="indicator-progress">
                                    {{ __('Please wait...') }} <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>

                            <a href="{{ route('login') }}" class="btn btn-lg btn-light-primary fw-bold">{{ __('Cancel') }}</a>
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
@endpush
