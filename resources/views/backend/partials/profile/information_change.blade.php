<div id="kt_account_settings_profile_details" class="collapse show">
    <!--begin::Form-->
    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="form fv-plugins-bootstrap5 fv-plugins-framework" novalidate="novalidate">
        @csrf
        @method('PATCH')
        <!--begin::Card body-->
        <div class="card-body border-top p-9">
            <!--begin::Input group-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('Avatar') }}</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8">
                    <!--begin::Image input-->
                    <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url({{ asset('backend/assets') ."/media/svg/avatars/blank.svg" }})">
                        <!--begin::Preview existing avatar-->
                        <div class="image-input-wrapper w-125px h-125px" style="background-image: url(  {{ $imageUrl ??  asset('backend/assets')."/media/avatars/300-1.jpg" }})"></div>
                        <!--end::Preview existing avatar-->

                        <!--begin::Label-->
                        <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" aria-label="{{ __('Change avatar') }}" data-bs-original-title="{{ __('Change avatar') }}" data-kt-initialized="1">
                            <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                            <!--begin::Inputs-->
                            <input type="file" name="avatar" id="avatar_file_input" accept=".png, .jpg, .jpeg">
                            <input type="hidden" name="avatar_remove">
                            <input type="hidden" name="cropped_avatar" id="cropped_avatar">
                            <!--end::Inputs-->
                        </label>
                        <!--end::Label-->

                        <!--begin::Cancel-->
                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" aria-label="{{ __('Cancel avatar') }}" data-bs-original-title="{{ __('Cancel avatar') }}" data-kt-initialized="1">
                            <i class="ki-duotone ki-cross fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </span>
                        <!--end::Cancel-->

                        <!--begin::Remove-->
                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" aria-label="{{ __('Remove avatar') }}" data-bs-original-title="{{ __('Remove avatar') }}" data-kt-initialized="1">
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
                <!--end::Col-->
            </div>
            <!--end::Input group-->

            <!--begin::Input group-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">{{ __('Full Name') }}</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row fv-plugins-icon-container">
                    <input type="text" name="name" class="form-control form-control-lg form-control-solid" placeholder="{{ __('Full Name') }}" value="{{ old('name',$user?->name) }}">
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->

        </div>
        <!--end::Card body-->

        <!--begin::Actions-->
        <div class="card-footer d-flex justify-content-end py-6 px-9">
            <button type="reset" class="btn btn-light btn-active-light-primary me-2">{{ __('Discard') }}</button>
            <button type="submit" class="btn btn-primary" id="kt_account_profile_details_submit">{{ __('Save Changes') }}</button>
        </div>
        <!--end::Actions-->
        <input type="hidden"></form>
    <!--end::Form-->
</div>

<!-- Avatar Crop Modal -->
<div class="modal fade" id="avatarCropModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">{{ __('Crop & Position Avatar') }}</h5>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body">
                <div class="image-container d-flex justify-content-center align-items-center" style="max-height: 450px; overflow: hidden; background-color: #f7f7f7; border-radius: 8px; padding: 10px;">
                    <img id="avatarCropImage" src="" style="max-width: 100%; max-height: 400px; display: block;">
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div>
                    <button type="button" class="btn btn-sm btn-icon btn-outline btn-outline-dashed btn-outline-default me-1" id="btnZoomIn" title="{{ __('Zoom In') }}">
                        <i class="fa fa-search-plus fs-4"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-icon btn-outline btn-outline-dashed btn-outline-default me-1" id="btnZoomOut" title="{{ __('Zoom Out') }}">
                        <i class="fa fa-search-minus fs-4"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-icon btn-outline btn-outline-dashed btn-outline-default me-1" id="btnRotateLeft" title="{{ __('Rotate Left') }}">
                        <i class="fa fa-undo fs-4"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-icon btn-outline btn-outline-dashed btn-outline-default" id="btnRotateRight" title="{{ __('Rotate Right') }}">
                        <i class="fa fa-redo fs-4"></i>
                    </button>
                </div>
                <div>
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="btnSaveCrop">{{ __('Save Crop') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
