<div class=" container-xxl ">
    <!--begin::Card-->
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i> 
                    <input type="text" data-kt-category-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="Search category">
                </div>
                <!--end::Search-->
            </div>
            <!--begin::Card title-->

            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <!--begin::Toolbar-->
                <div class="d-flex justify-content-end" data-kt-category-table-toolbar="base">
                    <!--begin::Add category-->
                    <button type="button" class="btn btn-primary" id="open_add_category_modal_btn">
                        <i class="ki-duotone ki-plus fs-2"></i> Add Category
                    </button>
                    <!--end::Add category-->
                </div>
                <!--end::Toolbar-->

                <!--begin::Modal - Add category-->
                @include('backend.partials.category.add')
                <!--end::Modal - Add category-->
            </div>
            <!--end::Card toolbar-->
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body py-4">
            <!--begin::Table-->
            <div id="kt_table_categories_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer" id="kt_table_categories">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-80px">Icon</th>
                                <th class="min-w-150px">Category Name</th>
                                <th class="min-w-120px">Slug</th>
                                <th class="min-w-200px">Description</th>
                                <th class="min-w-100px">Status</th>
                                <th class="min-w-150px">Created At</th>
                                <th class="text-end min-w-100px">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            <!-- Populated by DataTables via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <!--end::Table-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->
</div>
