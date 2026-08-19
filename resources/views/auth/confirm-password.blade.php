@extends('auth.app')

@section('title', __('Confirm Password'))
@section('content')
    <div class="d-flex flex-column flex-lg-row flex-column-fluid">

        <!--begin::Body-->
        <div class="content d-flex flex-column flex-lg-row-fluid py-10">
            <!--begin::Content-->
            <div class="d-flex flex-center flex-column flex-column-fluid">
                <!--begin::Wrapper-->
                <div class="w-lg-500px p-10 p-lg-15 mx-auto card">

                    <!--begin::Form-->
                    <form class="form w-100 fv-plugins-bootstrap5 fv-plugins-framework" novalidate="novalidate" method="POST" action="{{ route('password.confirm') }}">
                        @csrf
                        @include('backend.partials.brand_logo')
                        <!--begin::Heading-->
                        <div class="text-center mb-10">
                            <!--begin::Title-->
                            <h1 class="text-dark mb-3">
                                {{ __('Confirm Password') }}
                            </h1>
                            <!--end::Title-->

                            <!--begin::Link-->
                            <div class="text-gray-400 fw-semibold fs-4">
                                {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
                            </div>
                            <!--end::Link-->
                        </div>
                        <!--begin::Heading-->

                        <!--begin::Input group-->
                        <div class="fv-row mb-10 fv-plugins-icon-container">
                            <label class="form-label fw-bold text-gray-900 fs-6">{{ __('Password') }}</label>
                            <input class="form-control form-control-solid" type="password" name="password" required autocomplete="current-password">
                        </div>
                        <!--end::Input group-->

                        <!--begin::Actions-->
                        <div class="d-flex flex-wrap justify-content-center pb-lg-0">
                            <button type="submit" class="btn btn-lg btn-primary fw-bold me-4">
                                {{ __('Confirm') }}
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
