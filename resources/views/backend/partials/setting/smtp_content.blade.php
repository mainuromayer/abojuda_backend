<div id="kt_account_settings_profile_details" class="collapse show">
    <!--begin::Form-->
    <form action="{{ route('smtp.store') }}" method="POST" enctype="multipart/form-data" class="form fv-plugins-bootstrap5 fv-plugins-framework" novalidate="novalidate">
        @csrf
        <!--begin::Card body-->
        <div class="card-body border-top p-9">
            <!-- Second Row: SMTP Configuration -->
            <div class="d-flex flex-column flex-md-row gap-5 mb-6">
                <!-- Mail Mailer -->
                <div class="fv-row flex-row-fluid">
                    <label class="required form-label">{{ __('Mail Mailer') }}</label>
                    <input class="form-control form-control-solid" type="text" name="mail_mailer" placeholder="{{ __('Enter mail mailer (e.g., smtp)') }}" value="{{ old('mail_mailer',  env('MAIL_MAILER') ?? 'smtp') }}">
                </div>

                <!-- Mail Host -->
                <div class="fv-row flex-row-fluid">
                    <label class="required form-label">{{ __('Mail Host') }}</label>
                    <input class="form-control form-control-solid" type="text" name="mail_host" placeholder="{{ __('Enter mail host (e.g., smtp.gmail.com)') }}" value="{{ old('mail_host', env('MAIL_HOST') ?? '') }}">
                </div>
            </div>

            <!-- Third Row: SMTP Configuration (Continued) -->
            <div class="d-flex flex-column flex-md-row gap-5 mb-6">
                <!-- Mail Port -->
                <div class="fv-row flex-row-fluid">
                    <label class="required form-label">{{ __('Mail Port') }}</label>
                    <input class="form-control form-control-solid" type="number" name="mail_port" placeholder="{{ __('Enter mail port (e.g., 587)') }}" value="{{ old('mail_port',  env('MAIL_PORT') ?? 587) }}">
                </div>

                <!-- Mail Username -->
                <div class="fv-row flex-row-fluid">
                    <label class="required form-label">{{ __('Mail Username') }}</label>
                    <input class="form-control form-control-solid" type="text" name="mail_username" placeholder="{{ __('Enter mail username') }}" value="{{ old('mail_username', env('MAIL_USERNAME') ?? '') }}">
                </div>
            </div>

            <!-- Fourth Row: SMTP Configuration (Continued) -->
            <div class="d-flex flex-column flex-md-row gap-5 mb-6">
                <!-- Mail Password -->
                <div class="fv-row flex-row-fluid position-relative">
                    <label class="required form-label">{{ __('Mail Password') }}</label>
                    <div class="input-group">
                        <input class="form-control form-control-solid" type="password" name="mail_password" id="mail_password" placeholder="{{ __('Enter mail password') }}" value="{{ old('mail_password', $setting->mail_password ?? env('MAIL_PASSWORD') ?? '') }}">
                        <span class="input-group-append">
                            <button type="button" class="btn btn-icon btn-sm btn-active-color-primary" id="togglePassword">
                                <i class="fas fa-eye" id="passwordIcon"></i>
                            </button>
                        </span>
                    </div>
                </div>

                <!-- Mail Encryption -->
                <div class="fv-row flex-row-fluid">
                    <label class="required form-label">{{ __('Mail Encryption') }}</label>
                    <input class="form-control form-control-solid" type="text" name="mail_encryption" placeholder="{{ __('Enter mail encryption (e.g., tls)') }}" value="{{ old('mail_encryption', env('MAIL_ENCRYPTION') ?? 'tls') }}">
                </div>
            </div>

            <!-- Fifth Row: SMTP Configuration (Continued) -->
            <div class="d-flex flex-column flex-md-row gap-5 mb-6">
                <!-- Mail From Address -->
                <div class="fv-row flex-row-fluid">
                    <label class="required form-label">{{ __('Mail From Address') }}</label>
                    <input class="form-control form-control-solid" type="email" name="mail_from_address" placeholder="{{ __('Enter mail from address (e.g., example@domain.com)') }}" value="{{ old('mail_from_address',  env('MAIL_FROM_ADDRESS') ?? '') }}">
                </div>
            </div>
        </div>
        <!--end::Card body-->

        <!--begin::Actions-->
        <div class="card-footer d-flex justify-content-end py-6 px-9">
            <button type="submit" class="btn btn-primary" id="kt_account_profile_details_submit">{{ __('Save Changes') }}</button>
        </div>
        <!--end::Actions-->
        <input type="hidden"></form>
    <!--end::Form-->
</div>
