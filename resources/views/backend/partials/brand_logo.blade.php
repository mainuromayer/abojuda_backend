<?php
    $setting = \App\Models\Setting::first();

    $imageUrl = ($setting && $setting->logo)
        ? \App\Helpers\helpers::generateTempURL($setting->logo, config('app.file_system'))
        : null;
?>

{{--<div class="aside-logo flex-column-auto px-10 pt-9 pb-5" id="kt_aside_logo">--}}
{{--    <!--begin::Logo-->--}}
{{--    <a href="#">--}}
{{--        <img alt="Logo" src="{{ $imageUrl ??  asset('backend/assets') ."/media/logos/logo-default.svg"}}" class="max-h-50px logo-default theme-light-show" />--}}
{{--        <img alt="Logo" src="{{ $imageUrl ?? asset('backend/assets') ."/media/logos/logo-default-dark.svg"}}" class="max-h-50px logo-default theme-dark-show" />--}}
{{--        <img alt="Logo" src="{{ $imageUrl ?? asset('backend/assets') ."/media/logos/logo-minimize.svg"}}" class="max-h-50px logo-minimize" />--}}
{{--    </a>--}}
{{--    <!--end::Logo-->--}}
{{--</div>--}}


<div class="aside-logo flex-column-auto px-10 pt-9 pb-5 justify-content-center" id="kt_aside_logo" style="text-align: center;">
    <!--begin::Logo-->
    <a href="#">
        <img alt="Logo" src="{{ $imageUrl ?? asset('backend/assets/media/logos/logo-default.svg') }}" class="" style="width:40%; height: 40%; object-fit: cover;" />
    </a>
    <!--end::Logo-->
</div>
