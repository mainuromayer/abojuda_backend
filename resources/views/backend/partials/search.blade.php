<div id="kt_header_search" class="header-search d-flex align-items-center w-100 w-lg-300px" data-kt-search-keypress="true"
    data-kt-search-min-length="2" data-kt-search-enter="enter" data-kt-search-layout="menu" data-kt-search-responsive="lg"
    data-kt-menu-trigger="auto" data-kt-menu-permanent="true" data-kt-menu-placement="bottom-start">

    <!--begin::Tablet and mobile search toggle-->
    <div data-kt-search-element="toggle" class="search-toggle-mobile d-flex d-lg-none align-items-center cursor-pointer">
        <div class="d-flex btn btn-icon btn-custom btn-active-light btn-active-color-primary w-30px h-30px w-md-40px h-md-40px">
            <i class="ki-duotone ki-magnifier fs-2"><span class="path1"></span><span class="path2"></span></i>
        </div>
    </div>
    <!--end::Tablet and mobile search toggle-->

    <!--begin::Form-->
    <form data-kt-search-element="form" class="d-none d-lg-block w-100 position-relative mb-5 mb-lg-0" autocomplete="off">
        <input type="hidden" />

        <!--begin::Icon-->
        <i class="ki-duotone ki-magnifier search-icon fs-2 text-gray-500 position-absolute top-50 translate-middle-y {{ app()->getLocale() == 'ar' ? 'me-5 end-0' : 'ms-5 start-0' }}">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
        <!--end::Icon-->

        <!--begin::Input-->
        <input type="text" class="search-input form-control form-control-solid {{ app()->getLocale() == 'ar' ? 'pe-13 ps-5' : 'ps-13 pe-5' }}"
            name="search" id="global_search_input" value="" placeholder="{{ __('Search...') }}" data-kt-search-element="input" />
        <!--end::Input-->

        <!--begin::Spinner-->
        <span class="search-spinner position-absolute top-50 {{ app()->getLocale() == 'ar' ? 'start-0 ms-5' : 'end-0 me-5' }} translate-middle-y lh-0 d-none" id="global_search_spinner">
            <span class="spinner-border h-15px w-15px align-middle text-gray-400"></span>
        </span>
        <!--end::Spinner-->
    </form>
    <!--end::Form-->

    <!--begin::Menu-->
    <div data-kt-search-element="content" class="menu menu-sub menu-sub-dropdown py-5 px-5 overflow-hidden w-300px w-md-350px">
        <!--begin::Wrapper-->
        <div data-kt-search-element="wrapper">
            <!--begin::Results-->
            <div id="global_search_results_container" class="scroll-y mh-250px mh-lg-350px">
                <div class="text-center text-muted fs-7 py-4" id="global_search_initial_text">
                    {{ __('Type at least 2 characters to search...') }}
                </div>
            </div>
            <!--end::Results-->
        </div>
        <!--end::Wrapper-->
    </div>
    <!--end::Menu-->
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('global_search_input');
        const spinner = document.getElementById('global_search_spinner');
        const container = document.getElementById('global_search_results_container');
        let searchTimeout = null;

        if (!searchInput) return;

        searchInput.addEventListener('input', function() {
            const query = this.value.trim();

            if (searchTimeout) clearTimeout(searchTimeout);

            if (query.length < 2) {
                container.innerHTML = '<div class="text-center text-muted fs-7 py-4">' + "{{ __('Type at least 2 characters to search...') }}" + '</div>';
                return;
            }

            if (spinner) spinner.classList.remove('d-none');

            searchTimeout = setTimeout(function() {
                fetch("{{ route('global.search') }}?query=" + encodeURIComponent(query), {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "Accept": "application/json"
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (spinner) spinner.classList.add('d-none');

                    if (!data.results || data.results.length === 0) {
                        container.innerHTML = `
                            <div class="text-center py-6">
                                <i class="ki-duotone ki-search-list fs-3x text-gray-400 mb-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                <div class="fw-bold text-gray-600 fs-6">${"{{ __('No result found') }}"}</div>
                            </div>`;
                        return;
                    }

                    let html = '<div class="d-flex flex-column gap-2">';
                    data.results.forEach(item => {
                        html += `
                            <a href="${item.url}" class="d-flex align-items-center p-3 rounded-2 bg-hover-light text-dark text-hover-primary transition-all">
                                <div class="symbol symbol-35px me-3">
                                    <span class="symbol-label bg-light-primary text-primary">
                                        <i class="ki-duotone ${item.icon} fs-3 text-primary"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold fs-6 text-gray-800">${item.title}</span>
                                    <span class="fs-7 text-muted">${item.type} ${item.sub ? '• ' + item.sub : ''}</span>
                                </div>
                            </a>`;
                    });
                    html += '</div>';

                    container.innerHTML = html;
                })
                .catch(err => {
                    if (spinner) spinner.classList.add('d-none');
                    container.innerHTML = '<div class="text-center text-danger fs-7 py-4">' + "{{ __('Search error occurred.') }}" + '</div>';
                });
            }, 300);
        });
    });
</script>
