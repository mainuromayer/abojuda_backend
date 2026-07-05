<div class=" container-xxl " data-select2-id="select2-data-198-e71y">
    <!--begin::Card-->
    <div class="card" data-select2-id="select2-data-197-m5it">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6" data-select2-id="select2-data-196-qcil">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span
                            class="path2"></span></i> <input type="text" data-kt-user-table-filter="search"
                        class="form-control form-control-solid w-250px ps-13" placeholder="Search user">
                </div>
                <!--end::Search-->
            </div>
            <!--begin::Card title-->

            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <!--begin::Toolbar-->
                <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base"
                    data-select2-id="select2-data-195-9exk">

                    <!--begin::Add user-->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#kt_modal_add_user">
                        <i class="ki-duotone ki-plus fs-2"></i> Add User
                    </button>
                    <!--end::Add user-->
                </div>
                <!--end::Toolbar-->

                <!--begin::Group actions-->
                <div class="d-flex justify-content-end align-items-center d-none"
                    data-kt-user-table-toolbar="selected">
                    <div class="fw-bold me-5">
                        <span class="me-2" data-kt-user-table-select="selected_count"></span> Selected
                    </div>

                    <button type="button" class="btn btn-danger" data-kt-user-table-select="delete_selected">
                        Delete Selected
                    </button>
                </div>
                <!--end::Group actions-->

                <!--begin::Modal - Add task-->
                    @include('backend.partials.user.add')
                <!--end::Modal - Add task-->
            </div>
            <!--end::Card toolbar-->
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body py-4">

            <!--begin::Table-->
            <div id="kt_table_users_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer"
                        id="kt_table_users">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_table_users"
                                    rowspan="1" colspan="1"
                                    aria-label="User: activate to sort column ascending" style="width: 278.328px;">
                                    User</th>
                                <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_table_users"
                                    rowspan="1" colspan="1"
                                    aria-label="Role: activate to sort column ascending" style="width: 161.844px;">
                                    Role</th>
                                <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_table_users"
                                    rowspan="1" colspan="1"
                                    aria-label="Joined Date: activate to sort column ascending"
                                    style="width: 210.266px;">Joined Date</th>
                                <th class="min-w-100px sorting_disabled" rowspan="1" colspan="1"
                                    aria-label="Reports" style="width: 120px;">Reports</th>
                                <th class="text-end min-w-100px sorting_disabled" rowspan="1" colspan="1"
                                    aria-label="Actions" style="width: 132.484px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            <tr class="odd">

                            </tr>
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
