<?php
$setting = \App\Models\Setting::first();

$imageUrl = ($setting && $setting->favicon)
    ? \App\Helpers\helpers::generateTempURL($setting->favicon, config('app.file_system'))
    : null;
?>

<!--begin::Fonts(mandatory for all pages)-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" /> <!--end::Fonts-->

    <!--begin::Vendor Stylesheets(used for this page only)-->
    @if (app()->getLocale() == 'ar')
        <link href="{{ asset('backend/assets') ."/plugins/custom/fullcalendar/fullcalendar.bundle.rtl.css" }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('backend/assets') ."/plugins/custom/datatables/datatables.bundle.rtl.css" }}" rel="stylesheet" type="text/css" />
    @else
        <link href="{{ asset('backend/assets') ."/plugins/custom/fullcalendar/fullcalendar.bundle.css" }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('backend/assets') ."/plugins/custom/datatables/datatables.bundle.css" }}" rel="stylesheet" type="text/css" />
    @endif
    <!--end::Vendor Stylesheets-->


    <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
    @if (app()->getLocale() == 'ar')
        <link href="{{ asset('backend/assets') ."/plugins/global/plugins.bundle.rtl.css"}}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('backend/assets') ."/css/style.bundle.rtl.css"}}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('backend/assets/css/custom-theme.rtl.css') }}" rel="stylesheet" type="text/css" />
    @else
        <link href="{{ asset('backend/assets') ."/plugins/global/plugins.bundle.css"}}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('backend/assets') ."/css/style.bundle.css"}}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('backend/assets/css/custom-theme.css') }}" rel="stylesheet" type="text/css" />
    @endif
    <!--end::Global Stylesheets Bundle-->

    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/css/dropify.min.css" integrity="sha512-EZSUkJWTjzDzpOoaKBWXTOysFRw453h6NNy14F00a5dlsLup00a7bSsk5W55sBQL98a8Wn4Wz4y532e8h/7j2Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dropify@0.2.2/dist/css/dropify.min.css">
    <link rel="shortcut icon" href="{{ $imageUrl ??  asset('backend/assets') ."/media/logos/favicon.ico"}}" />

    @stack('style')
