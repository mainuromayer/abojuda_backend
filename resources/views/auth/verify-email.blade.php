@extends('auth.app')

@section('title', __('Verify Email'))
@section('content')
    <div class="d-flex flex-column flex-lg-row flex-column-fluid">

        <!--begin::Body-->
        <div class="content d-flex flex-column flex-lg-row-fluid py-10">
            <!--begin::Content-->
            <div class="d-flex flex-center flex-column flex-column-fluid">
                <!--begin::Wrapper-->
                <div class="w-lg-600px p-10 p-lg-15 mx-auto card">

                    @include('backend.partials.brand_logo')
                    <!--begin::Heading-->
                    <div class="text-center mb-10">
                        <!--begin::Title-->
                        <h1 class="text-dark mb-3">
                            {{ __('Verify Your Email') }}
                        </h1>
                        <!--end::Title-->

                        <!--begin::Link-->
                        <div class="text-gray-400 fw-semibold fs-4">
                            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
                        </div>
                        <!--end::Link-->
                    </div>
                    <!--end::Heading-->

                    @if (session('status') == 'verification-link-sent')
                        <div class="alert alert-success d-flex align-items-center p-5 mb-10">
                            <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-dark">{{ __('Verification Link Sent') }}</h4>
                                <span>{{ __('A new verification link has been sent to the email address you provided during registration.') }}</span>
                            </div>
                        </div>
                    @endif

                    <!--begin::Actions-->
                    <div class="d-flex flex-wrap justify-content-between align-items-center pb-lg-0">
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="btn btn-lg btn-primary fw-bold me-4">
                                {{ __('Resend Verification Email') }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-lg btn-light-danger fw-bold">
                                {{ __('Log Out') }}
                            </button>
                        </form>
                    </div>
                    <!--end::Actions-->
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Body-->
    </div>
@endsection
