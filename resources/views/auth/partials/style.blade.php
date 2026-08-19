<link rel="shortcut icon" href="{{ asset('backend/assets') ."/media/logos/favicon.ico" }}" />

<!--begin::Fonts(mandatory for all pages)-->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" /> <!--end::Fonts-->

<!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
@if (app()->getLocale() == 'ar')
    <link href="{{ asset('backend/assets') ."/plugins/global/plugins.bundle.rtl.css" }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets') ."/css/style.bundle.rtl.css" }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/css/custom-theme.rtl.css') }}" rel="stylesheet" type="text/css" />
@else
    <link href="{{ asset('backend/assets') ."/plugins/global/plugins.bundle.css" }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets') ."/css/style.bundle.css" }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/css/custom-theme.css') }}" rel="stylesheet" type="text/css" />
@endif

@stack('styles')
