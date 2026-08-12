<div class="modal fade" id="kt_modal_add_ad" tabindex="-1" style="display: none;" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header" id="kt_modal_add_ad_header">
                <!--begin::Modal title-->
                <h2 class="fw-bold" id="ad_modal_title">Add New Ad</h2>
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
                <form id="kt_modal_add_ad_form" method="POST" enctype="multipart/form-data" class="form fv-plugins-bootstrap5 fv-plugins-framework">
                    @csrf
                    <input type="hidden" name="ad_id" id="ad_id">
                    
                    <!--begin::Scroll-->
                    <div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_add_ad_scroll" data-kt-scroll="true"
                         data-kt-scroll-activate="true" data-kt-scroll-max-height="auto"
                         data-kt-scroll-dependencies="#kt_modal_add_ad_header"
                         data-kt-scroll-wrappers="#kt_modal_add_ad_scroll"
                         data-kt-scroll-offset="300px">
                        
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <label class="required fw-semibold fs-6 mb-2">Title</label>
                            <input type="text" name="title" id="ad_title" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Ad Title" required />
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <label class="required fw-semibold fs-6 mb-2">Media Type</label>
                            <select name="media_type" id="ad_media_type" class="form-select form-select-solid" required>
                                <option value="image">Image</option>
                                <option value="video">Video</option>
                            </select>
                        </div>
                        <!--end::Input group-->

                        <!--begin::Image Input group-->
                        <div class="fv-row mb-7 d-flex flex-column align-items-start" id="image_media_group">
                            <label class="required fw-semibold fs-6 mb-2">Image Thumbnail</label>
                            
                            <style>
                                .image-input-placeholder {
                                    background-image: url({{ asset('backend/assets/media/svg/files/blank-image.svg') }});
                                }
                                [data-bs-theme="dark"] .image-input-placeholder {
                                    background-image: url({{ asset('backend/assets/media/svg/files/blank-image-dark.svg') }});
                                }
                            </style>

                            <!--begin::Image input-->
                            <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true" id="ad_image_input">
                                <!--begin::Preview existing icon-->
                                <div class="image-input-wrapper w-150px h-150px" id="ad_image_wrapper" style="background-image: url({{ asset('backend/assets/media/svg/files/blank-image.svg') }});"></div>
                                <!--end::Preview existing icon-->

                                <!--begin::Label-->
                                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change image">
                                    <i class="ki-duotone ki-pencil fs-7">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <!--begin::Inputs-->
                                    <input type="file" name="media_image" id="ad_media_image" accept=".png, .jpg, .jpeg, .svg, .gif, .webp" />
                                    <input type="hidden" name="media_remove" id="media_remove" value="0" />
                                    <!--end::Inputs-->
                                </label>
                                <!--end::Label-->

                                <!--begin::Cancel-->
                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel image">
                                    <i class="ki-duotone ki-cross fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </span>
                                <!--end::Cancel-->

                                <!--begin::Remove-->
                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove image" id="remove_ad_image_btn">
                                    <i class="ki-duotone ki-cross fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </span>
                                <!--end::Remove-->
                            </div>
                            <!--end::Image input-->
                            <div class="form-text mt-2">Allowed file types: png, jpg, jpeg, svg, gif, webp.</div>
                        </div>
                        <!--end::Image Input group-->

                        <!--begin::Video Input group-->
                        <div class="fv-row mb-7 d-flex flex-column align-items-start d-none" id="video_media_group">
                            <label class="required fw-semibold fs-6 mb-2">Video File</label>
                            
                            <!--begin::Video input container-->
                            <div class="image-input image-input-outline image-input-placeholder" id="ad_video_input">
                                <div class="image-input-wrapper w-200px h-130px d-flex align-items-center justify-content-center bg-light rounded position-relative overflow-hidden" id="ad_video_wrapper">
                                    <video id="ad_video_preview" class="w-100 h-100 object-fit-cover d-none" controls></video>
                                    <div id="ad_video_placeholder" class="text-center p-3">
                                        <i class="ki-duotone ki-video fs-3x text-muted mb-1">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <div class="fs-8 text-muted">No video chosen</div>
                                    </div>
                                </div>

                                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-bs-toggle="tooltip" title="Upload Video">
                                    <i class="ki-duotone ki-pencil fs-7">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <input type="file" name="media_video" id="ad_media_video" accept=".mp4, .mov, .avi, .mkv" style="display:none;" />
                                </label>

                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" id="remove_ad_video_btn" data-bs-toggle="tooltip" title="Remove video">
                                    <i class="ki-duotone ki-cross fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </span>
                            </div>
                            <!--end::Video input container-->
                            <div class="form-text mt-2">Allowed file types: mp4, mov, avi, mkv. Max size: 20MB.</div>
                        </div>
                        <!--end::Video Input group-->

                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <label class="fw-semibold fs-6 mb-2">Back Link</label>
                            <input type="url" name="back_link" id="ad_back_link" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="https://example.com" />
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <label class="required fw-semibold fs-6 mb-2">Status</label>
                            <select name="status" id="ad_status" class="form-select form-select-solid" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <!--end::Input group-->

                    </div>
                    <!--end::Scroll-->

                    <!--begin::Actions-->
                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                        <button type="submit" class="btn btn-primary" id="ad_submit_btn">
                            <span class="indicator-label">Submit</span>
                            <span class="indicator-progress">Please wait... 
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
