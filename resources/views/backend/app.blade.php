<!DOCTYPE html>

<html lang="en">
<!--begin::Head-->
<meta http-equiv="content-type" content="text/html;charset=UTF-8" /><!-- /Added by HTTrack -->

<head>
    <title>@yield('title')</title>
    <meta charset="utf-8" />
    <meta name="description"
        content="Craft admin dashboard live demo. Check out all the features of the admin panel. A large number of settings, additional services and widgets." />
    <meta name="keywords"
        content="Craft, bootstrap, bootstrap 5, admin themes, dark mode, free admin themes, bootstrap admin, bootstrap dashboard" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="Craft - Bootstrap 5 HTML Admin Dashboard Theme" />
    <meta property="og:url" content="https://themes.getbootstrap.com/product/craft-bootstrap-5-admin-dashboard-theme" />
    <meta property="og:site_name" content="Keenthemes | Craft" />
    <link rel="canonical" href="https://preview.keenthemes.com/craft" />


    @include('backend.partials.style')
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!--Begin::Google Tag Manager -->
    {{--    <script> --}}
    {{--        (function(w, d, s, l, i) { --}}
    {{--            w[l] = w[l] || []; --}}
    {{--            w[l].push({ --}}
    {{--                'gtm.start': new Date().getTime(), --}}
    {{--                event: 'gtm.js' --}}
    {{--            }); --}}
    {{--            var f = d.getElementsByTagName(s)[0], --}}
    {{--                j = d.createElement(s), --}}
    {{--                dl = l != 'dataLayer' ? '&l=' + l : ''; --}}
    {{--            j.async = true; --}}
    {{--            j.src = --}}
    {{--                '../../../www.googletagmanager.com/gtm5445.html?id=' + i + dl; --}}
    {{--            f.parentNode.insertBefore(j, f); --}}
    {{--        })(window, document, 'script', 'dataLayer', 'GTM-5FS8GGP'); --}}
    {{--    </script> --}}
    <!--End::Google Tag Manager -->

    <script>
        // Frame-busting to prevent site from being loaded within a frame without permission (click-jacking)
        if (window.top != window.self) {
            window.top.location.replace(window.self.location.href);
        }
    </script>
</head>
<!--end::Head-->

<!--begin::Body-->

<body id="kt_body"
    class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled aside-fixed aside-default-enabled">
    <!--begin::Theme mode setup on page load-->
    <script>
        var defaultThemeMode = "light";
        var themeMode;

        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }

            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }

            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>
    <!--end::Theme mode setup on page load-->
    <!--Begin::Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5FS8GGP" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
    <!--End::Google Tag Manager (noscript) -->

    <!--begin::Main-->
    <!--begin::Root-->
    <div class="d-flex flex-column flex-root">
        <!--begin::Page-->
        <div class="page d-flex flex-row flex-column-fluid">
            <!--begin::Aside-->
            <div id="kt_aside" class="aside aside-default  aside-hoverable " data-kt-drawer="true"
                data-kt-drawer-name="aside" data-kt-drawer-activate="{default: true, lg: false}"
                data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'200px', '300px': '250px'}"
                data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_aside_toggle">

                <!--begin::Brand-->
                @include('backend.partials.brand_logo')
                <!--end::Brand-->

                <!--begin::Aside menu-->
                @include('backend.partials.sidebar')
                <!--end::Aside menu-->

                <!--begin::Footer-->
                <div class="aside-footer flex-column-auto pb-5 d-none" id="kt_aside_footer">
                    <a href="../index-2.html" class="btn btn-light-primary w-100">
                        Button
                    </a>
                </div>
                <!--end::Footer-->
            </div>
            <!--end::Aside-->

            <!--begin::Wrapper-->
            <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
                <!--begin::Header-->
                @include('backend.partials.header')
                <!--end::Header-->
                <!--begin::Content-->
                @yield('content')
                <!--end::Content-->

                <!--begin::Footer-->
                @include('backend.partials.footer')
                <!--end::Footer-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Page-->
    </div>
    <!--end::Root-->

    <!--begin::Scroll top-->
    @include('backend.partials.scroll')
    <!--end::Scroll top-->

    @include('backend.partials.script')
</body>
<!--end::Body-->

</html>
