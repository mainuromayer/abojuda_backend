    <!--begin::Javascript-->
    <script>
        var hostUrl = "../assets/index.html";
    </script>

    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
    <script src="{{ asset('backend/assets') ."/plugins/global/plugins.bundle.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/scripts.bundle.js"}}"></script>
    <!--end::Global Javascript Bundle-->

    <!--begin::Vendors Javascript(used for this page only)-->
    <script src="{{ asset('backend/assets') ."/plugins/custom/datatables/datatables.bundle.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/plugins/custom/formrepeater/formrepeater.bundle.js"}}"></script>
    <!--end::Vendors Javascript-->

    <!--begin::Custom Javascript(used for this page only)-->
    <script src="{{ asset('backend/assets') ."/js/widgets.bundle.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/widgets.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/apps/chat/chat.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/utilities/modals/upgrade-plan.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/utilities/modals/create-project/type.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/utilities/modals/create-project/budget.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/utilities/modals/create-project/settings.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/utilities/modals/create-project/team.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/utilities/modals/create-project/targets.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/utilities/modals/create-project/files.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/utilities/modals/create-project/complete.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/utilities/modals/create-project/main.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/utilities/modals/users-search.js"}}"></script>

    <!--end::Custom Javascript-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dropify@0.2.2/dist/js/dropify.min.js"></script>

    <!--end::Javascript-->
    <script>
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "timeOut": "2000",
            "positionClass": "toast-top-right"
        };

        @if (session('success'))
        toastr.success(@json(session('success')));
        @endif

        @if (session('error'))
        toastr.error(@json(session('error')));
        @endif

        @if ($errors->any())
        @foreach ($errors->all() as $error)
        toastr.error(@json($error));
        @endforeach
        @endif
    </script>


    <!--end::Custom Javascript-->
    @stack('script')
