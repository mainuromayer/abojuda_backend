<div class="modal fade" id="kt_modal_add_category" tabindex="-1" style="display: none;" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header" id="kt_modal_add_category_header">
                <!--begin::Modal title-->
                <h2 class="fw-bold" id="category_modal_title">{{ __('Add Category') }}</h2>
                <!--end::Modal title-->

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
                <!--end::Close-->
            </div>
            <!--end::Modal header-->

            <!--begin::Modal body-->
            <div class="modal-body px-5 my-7">
                <!--begin::Form-->
                <form id="kt_modal_add_category_form" method="POST" enctype="multipart/form-data" class="form fv-plugins-bootstrap5 fv-plugins-framework">
                    @csrf
                    <input type="hidden" name="category_id" id="category_id">
                    
                    <!--begin::Scroll-->
                    <div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_add_category_scroll" data-kt-scroll="true"
                         data-kt-scroll-activate="true" data-kt-scroll-max-height="auto"
                         data-kt-scroll-dependencies="#kt_modal_add_category_header"
                         data-kt-scroll-wrappers="#kt_modal_add_category_scroll"
                         data-kt-scroll-offset="300px">
                        
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <label class="required fw-semibold fs-6 mb-2">{{ __('Category Name') }}</label>
                            <input type="text" name="name" id="category_name" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="{{ __('Category Name') }}" required />
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="fv-row mb-7 d-flex flex-column align-items-start">
                            <label class="fw-semibold fs-6 mb-2">{{ __('Category Icon') }}</label>
                            
                            <style>
                                .image-input-placeholder {
                                    background-image: url({{ asset('backend/assets/media/svg/files/blank-image.svg') }});
                                }
                                [data-bs-theme="dark"] .image-input-placeholder {
                                    background-image: url({{ asset('backend/assets/media/svg/files/blank-image-dark.svg') }});
                                }
                            </style>

                            <!--begin::Image input-->
                            <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true" id="category_icon_image_input">
                                <!--begin::Preview existing icon-->
                                <div class="image-input-wrapper w-125px h-125px" id="category_icon_wrapper" style="background-image: url({{ asset('backend/assets/media/svg/files/blank-image.svg') }});"></div>
                                <!--end::Preview existing icon-->

                                <!--begin::Label-->
                                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="{{ __('Change icon') }}">
                                    <i class="ki-duotone ki-pencil fs-7">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <!--begin::Inputs-->
                                    <input type="file" name="icon" id="category_icon" accept=".png, .jpg, .jpeg, .svg" />
                                    <input type="hidden" name="icon_remove" id="icon_remove" value="0" />
                                    <!--end::Inputs-->
                                </label>
                                <!--end::Label-->

                                <!--begin::Cancel-->
                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="{{ __('Cancel icon') }}">
                                    <i class="ki-duotone ki-cross fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </span>
                                <!--end::Cancel-->

                                <!--begin::Remove-->
                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="{{ __('Remove icon') }}" id="remove_category_icon_btn">
                                    <i class="ki-duotone ki-cross fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </span>
                                <!--end::Remove-->
                            </div>
                            <!--end::Image input-->
                            <div class="form-text mt-2">{{ __('Allowed file types: png, jpg, jpeg, svg.') }}</div>
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <label class="fw-semibold fs-6 mb-2">{{ __('Short Description') }}</label>
                            <textarea name="shot_desc" id="category_description" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="{{ __('Enter short description...') }}" rows="3"></textarea>
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <label class="required fw-semibold fs-6 mb-2">{{ __('Status') }}</label>
                            <select name="status" id="category_status" class="form-select form-select-solid" required>
                                <option value="active">{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                            </select>
                        </div>
                        <!--end::Input group-->

                    </div>
                    <!--end::Scroll-->

                    <!--begin::Actions-->
                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">{{ __('Discard') }}</button>
                        <button type="submit" class="btn btn-primary" id="category_submit_btn">
                            <span class="indicator-label">{{ __('Submit') }}</span>
                            <span class="indicator-progress">{{ __('Please wait...') }} 
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
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
