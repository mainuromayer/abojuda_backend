<div class=" container-xxl " data-select2-id="select2-data-198-e71y">
    <!--begin::Card-->
    <div class="card" data-select2-id="select2-data-197-m5it">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6" data-select2-id="select2-data-196-qcil">
            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <!--begin::Toolbar-->
                <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base"
                     data-select2-id="select2-data-195-9exk">

                    <!--begin::Add user-->
                    <a href="{{ route('dynamic-pages.index') }}" class="btn btn-primary">

                        <i class="ki-duotone ki-entrance-right fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>

                        List of pages
                    </a>
                    <!--end::Add user-->
                </div>
                <!--end::Toolbar-->
            </div>
            <!--end::Card toolbar-->
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body py-4">
            <!--begin::Form-->
            <form id="add-page-form" method="POST" enctype="multipart/form-data" class="form fv-plugins-bootstrap5 fv-plugins-framework">
                @csrf
                <!--begin::Scroll-->
                <div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_add_page_scroll"
                     data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-max-height="auto"
                     data-kt-scroll-dependencies="#kt_modal_add_page_header"
                     data-kt-scroll-wrappers="#kt_modal_add_page_scroll" data-kt-scroll-offset="300px">
                    <!--begin::Input group-->
                    <div class="fv-row mb-6">
                        <label class="required form-label">Title</label>
                        <input class="form-control form-control-solid" type="text" name="title" placeholder="Enter Title" value="{{ old('title') }}">
                    </div>
                    <!--end::Input group-->

                    <!--begin::Input group-->
                    <div class="fv-row mb-6">
                        <label class="required form-label">Content</label>
                        <!--begin::Editor-->
                        <textarea class="form-control" name="text" id="kt_add_page_content"></textarea>
                        <!--end::Editor-->
                        <div class="fv-plugins-message-container invalid-feedback"></div>
                        <div class="text-muted fs-7">Set the content for the page using the rich text editor.</div>
                    </div>
                    <!--end::Input group-->

                </div>
                <!--end::Scroll-->
                <!--begin::Actions-->
                <div class="text-center pt-10">
                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                    <button type="submit" class="btn btn-primary" data-kt-users-modal-action="submit">
                        <span class="indicator-label">Submit</span>
                        <span class="indicator-progress">Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
                <!--end::Actions-->
            </form>
            <!--end::Form-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->
</div>
