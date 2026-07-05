@extends('backend.app')

@section('title', 'Admin Dashboard')

@section('content')
    <!--begin::Content-->
    <div class="content fs-6 d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Toolbar-->
        <div class="toolbar" id="kt_toolbar">
            <div class=" container-fluid  d-flex flex-stack flex-wrap flex-sm-nowrap">
                <!--begin::Info-->
                <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                    <!--begin::Title-->
                    <h1 class="text-dark fw-bold my-1 fs-2">Dashboard</h1>
                    <!--end::Title-->

                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="" class="text-muted text-hover-primary">Home</a>
                        </li>
                        <li class="breadcrumb-item text-dark">Metrics Overview</li>
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Info-->
            </div>
        </div>
        <!--end::Toolbar-->

        <!--begin::Post-->
        <div class="post fs-6 d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            <div class=" container-xxl ">

                <!--begin:row-->
                <div class="row g-6 g-xl-9">
                    <!--begin::Col-->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card h-100">
                            <div class="card-body d-flex justify-content-between align-items-start flex-column p-9">
                                <div class="symbol symbol-50px bg-light-primary p-2 rounded mb-4">
                                    <i class="fa-solid fa-users fs-2x text-primary"></i>
                                </div>
                                <div class="d-flex flex-column mb-2">
                                    <span class="fs-2hx fw-bold text-gray-800 lh-1 mb-1">{{ $metrics['total_users'] }}</span>
                                    <span class="fs-6 fw-semibold text-gray-400">Total Registered Users</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Col-->

                    <!--begin::Col-->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card h-100">
                            <div class="card-body d-flex justify-content-between align-items-start flex-column p-9">
                                <div class="symbol symbol-50px bg-light-success p-2 rounded mb-4">
                                    <i class="fa-solid fa-rectangle-ad fs-2x text-success"></i>
                                </div>
                                <div class="d-flex flex-column mb-2 w-100">
                                    <span class="fs-2hx fw-bold text-gray-800 lh-1 mb-1">{{ $metrics['total_posts'] }}</span>
                                    <span class="fs-6 fw-semibold text-gray-400">Total Listings / Posts</span>
                                    
                                    <div class="d-flex flex-column mt-3 gap-1 fs-7 fw-semibold text-gray-600 w-100">
                                        <div class="d-flex justify-content-between">
                                            <span>Active</span>
                                            <span class="badge badge-light-success fs-9">{{ $metrics['active_posts'] }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Pending</span>
                                            <span class="badge badge-light-warning fs-9">{{ $metrics['pending_posts'] }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Rejected</span>
                                            <span class="badge badge-light-danger fs-9">{{ $metrics['rejected_posts'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Col-->

                    <!--begin::Col-->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card h-100">
                            <div class="card-body d-flex justify-content-between align-items-start flex-column p-9">
                                <div class="symbol symbol-50px bg-light-warning p-2 rounded mb-4">
                                    <i class="fa-solid fa-list fs-2x text-warning"></i>
                                </div>
                                <div class="d-flex flex-column mb-2">
                                    <span class="fs-2hx fw-bold text-gray-800 lh-1 mb-1">{{ $metrics['total_categories'] }}</span>
                                    <span class="fs-6 fw-semibold text-gray-400">Listing Categories</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Col-->

                    <!--begin::Col-->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card h-100">
                            <div class="card-body d-flex justify-content-between align-items-start flex-column p-9">
                                <div class="symbol symbol-50px bg-light-danger p-2 rounded mb-4">
                                    <i class="fa-solid fa-triangle-exclamation fs-2x text-danger"></i>
                                </div>
                                <div class="d-flex flex-column mb-2 w-100">
                                    <span class="fs-2hx fw-bold text-gray-800 lh-1 mb-1">{{ $metrics['total_reports'] }}</span>
                                    <span class="fs-6 fw-semibold text-gray-400">Abuse Reports</span>
                                    
                                    <div class="d-flex flex-column mt-3 gap-1 fs-7 fw-semibold text-gray-600 w-100">
                                        <div class="d-flex justify-content-between">
                                            <span>Unread Reports</span>
                                            <span class="badge badge-light-danger fs-9">{{ $metrics['unread_reports'] }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Support Requests</span>
                                            <span class="badge badge-light-primary fs-9">{{ $metrics['total_supports'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Col-->
                </div>
                <!--end:row-->

            </div>
            <!--end::Container-->
        </div>
        <!--end::Post-->
    </div>
    <!--end::Content-->
@endsection
