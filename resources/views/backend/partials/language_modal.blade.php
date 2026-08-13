<!--begin::Language Settings Modal-->
<div class="modal fade" id="kt_modal_language_settings" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-500px">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <!--begin::Modal header-->
            <div class="modal-header border-0 pb-0">
                <h3 class="modal-title fw-bold text-gray-900 fs-2">{{ __('Language Settings') }}</h3>
                <button type="button" class="btn btn-icon btn-sm btn-active-light-primary rounded-circle" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </button>
            </div>
            <!--end::Modal header-->

            <!--begin::Modal body-->
            <div class="modal-body pt-2 pb-6 px-8">
                <p class="text-gray-500 fs-6 mb-6">{{ __('Choose the language used throughout the application.') }}</p>

                <form id="kt_language_form" action="{{ route('language.switch') }}" method="POST">
                    @csrf
                    <input type="hidden" name="locale" id="selected_locale" value="{{ app()->getLocale() }}">

                    <div class="d-flex flex-column gap-4">
                        <!--begin::Option - English-->
                        <div class="language-card-option p-4 rounded-3 border transition-all cursor-pointer d-flex align-items-center justify-content-between {{ app()->getLocale() == 'en' ? 'border-primary bg-light-primary' : 'border-gray-300' }}"
                             onclick="selectLanguageOption('en', this)">
                            <div class="d-flex align-items-center gap-4">
                                <div class="symbol symbol-45px symbol-circle bg-light-primary d-flex align-items-center justify-content-center">
                                    <i class="ki-duotone ki-geolocation fs-2x text-primary"><span class="path1"></span><span class="path2"></span></i>
                                </div>
                                <div>
                                    <div class="fw-bold fs-5 text-gray-900">English</div>
                                    <div class="text-gray-500 fs-7">English</div>
                                </div>
                            </div>
                            <div class="check-icon-wrapper">
                                @if(app()->getLocale() == 'en')
                                    <span class="badge badge-circle badge-primary p-2">
                                        <i class="ki-duotone ki-check fs-2 text-white"><span class="path1"></span></i>
                                    </span>
                                @else
                                    <span class="badge badge-circle badge-light p-2 d-none">
                                        <i class="ki-duotone ki-check fs-2 text-white"><span class="path1"></span></i>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <!--end::Option - English-->

                        <!--begin::Option - Arabic-->
                        <div class="language-card-option p-4 rounded-3 border transition-all cursor-pointer d-flex align-items-center justify-content-between {{ app()->getLocale() == 'ar' ? 'border-primary bg-light-primary' : 'border-gray-300' }}"
                             onclick="selectLanguageOption('ar', this)">
                            <div class="d-flex align-items-center gap-4">
                                <div class="symbol symbol-45px symbol-circle bg-light-info d-flex align-items-center justify-content-center">
                                    <i class="ki-duotone ki-geolocation fs-2x text-info"><span class="path1"></span><span class="path2"></span></i>
                                </div>
                                <div>
                                    <div class="fw-bold fs-5 text-gray-900">Arabic</div>
                                    <div class="text-gray-500 fs-7">العربية</div>
                                </div>
                            </div>
                            <div class="check-icon-wrapper">
                                @if(app()->getLocale() == 'ar')
                                    <span class="badge badge-circle badge-primary p-2">
                                        <i class="ki-duotone ki-check fs-2 text-white"><span class="path1"></span></i>
                                    </span>
                                @else
                                    <span class="badge badge-circle badge-light p-2 d-none">
                                        <i class="ki-duotone ki-check fs-2 text-white"><span class="path1"></span></i>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <!--end::Option - Arabic-->
                    </div>

                    <div class="text-center text-gray-500 fs-7 my-6">
                        {{ __('Language changes will update the app interface instantly.') }}
                    </div>

                    <div class="d-flex justify-content-end gap-3">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary px-6">{{ __('Save Changes') }}</button>
                    </div>
                </form>
            </div>
            <!--end::Modal body-->
        </div>
    </div>
</div>

<script>
    function selectLanguageOption(locale, element) {
        document.getElementById('selected_locale').value = locale;
        
        document.querySelectorAll('.language-card-option').forEach(card => {
            card.classList.remove('border-primary', 'bg-light-primary');
            card.classList.add('border-gray-300');
            const checkBadge = card.querySelector('.check-icon-wrapper span');
            if (checkBadge) {
                checkBadge.className = 'badge badge-circle badge-light p-2 d-none';
            }
        });

        element.classList.remove('border-gray-300');
        element.classList.add('border-primary', 'bg-light-primary');
        const activeCheck = element.querySelector('.check-icon-wrapper span');
        if (activeCheck) {
            activeCheck.className = 'badge badge-circle badge-primary p-2';
        }
    }
</script>
