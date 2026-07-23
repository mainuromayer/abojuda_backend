<div class=" container-xxl ">
    <!--begin::Card-->
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <h2>{{ $page->title }}</h2>
            </div>
            <!--end::Card title-->
            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('dynamic-pages.index') }}" class="btn btn-light me-3">
                        <i class="ki-duotone ki-entrance-right fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        List of pages
                    </a>
                    <a href="{{ route('dynamic-pages.edit', $page->id) }}" class="btn btn-primary">
                        <i class="ki-duotone ki-pencil fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Edit Page
                    </a>
                </div>
            </div>
            <!--end::Card toolbar-->
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body py-4">
            <!--begin::Details-->
            <div class="d-flex flex-column px-5 px-lg-10">
                <!--begin::Row-->
                <div class="row mb-7">
                    <!--begin::Label-->
                    <label class="col-lg-4 fw-semibold text-muted">Slug</label>
                    <!--end::Label-->
                    <!--begin::Col-->
                    <div class="col-lg-8">
                        <span class="fw-bold fs-6 text-gray-800">{{ $page->slug }}</span>
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Row-->


                <!--begin::Separator-->
                <div class="separator separator-dashed my-6"></div>
                <!--end::Separator-->

                <!--begin::Content Row-->
                <div class="d-flex flex-column mb-7">
                    <label class="fw-semibold text-muted mb-3">Page Content</label>
                    <div class="p-5 bg-light rounded border min-h-200px text-gray-800">
                        {!! $page->content !!}
                    </div>
                </div>
                <!--end::Content Row-->
            </div>
            <!--end::Details-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->
</div>
