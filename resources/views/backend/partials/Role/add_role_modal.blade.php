<div class="modal fade" id="kt_modal_add_role" tabindex="-1" style="display: none;" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header" id="kt_modal_add_user_header">
                <!--begin::Modal title-->
                <h2 class="fw-bold">{{ __('Add New Role') }}</h2>
                <!--end::Modal title-->

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-kt-users-modal-action="close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
                <!--end::Close-->
            </div>
            <!--end::Modal header-->

            <!--begin::Modal body-->
            <div class="modal-body px-5 my-7">
                <!--begin::Form-->
                <form id="add-role-form" method="POST" enctype="multipart/form-data"
                    class="form fv-plugins-bootstrap5 fv-plugins-framework">
                    @csrf
                    <!--begin::Scroll-->
                    <div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_add_user_scroll"
                        data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-max-height="auto"
                        data-kt-scroll-dependencies="#kt_modal_add_user_header"
                        data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px"
                        style="max-height: 177px;">


                        <!--begin::Input group-->
                        <div class="d-flex flex-column flex-md-row gap-5 mb-6">
                            <!-- first name-->
                            <div class="fv-row flex-row-fluid">
                                <label class="required form-label">{{ __('Enter Role Name') }}</label>
                                <input class="form-control form-control-solid" type="text" name="role"
                                    placeholder="{{ __('Enter Role Name') }}" value="{{ old('role') }}">
                            </div>
                        </div>

                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="fv-row mb-7 fv-plugins-icon-container">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">{{ __('Select Permissions') }}</label>
                            <!--end::Label-->

                            <div id="permissions-container"></div>

                            <div
                                class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                            </div>
                        </div>
                        <!--end::Input group-->
                    </div>
                    <!--end::Scroll-->

                    <!--begin::Actions-->
                    <div class="text-center pt-10">
                        <button type="reset" class="btn btn-light me-3" data-kt-users-modal-action="cancel">
                            {{ __('Discard') }}
                        </button>

                        <button type="submit" class="btn btn-primary" data-kt-users-modal-action="submit">
                            <span class="indicator-label">
                                {{ __('Submit') }}
                            </span>
                            <span class="indicator-progress">
                                {{ __('Please wait...') }} <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
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





<!--- begin:edit modal-->
<!-- Edit Role Modal -->
<div class="modal fade" id="kt_modal_edit_role" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">{{ __('Edit Role') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body px-5 my-7">
                <form id="edit-role-form" method="POST">
                    @csrf
                    <input type="hidden" name="role_id" id="edit_role_id">

                    <div class="mb-6">
                        <label class="form-label">{{ __('Role Name') }}</label>
                        <input type="text" class="form-control form-control-solid" name="role" id="edit_role_name"
                            placeholder="{{ __('Enter Role Name') }}">
                    </div>

                    <div class="mb-7">
                        <label class="form-label">{{ __('Permissions') }}</label>
                        <div id="edit-permissions-container"></div>
                    </div>

                    <div class="text-center pt-10">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">{{ __('Discard') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">{{ __('Update') }}</span>
                            <span class="indicator-progress">{{ __('Please wait...') }}
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!----end:modal dialog-->
