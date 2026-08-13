<div class="d-flex flex-wrap align-items-center mb-10">
    <!--begin::Label-->
    <div id="kt_signin_password">
        <div class="fs-6 fw-bold mb-1">{{ __('Password') }}</div>
        <div class="fw-semibold text-gray-600">************</div>
    </div>
    <!--end::Label-->

    <!--begin::Edit-->
    <div id="kt_signin_password_edit" class="flex-row-fluid d-none">
        <!--begin::Form-->
        <form id="kt_signin_change_password" action="{{ route('password.change') }}" method="POST" class="form fv-plugins-bootstrap5 fv-plugins-framework" novalidate="novalidate">
            @csrf
            @method('PATCH')
            <div class="row mb-1">
                <!-- Current Password -->
                <div class="col-lg-4">
                    <div class="mb-3 position-relative">
                        <label class="form-label fw-bold">{{ __('Current Password') }}</label>
                        <div class="input-group">
                            <input type="password" class="form-control form-control-lg form-control-solid password-field" id="currentpassword" name="currentpassword" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                            <button class="btn btn-solid btn-active-light-primary border toggle-password" type="button" data-target="currentpassword" style="border-top-left-radius: 0; border-bottom-left-radius: 0; background-color: #f1f1f4;">
                                <i class="fa fa-eye-slash fs-3"></i>
                            </button>
                        </div>
                        @error('currentpassword')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- New Password -->
                <div class="col-lg-4">
                    <div class="mb-3 position-relative">
                        <label class="form-label fw-bold">{{ __('New Password') }}</label>
                        <div class="input-group">
                            <input type="password" class="form-control form-control-lg form-control-solid password-field" id="password" name="password" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                            <button class="btn btn-solid btn-active-light-primary border toggle-password" type="button" data-target="password" style="border-top-left-radius: 0; border-bottom-left-radius: 0; background-color: #f1f1f4;">
                                <i class="fa fa-eye-slash fs-3"></i>
                            </button>
                        </div>
                        @error('password')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="col-lg-4">
                    <div class="mb-3 position-relative">
                        <label class="form-label fw-bold">{{ __('Confirm Password') }}</label>
                        <div class="input-group">
                            <input type="password" class="form-control form-control-lg form-control-solid password-field" id="password_confirmation" name="password_confirmation" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                            <button class="btn btn-solid btn-active-light-primary border toggle-password" type="button" data-target="password_confirmation" style="border-top-left-radius: 0; border-bottom-left-radius: 0; background-color: #f1f1f4;">
                                <i class="fa fa-eye-slash fs-3"></i>
                            </button>
                        </div>
                        <div id="password-match-msg" class="small mt-1"></div>
                        @error('password_confirmation')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="form-text mb-5">{{ __('Password must be at least 8 character and contain symbols') }}</div>

            <div class="d-flex">
                <button id="kt_password_submit" type="submit" class="btn btn-primary me-2 px-6">{{ __('Update Password') }}</button>
                <button id="kt_password_cancel" type="button" class="btn btn-color-gray-400 btn-active-light-primary px-6">{{ __('Cancel') }}</button>
            </div>
        </form>
        <!--end::Form-->
    </div>
    <!--end::Edit-->

    <!--begin::Action-->
    <div id="kt_signin_password_button" class="ms-auto">
        <button class="btn btn-light btn-active-light-primary">{{ __('Reset Password') }}</button>
    </div>
    <!--end::Action-->
</div>
