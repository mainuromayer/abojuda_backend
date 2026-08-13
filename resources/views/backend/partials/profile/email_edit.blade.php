<div id="kt_signin_email_edit" class="flex-row-fluid d-none">
    <!--begin::Form-->
    <form id="kt_signin_change_email" action="{{ route('email.change') }}" method="POST">
        @csrf
        @method('PATCH')
        <div class="row mb-6">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="form-group">
                    <label for="emailaddress" class="form-label">{{ __('Enter New Email Address') }}</label>
                    <input type="email" class="form-control form-control-lg form-control-solid" id="emailaddress" placeholder="{{ __('Email Address') }}" name="emailaddress" value="{{ old('email', $user?->email) }}">
                    @error('emailaddress')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group">
                    <label for="confirmemailpassword" class="form-label">{{ __('Confirm Password') }}</label>
                    <input type="password" class="form-control form-control-lg form-control-solid" name="confirmemailpassword" id="confirmemailpassword">
                    @error('confirmemailpassword')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">{{ __('Update Email') }}</button>
            <button type="button" class="btn btn-secondary" id="kt_signin_cancel">{{ __('Cancel') }}</button>
        </div>
    </form>
    <!--end::Form-->
</div>
