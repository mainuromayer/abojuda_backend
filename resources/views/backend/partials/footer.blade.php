<?php
    $current_year = now()->format('Y');
    if (app()->getLocale() == 'ar') {
        $current_year = str_replace(['0','1','2','3','4','5','6','7','8','9'], ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'], $current_year);
    }
    $system_copyright = \App\Models\Setting::first();
?>
<div class="footer py-4 d-flex flex-lg-column " id="kt_footer">
    <!--begin::Container-->
    <div class=" container-fluid  d-flex flex-column flex-md-row flex-stack">
        <!--begin::Copyright-->
        <div class="text-dark order-2 order-md-1">
            <span class="text-muted fw-semibold me-2">{{ $current_year }} &copy;</span>

            <a href="#" target="_blank" class="text-gray-800 text-hover-primary">{{ $system_copyright?->copyright_text }}</a>
        </div>
        <!--end::Copyright-->
    </div>
    <!--end::Container-->
</div>
