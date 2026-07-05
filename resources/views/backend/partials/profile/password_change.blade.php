<div class="d-flex flex-wrap align-items-center mb-10">
    <!--begin::Label-->
    <div id="kt_signin_password">
        <div class="fs-6 fw-bold mb-1">Password</div>
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
                        <label class="form-label fw-bold">Current Password</label>
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
                        <label class="form-label fw-bold">New Password</label>
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
                        <label class="form-label fw-bold">Confirm Password</label>
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
            <div class="form-text mb-5">Password must be at least 8 character and contain symbols</div>

            <div class="d-flex">
                <button id="kt_password_submit" type="submit" class="btn btn-primary me-2 px-6">Update Password</button>
                <button id="kt_password_cancel" type="button" class="btn btn-color-gray-400 btn-active-light-primary px-6">Cancel</button>
            </div>
        </form>
        <!--end::Form-->
    </div>
    <!--end::Edit-->

    <!--begin::Action-->
    <div id="kt_signin_password_button" class="ms-auto">
        <button class="btn btn-light btn-active-light-primary">Reset Password</button>
    </div>
    <!--end::Action-->
</div>


{{--<form id="kt_signin_change_password" action="{{ route('password.change') }}" method="POST">--}}
{{--    @csrf--}}
{{--    @method('PATCH')--}}
{{--    <div class="row">--}}
{{--        <!-- Current Password -->--}}
{{--        <div class="col-lg-4">--}}
{{--            <div class="mb-3 position-relative">--}}
{{--                <label class="form-label fw-bold">Current Password</label>--}}
{{--                <div class="input-group">--}}
{{--                    <input type="password" class="form-control form-control-lg form-control-solid password-field" id="currentpassword" name="currentpassword">--}}
{{--                    <span class="input-group-text toggle-password" data-target="currentpassword"><i class="fa fa-eye"></i></span>--}}
{{--                </div>--}}
{{--                @error('currentpassword')--}}
{{--                <div class="text-danger">{{ $message }}</div>--}}
{{--                @enderror--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        <!-- New Password -->--}}
{{--        <div class="col-lg-4">--}}
{{--            <div class="mb-3 position-relative">--}}
{{--                <label class="form-label fw-bold">New Password</label>--}}
{{--                <div class="input-group">--}}
{{--                    <input type="password" class="form-control form-control-lg form-control-solid password-field" id="password" name="password">--}}
{{--                    <span class="input-group-text toggle-password" data-target="password"><i class="fa fa-eye"></i></span>--}}
{{--                </div>--}}
{{--                @error('password')--}}
{{--                <div class="text-danger">{{ $message }}</div>--}}
{{--                @enderror--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        <!-- Confirm Password -->--}}
{{--        <div class="col-lg-4">--}}
{{--            <div class="mb-3 position-relative">--}}
{{--                <label class="form-label fw-bold">Confirm Password</label>--}}
{{--                <div class="input-group">--}}
{{--                    <input type="password" class="form-control form-control-lg form-control-solid password-field" id="password_confirmation" name="password_confirmation">--}}
{{--                    <span class="input-group-text toggle-password" data-target="password_confirmation"><i class="fa fa-eye"></i></span>--}}
{{--                </div>--}}
{{--                <div id="password-match-msg" class="small mt-1"></div>--}}
{{--                @error('password_confirmation')--}}
{{--                <div class="text-danger">{{ $message }}</div>--}}
{{--                @enderror--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}

{{--    <div class="form-text mb-5">Password must be at least 8 characters and contain symbols.</div>--}}

{{--    <div class="d-flex">--}}
{{--        <button type="submit" class="btn btn-primary px-6">Update Password</button>--}}
{{--        <button type="button" class="btn btn-secondary px-6 ms-2" onclick="window.location.reload()">Cancel</button>--}}
{{--    </div>--}}
{{--</form>--}}
