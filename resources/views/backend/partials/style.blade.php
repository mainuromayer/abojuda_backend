<?php
$setting = \App\Models\Setting::first();

$imageUrl = ($setting && $setting->favicon)
    ? \App\Helpers\helpers::generateTempURL($setting->favicon, config('app.file_system'))
    : null;
?>

<!--begin::Fonts(mandatory for all pages)-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" /> <!--end::Fonts-->

    <!--begin::Vendor Stylesheets(used for this page only)-->
    <link href="{{ asset('backend/assets') ."/plugins/custom/fullcalendar/fullcalendar.bundle.css" }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets') ."/plugins/custom/datatables/datatables.bundle.css" }}" rel="stylesheet" type="text/css" />
    <!--end::Vendor Stylesheets-->


    <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
    <link href="{{ asset('backend/assets') ."/plugins/global/plugins.bundle.css"}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets') ."/css/style.bundle.css"}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/css/custom-theme.css') }}" rel="stylesheet" type="text/css" />
    <!--end::Global Stylesheets Bundle-->

    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
    <link rel="shortcut icon" href="{{ $imageUrl ??  asset('backend/assets') ."/media/logos/favicon.ico"}}" />

    @stack('style')
