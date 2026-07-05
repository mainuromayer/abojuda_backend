<div class="aside-menu flex-column-fluid ps-3 pe-1">
    <!--begin::Aside Menu-->

    <!--begin::Menu-->
    <div class="menu menu-sub-indention menu-column menu-rounded menu-title-gray-600 menu-icon-gray-400 menu-active-bg menu-state-primary menu-arrow-gray-500 fw-semibold fs-6 my-5 mt-lg-2 mb-lg-0"
        id="kt_aside_menu" data-kt-menu="true">

        <div class="hover-scroll-y mx-4" id="kt_aside_menu_wrapper" data-kt-scroll="true"
            data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto"
            data-kt-scroll-wrappers="#kt_aside_menu" data-kt-scroll-offset="20px"
            data-kt-scroll-dependencies="#kt_aside_logo, #kt_aside_footer">


            <!--begin:Menu item-->
            <div class="menu-item">
                <a class="menu-link {{ request()->routeIs(['dashboard']) ? 'active' : '' }}"
                    href="{{ route('dashboard') }}">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-element-11 fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                    </span>
                    <span class="menu-title">Dashboards</span>
                </a>
            </div>
            <!--end:Menu item-->


            <!--begin:Menu item-->
            <div class="menu-item pt-5">
                <div class="menu-content">
                    <span class="fw-bold text-muted text-uppercase fs-7">Site Management</span>
                </div>
            </div><!--end:Menu item-->

            <!--begin:Menu item-->
            <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ request()->routeIs('user.*', 'user.roles.*', 'role.*', 'roles.*') ? 'show' : '' }}">
                <!--begin:Menu link-->
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-shield-tick fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </span>
                    <span class="menu-title">User Management</span>
                    @php
                        $pendingUsersCount = \App\Models\User::where('status', 'pending_delete')->count();
                    @endphp
                    @if($pendingUsersCount > 0)
                        <span class="badge badge-danger fs-8 fw-bold px-2 py-1 ms-2">{{ $pendingUsersCount }}</span>
                    @endif
                    <span class="menu-arrow"></span>
                </span>
                <!--end:Menu link-->

                <!--begin:Menu sub-->
                <div class="menu-sub menu-sub-accordion"
                    style="{{ request()->routeIs('user.*', 'user.roles.*', 'role.*', 'roles.*') ? '' : 'display: none; overflow: hidden;' }}">

                    <!--begin:Users List-->
                    <div class="menu-item mb-1">
                        <a class="menu-link {{ request()->routeIs('user.index') ? 'active' : '' }}"
                            href="{{ route('user.index') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Users List</span>
                        </a>
                    </div>
                    <!--end:Users List-->

                </div>
                <!--end:Menu sub-->
            </div>
            <!--end:Menu item-->


            <!--begin:Category Management-->
            <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ request()->routeIs('category.*') ? 'show' : '' }}">
                <!--begin:Menu link-->
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-element-plus fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </span>
                    <span class="menu-title">Category Management</span>
                    <span class="menu-arrow"></span>
                </span>
                <!--end:Menu link-->

                <!--begin:Menu sub-->
                <div class="menu-sub menu-sub-accordion"
                    style="{{ request()->routeIs('category.*') ? '' : 'display: none; overflow: hidden;' }}">

                    <!--begin:Categories List-->
                    <div class="menu-item mb-1">
                        <a class="menu-link {{ request()->routeIs('category.index') ? 'active' : '' }}"
                            href="{{ route('category.index') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Categories List</span>
                        </a>
                    </div>
                    <!--end:Categories List-->

            </div>
            <!--end:Menu sub-->
        </div>
        <!--end:Category Management-->

        <!--begin:Post Management-->
        <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ request()->routeIs('post.*') ? 'show' : '' }}">
            <!--begin:Menu link-->
            <span class="menu-link">
                <span class="menu-icon">
                    <i class="ki-duotone ki-abstract-26 fs-2">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                </span>
                <span class="menu-title">Post Management</span>
                @php
                    $pendingCount = \App\Models\Post::where('status', 'pending')->count();
                @endphp
                @if($pendingCount > 0)
                    <span class="badge badge-warning fs-8 fw-bold px-2 py-1 ms-2">{{ $pendingCount }}</span>
                @endif
                <span class="menu-arrow"></span>
            </span>
            <!--end:Menu link-->

            <!--begin:Menu sub-->
            <div class="menu-sub menu-sub-accordion"
                style="{{ request()->routeIs('post.*') ? '' : 'display: none; overflow: hidden;' }}">

                <!--begin:Posts List-->
                <div class="menu-item mb-1">
                    <a class="menu-link {{ request()->routeIs('post.index') ? 'active' : '' }}"
                        href="{{ route('post.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">All Posts</span>
                    </a>
                </div>
                <!--end:Posts List-->

            </div>
            <!--end:Menu sub-->
        </div>
        <!--end:Post Management-->


            <!--begin:Menu item-->
            <div class="menu-item pt-5">
                <div class="menu-content">
                    <span class="fw-bold text-muted text-uppercase fs-7">Setting Management</span>
                </div>
            </div><!--end:Menu item-->


            <!--begin:setting item-->
            <div data-kt-menu-trigger="click"
                class="menu-item menu-accordion {{ request()->routeIs('profile.*', 'setting.*', 'smtp.*') ? 'show' : '' }}">
                <!--begin:Menu link-->
                <span class="menu-link ">
                    <span class="menu-icon">
                        <i class="fa-solid fa-gear fs-2"></i>
                    </span>
                    <span class="menu-title">Setting</span>
                    <span class="menu-arrow"></span>
                </span>
                <!--end:Menu link-->

                <!--begin:Menu sub-->
                <div class="menu-sub menu-sub-accordion">
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('profile.edit') ? 'active menu-active-bg' : '' }}"
                            href="{{ route('profile.edit') }}">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title">My Profile</span>
                        </a>
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('setting.index') ? 'active menu-active-bg' : '' }}"
                            href="{{ route('setting.index') }}">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title">Account Setting</span>
                        </a>
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('smtp.index') ? 'active menu-active-bg' : '' }}"
                            href="{{ route('smtp.index') }}">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title">Smtp Setting</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                </div>
                <!--end:Menu sub-->
            </div>
            <!--end:setting item-->


        </div>
    </div>
    <!--end::Menu-->
</div>
