<?php
$setting = \App\Models\Setting::first();

$imageUrl = ($setting && $setting->logo)
    ? \App\Helpers\helpers::generateTempURL($setting->logo, config('app.file_system'))
    : null;
?>

<!--begin::Header-->
<div id="kt_header" class="header " data-kt-sticky="true" data-kt-sticky-name="header"
    data-kt-sticky-offset="{default: '200px', lg: '300px'}">

    <!--begin::Container-->
    <div class=" container-fluid  d-flex align-items-stretch justify-content-between">
        <!--begin::Logo bar-->
        <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
            <!--begin::Aside Toggle-->
            <div class="d-flex align-items-center d-lg-none">
                <div class="btn btn-icon btn-active-color-primary ms-n2 me-1 " id="kt_aside_toggle">
                    <i class="ki-duotone ki-abstract-14 fs-1"><span class="path1"></span><span
                            class="path2"></span></i>
                </div>
            </div>
            <!--end::Aside Toggle-->

            <!--begin::Logo-->
            <a href="#" class="d-lg-none">
                <img alt="Logo" src="{{ $imageUrl ?? asset('backend/assets') . '/media/logos/logo-compact.svg' }}"
                    class="mh-40px" />
            </a>
            <!--end::Logo-->

            <!--begin::Aside toggler-->
            <div class="btn btn-icon w-auto ps-0 btn-active-color-primary d-none d-lg-inline-flex me-2 me-lg-5 "
                data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
                data-kt-toggle-name="aside-minimize">

                <i class="ki-duotone ki-black-left-line fs-1 rotate-180"><span class="path1"></span><span
                        class="path2"></span></i>
            </div>
            <!--end::Aside toggler-->
        </div>
        <!--end::Logo bar-->

        <!--begin::Topbar-->
        <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1">
            <!--begin::Search-->
            <div class="d-flex align-items-stretch me-1">

                <!--begin::Search-->
                @include('backend.partials.search')
                <!--end::Search-->
            </div>
            <!--end::Search-->

            <!--begin::Toolbar wrapper-->
            <div class="d-flex align-items-stretch flex-shrink-0">

                <!--begin::Notifications-->
                @include('backend.partials.notification')
                <!--end::Notifications-->

                <!--begin::Language Switcher-->
                <div class="d-flex align-items-center ms-1 ms-lg-3">
                    <button type="button" class="btn btn-icon btn-custom btn-active-light btn-active-color-primary w-30px h-30px w-md-40px h-md-40px fw-bold text-uppercase" data-bs-toggle="modal" data-bs-target="#kt_modal_language_settings" title="{{ __('Language Settings') }}">
                        <span class="badge badge-light-primary fw-bold fs-7">{{ strtoupper(app()->getLocale()) }}</span>
                    </button>
                </div>
                <!--end::Language Switcher-->

                <!--begin::Theme mode-->
                @include('backend.partials.theme_mode')
                <!--end::Theme mode-->

                <!--begin::User-->
                @include('backend.partials.user_profile')
                <!--end::User -->
            </div>
            <!--end::Toolbar wrapper-->
        </div>
        <!--end::Topbar-->
    </div>
    <!--end::Container-->
</div>
<!--end::Header-->

@include('backend.partials.language_modal')
