<div class="modal fade" id="kt_modal_add_user" tabindex="-1" style="display: none;"
     aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header" id="kt_modal_add_user_header">
                <!--begin::Modal title-->
                <h2 class="fw-bold">{{ __('Add User') }}</h2>
                <!--end::Modal title-->

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-icon-primary"
                     data-kt-users-modal-action="close" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span
                            class="path2"></span></i>
                </div>
                <!--end::Close-->
            </div>
            <!--end::Modal header-->

            <!--begin::Modal body-->
            <div class="modal-body px-5 my-7">
                <!--begin::Form-->
                <form action="{{ route('user.store') }}" method="POST" enctype="multipart/form-data" class="form fv-plugins-bootstrap5 fv-plugins-framework">
                    @csrf
                    <!--begin::Scroll-->
                    <div class="d-flex flex-column scroll-y px-5 px-lg-10"
                         id="kt_modal_add_user_scroll" data-kt-scroll="true"
                         data-kt-scroll-activate="true" data-kt-scroll-max-height="auto"
                         data-kt-scroll-dependencies="#kt_modal_add_user_header"
                         data-kt-scroll-wrappers="#kt_modal_add_user_scroll"
                         data-kt-scroll-offset="300px" style="max-height: 177px;">
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="d-block fw-semibold fs-6 mb-5">{{ __('Avatar') }}</label>
                            <!--end::Label-->

                            <!--begin::Image placeholder-->
                            <style>
                                .image-input-placeholder {
                                    background-image: url({{ asset('backend/assets/media/svg/files/blank-image.svg') }});
                                }

                                [data-bs-theme="dark"] .image-input-placeholder {
                                    background-image: url({{ asset('backend/assets/media/svg/files/blank-image-dark.svg') }});
                                }
                            </style>
                            <!--end::Image placeholder-->
                            <!--begin::Image input-->
                            <div class="image-input image-input-outline image-input-placeholder"
                                 data-kt-image-input="true">
                                <!--begin::Preview existing avatar-->
                                <div class="image-input-wrapper w-125px h-125px"
                                     style="background-image: url({{ asset('backend/assets/media/avatars/300-1.jpg') }});">
                                </div>
                                <!--end::Preview existing avatar-->

                                <!--begin::Label-->
                                <label
                                    class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                    data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                    aria-label="{{ __('Change avatar') }}" data-bs-original-title="{{ __('Change avatar') }}"
                                    data-kt-initialized="1">
                                    <i class="ki-duotone ki-pencil fs-7">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <!--begin::Inputs-->
                                    <input type="file" name="avatar" accept=".png, .jpg, .jpeg">
                                    <input type="hidden" name="avatar_remove">
                                    <!--end::Inputs-->
                                </label>
                                <!--end::Label-->

                                <!--begin::Cancel-->
                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                    data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                                    aria-label="{{ __('Cancel avatar') }}" data-bs-original-title="{{ __('Cancel avatar') }}"
                                    data-kt-initialized="1">
                                                    <i class="ki-duotone ki-cross fs-2">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                </span>
                                <!--end::Cancel-->

                                <!--begin::Remove-->
                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                    data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                                    aria-label="{{ __('Remove avatar') }}" data-bs-original-title="{{ __('Remove avatar') }}"
                                    data-kt-initialized="1">
                                                    <i class="ki-duotone ki-cross fs-2">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                </span>
                                <!--end::Remove-->
                            </div>
                            <!--end::Image input-->

                            <!--begin::Hint-->
                            <div class="form-text">{{ __('Allowed file types: png, jpg, jpeg.') }}</div>
                            <!--end::Hint-->
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="fv-row mb-6">
                            <label class="required form-label">{{ __('Name') }}</label>
                            <input class="form-control form-control-solid" type="text" name="name" placeholder="{{ __('Enter name...') }}" value="{{ old('name') }}" required>
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="d-flex flex-column flex-md-row gap-5 mb-6">
                            <!-- Province -->
                            <div class="fv-row flex-row-fluid">
                                <label class="required form-label">{{ __('Province') }}</label>
                                <input class="form-control form-control-solid" type="text" name="province" placeholder="{{ __('Enter province...') }}" value="{{ old('province') }}" required>
                            </div>

                            <!-- City -->
                            <div class="fv-row flex-row-fluid">
                                <label class="required form-label">{{ __('City') }}</label>
                                <input class="form-control form-control-solid" type="text" name="city" placeholder="{{ __('Enter city...') }}" value="{{ old('city') }}" required>
                            </div>
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="fv-row mb-6">
                            <label class="form-label">{{ __('Address') }}</label>
                            <input class="form-control form-control-solid" type="text" name="address" placeholder="{{ __('Enter address...') }}" value="{{ old('address') }}">
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="fv-row mb-7 fv-plugins-icon-container">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">{{ __('Email') }}</label>
                            <!--end::Label-->

                            <!--begin::Input-->
                            <input type="email" name="email"
                                   class="form-control form-control-solid mb-3 mb-lg-0"
                                   placeholder="example@domain.com">
                            <!--end::Input-->
                            <div
                                class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                            </div>
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="mb-10">
                            <label for="exampleFormControlInput1" class="required form-label">{{ __('Role') }}</label>
                            <select class="form-select form-select-solid" aria-label="Select example" name="role">
                                <option value="">{{ __('Select Role') }}</option>
                                <option value="{{ \App\Enum\Role::USER->value }}">{{ __('User') }}</option>
                                <option value="{{ \App\Enum\Role::ADMIN->value }}">{{ __('Admin') }}</option>
                                <option value="{{ \App\Enum\Role::GUEST->value }}">{{ __('Guest') }}</option>
                            </select>
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="input-group mb-5">
                            <input type="password" class="form-control" name="password" id="passwordInput" placeholder="{{ __('Password') }}" aria-label="Password" aria-describedby="basic-addon2"/>
                            <span class="input-group-text" id="basic-addon2">
                                <button type="button" class="btn btn-outline-secondary btn-sm me-2" id="generatePassword">
                                    <i class="ki-duotone ki-arrows-circle">
                                     <span class="path1"></span>
                                     <span class="path2"></span>
                                    </i>
                                </button>

                                <button type="button" class="btn btn-outline-secondary btn-sm" id="togglePassword">
                                    <i class="ki-duotone ki-eye">
                                     <span class="path1"></span>
                                     <span class="path2"></span>
                                     <span class="path3"></span>
                                    </i>
                                </button>
                            </span>
                        </div>
                        <!--end::Input group-->
                    </div>
                    <!--end::Scroll-->

                    <!--begin::Actions-->
                    <div class="text-center pt-10">
                        <button type="reset" class="btn btn-light me-3"
                                data-kt-users-modal-action="cancel" data-bs-dismiss="modal">
                            {{ __('Discard') }}
                        </button>

                        <button type="submit" class="btn btn-primary"
                                data-kt-users-modal-action="submit">
                            <span class="indicator-label">
                                {{ __('Submit') }}
                            </span>
                            <span class="indicator-progress">
                                {{ __('Please wait...') }} <span
                                    class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                    <!--end::Actions-->
                </form>
                <!--end::Form-->
            </div>
            <!--end::Modal body-->
        </div>
        <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="kt_modal_edit_user" tabindex="-1" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header" id="kt_modal_edit_user_header">
                <h2 class="fw-bold">{{ __('Edit User') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-kt-users-modal-action="close" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body px-5 my-7">
                <form action="{{ route('user.update', ['id' => '__id__']) }}" method="POST" enctype="multipart/form-data" class="form fv-plugins-bootstrap5 fv-plugins-framework" id="kt_modal_edit_user_form">
                    @csrf
                    @method('PUT')
                    <div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_edit_user_scroll" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_edit_user_header" data-kt-scroll-wrappers="#kt_modal_edit_user_scroll" data-kt-scroll-offset="300px" style="max-height: 177px;">
                        <!-- Avatar -->
                        <div class="fv-row mb-7">
                            <label class="d-block fw-semibold fs-6 mb-5">{{ __('Avatar') }}</label>
                            <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true">
                                <div class="image-input-wrapper w-125px h-125px" id="edit_avatar_preview"></div>
                                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" aria-label="{{ __('Change avatar') }}" data-bs-original-title="{{ __('Change avatar') }}" data-kt-initialized="1">
                                    <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                    <input type="file" name="avatar" accept=".png, .jpg, .jpeg">
                                    <input type="hidden" name="avatar_remove">
                                </label>
                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" aria-label="{{ __('Cancel avatar') }}" data-bs-original-title="{{ __('Cancel avatar') }}" data-kt-initialized="1">
                                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                </span>
                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" aria-label="{{ __('Remove avatar') }}" data-bs-original-title="{{ __('Remove avatar') }}" data-kt-initialized="1">
                                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                </span>
                            </div>
                            <div class="form-text">{{ __('Allowed file types: png, jpg, jpeg.') }}</div>
                        </div>
                        <!-- Name -->
                        <div class="fv-row mb-6">
                            <label class="required form-label">{{ __('Name') }}</label>
                            <input class="form-control form-control-solid" type="text" name="name" id="edit_name" placeholder="{{ __('Enter name...') }}" required>
                        </div>
                        <!-- Province and City -->
                        <div class="d-flex flex-column flex-md-row gap-5 mb-6">
                            <div class="fv-row flex-row-fluid">
                                <label class="required form-label">{{ __('Province') }}</label>
                                <input class="form-control form-control-solid" type="text" name="province" id="edit_province" placeholder="{{ __('Enter province...') }}" required>
                            </div>
                            <div class="fv-row flex-row-fluid">
                                <label class="required form-label">{{ __('City') }}</label>
                                <input class="form-control form-control-solid" type="text" name="city" id="edit_city" placeholder="{{ __('Enter city...') }}" required>
                            </div>
                        </div>
                        <!-- Address -->
                        <div class="fv-row mb-6">
                            <label class="form-label">{{ __('Address') }}</label>
                            <input class="form-control form-control-solid" type="text" name="address" id="edit_address" placeholder="{{ __('Enter address...') }}">
                        </div>
                        <!-- Email -->
                        <div class="fv-row mb-7 fv-plugins-icon-container">
                            <label class="required fw-semibold fs-6 mb-2">{{ __('Email') }}</label>
                            <input type="email" name="email" id="edit_email" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="example@domain.com">
                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                        </div>
                        <!-- Role -->
                        <div class="mb-10">
                            <label for="exampleFormControlInput1" class="required form-label">{{ __('Role') }}</label>
                            <select class="form-select form-select-solid" aria-label="Select example" name="role" id="edit_role">
                                <option value="">{{ __('Select Role') }}</option>
                                <option value="{{ \App\Enum\Role::USER->value }}">{{ __('User') }}</option>
                                <option value="{{ \App\Enum\Role::ADMIN->value }}">{{ __('Admin') }}</option>
                                <option value="{{ \App\Enum\Role::GUEST->value }}">{{ __('Guest') }}</option>
                            </select>
                        </div>
                        <!-- Password -->
                        <div class="input-group mb-5">
                            <input type="password" class="form-control" name="password" id="edit_passwordInput" placeholder="{{ __('Password') }}" aria-label="Password" aria-describedby="edit_basic-addon2"/>
                            <span class="input-group-text" id="edit_basic-addon2">
                                <button type="button" class="btn btn-outline-secondary btn-sm me-2" id="edit_generatePassword">
                                    <i class="ki-duotone ki-arrows-circle"><span class="path1"></span><span class="path2"></span></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="edit_togglePassword">
                                    <i class="ki-duotone ki-eye"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                </button>
                            </span>
                        </div>
                    </div>
                    <!-- Actions -->
                    <div class="text-center pt-10">
                        <button type="reset" class="btn btn-light me-3" data-kt-users-modal-action="cancel" data-bs-dismiss="modal">{{ __('Discard') }}</button>
                        <button type="submit" class="btn btn-primary" data-kt-users-modal-action="submit">
                            <span class="indicator-label">{{ __('Submit') }}</span>
                            <span class="indicator-progress">{{ __('Please wait...') }} <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
