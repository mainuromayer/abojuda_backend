<?php
$user = auth()->user();
if ($user->avatar){
    $imageUrl = App\Helpers\helpers::generateTempURL($user->avatar,config('app.file_system'));
}
?>

<div class="d-flex align-items-center ms-2 ms-lg-3" id="kt_header_user_menu_toggle">
    <!--begin::Menu wrapper-->
    <div class="cursor-pointer symbol symbol-35px symbol-lg-35px" data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
        data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
        <img alt="Pic" src="{{ $imageUrl ?? asset('backend/assets') ."/media/avatars/300-1.jpg" }}" />
    </div>

    <!--begin::User account menu-->
    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px"
        data-kt-menu="true">
        <!--begin::Menu item-->
        <div class="menu-item px-3">
            <div class="menu-content d-flex align-items-center px-3">
                <!--begin::Avatar-->
                <div class="symbol symbol-50px me-5">
                    <img alt="Logo" src="{{ $imageUrl ?? asset('backend/assets') ."/media/avatars/300-1.jpg" }}" />
                </div>
                <!--end::Avatar-->

                <!--begin::Username-->
                <div class="d-flex flex-column">
                    <div class="fw-bold d-flex align-items-center fs-5">
                        {{ auth()->user()?->first_name }} <span class="badge badge-light-success fw-bold fs-8 px-2 py-1 ms-2">{{ auth()->user()?->role }}</span>
                    </div>

                    <a href="#" class="fw-semibold text-muted text-hover-primary fs-7">
                        {{ auth()->user()?->email }} </a>
                </div>
                <!--end::Username-->
            </div>
        </div>
        <!--end::Menu item-->

        <!--begin::Menu separator-->
        <div class="separator my-2"></div>
        <!--end::Menu separator-->

        <!--begin::Menu item-->
        <div class="menu-item px-5">
            <a href="{{ route('profile.edit') }}" class="menu-link px-5">
                {{ __('My Profile') }}
            </a>
        </div>
        <!--end::Menu item-->

        <!--begin::Menu separator-->
        <div class="separator my-2"></div>
        <!--end::Menu separator-->

        <!--begin::Menu item-->
        <div class="menu-item px-5 my-1">
            <a href="{{ route('setting.index') }}" class="menu-link px-5">
                {{ __('Account Settings') }}
            </a>
        </div>
        <!--end::Menu item-->

        <!--begin::Menu item-->
        <div class="menu-item px-5">
            <form action="{{ route('logout') }}" method="post">
                @csrf
                <button type="submit" class="menu-link px-5 border-0 bg-transparent text-start w-100">
                    {{ __('Sign Out') }}
                </button>
            </form>
        </div>
        <!--end::Menu item-->
    </div>
    <!--end::User account menu-->
    <!--end::Menu wrapper-->
</div>
