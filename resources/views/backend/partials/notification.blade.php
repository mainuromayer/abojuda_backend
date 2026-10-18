@php
    $unreadCount = \App\Models\SystemNotification::where('is_read', false)->count();
    $notifications = \App\Models\SystemNotification::latest()->take(10)->get();
@endphp
<div class="d-flex align-items-center ms-1 ms-lg-2">
    <!--begin::Menu wrapper-->
    <div class="btn btn-icon btn-active-light-primary w-30px h-30px w-md-40px h-md-40px position-relative"
        data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent"
        data-kt-menu-placement="bottom-end">
        <i class="fa-solid fa-bell fs-2 text-gray-600"></i>
        @if($unreadCount > 0)
            <span class="badge badge-circle badge-danger position-absolute translate-middle start-75 top-25 fs-9 h-18px w-18px d-flex align-items-center justify-content-center" style="font-size: 0.75rem;">{{ $unreadCount }}</span>
        @endif
    </div>

    <!--begin::Menu-->
    <div class="menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-375px" data-kt-menu="true"
        id="kt_menu_notifications" style="">
        <!--begin::Heading-->
        <div class="d-flex flex-column bgi-no-repeat rounded-top"
            style="background-image: url({{ asset('backend/assets/media/misc/menu-header-bg.jpg') }})">
            <!--begin::Title-->
            <h3 class="text-white fw-semibold px-9 mt-10 mb-6 d-flex align-items-center justify-content-between w-100">
                <span>{{ __('Notifications') }} <span class="fs-8 opacity-75 ps-3" id="unread_count_text">{{ $unreadCount }} {{ __('unread') }}</span></span>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" class="fs-9 text-white opacity-75 opacity-state-100 text-hover-primary text-decoration-underline" id="mark_all_notifications_read_btn" style="{{ $unreadCount > 0 ? '' : 'display: none;' }}">{{ __('Mark all as read') }}</a>
                    <a href="#" class="fs-9 text-white opacity-75 opacity-state-100 text-hover-danger text-decoration-underline" id="clear_all_notifications_btn" style="{{ count($notifications) > 0 ? '' : 'display: none;' }}">{{ __('Clear all') }}</a>
                </div>
            </h3>
            <!--end::Title-->

            <!--begin::Tabs-->
            <ul class="nav nav-line-tabs nav-line-tabs-2x nav-stretch fw-semibold px-9" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link text-white opacity-75 opacity-state-100 pb-4 active" data-bs-toggle="tab"
                        href="#kt_topbar_notifications_1" aria-selected="false" tabindex="-1" role="tab">{{ __('Alerts') }}</a>
                </li>
            </ul>
            <!--end::Tabs-->
        </div>
        <!--end::Heading-->

        <!--begin::Tab content-->
        <div class="tab-content">
            <!--begin::Tab panel-->
            <div class="tab-pane fade show active" id="kt_topbar_notifications_1" role="tabpanel">
                <!--begin::Items-->
                <div class="scroll-y mh-325px my-5 px-8">
                    @forelse($notifications as $notification)
                    <!--begin::Item-->
                    @php
                        $notifUrl = $notification->target_url;
                        if ($notifUrl) {
                            $parsed = parse_url($notifUrl);
                            if (!empty($parsed['path'])) {
                                $notifUrl = $parsed['path'] . (!empty($parsed['query']) ? '?' . $parsed['query'] : '');
                            }
                        }

                        if ($notification->type === 'contact_support' && (!$notifUrl || !str_contains($notifUrl, 'id='))) {
                            $supportId = null;
                            if (preg_match('/from\s+([^\s,.]+@[^\s,]+)/i', $notification->content, $matches)) {
                                $matchedSupport = \App\Models\ContactSupport::where('email', trim($matches[1]))->latest()->first();
                                if ($matchedSupport) {
                                    $supportId = $matchedSupport->id;
                                }
                            }
                            if (!$supportId && $notification->created_at) {
                                $matchedSupport = \App\Models\ContactSupport::whereBetween('created_at', [
                                    $notification->created_at->copy()->subHours(2),
                                    $notification->created_at->copy()->addHours(2)
                                ])->first() ?? \App\Models\ContactSupport::latest()->first();
                                if ($matchedSupport) {
                                    $supportId = $matchedSupport->id;
                                }
                            }
                            $notifUrl = $supportId 
                                ? route('contact-support.index', ['id' => $supportId], false) 
                                : route('contact-support.index', [], false);
                        }
                    @endphp
                    <div class="d-flex flex-stack py-4 w-100 ps-3 mb-2 rounded notification-item {{ !$notification->is_read ? 'border-start border-4 border-danger bg-light-danger bg-opacity-20' : '' }}" id="notification_item_{{ $notification->id }}">
                        <a href="{{ $notifUrl ?? '#' }}" class="d-flex align-items-center text-gray-800 text-hover-primary text-decoration-none w-100 me-3 notification-link" data-id="{{ $notification->id }}" data-type="{{ $notification->type }}">
                            <!--begin::Symbol-->
                            <div class="symbol symbol-35px me-4">
                                @if($notification->type === 'contact_support')
                                <span class="symbol-label bg-light-primary">
                                    <i class="ki-duotone ki-message-text-2 fs-2 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                </span>
                                @else
                                <span class="symbol-label bg-light-danger">
                                    <i class="ki-duotone ki-information fs-2 text-danger"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                </span>
                                @endif
                            </div>
                            <!--end::Symbol-->

                            <!--begin::Title-->
                            <div class="mb-0 me-2">
                                <span class="fs-6 fw-semibold">{{ $notification->title }}</span>
                                <div class="text-gray-400 fs-7" style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $notification->content }}
                                </div>
                            </div>
                            <!--end::Title-->
                        </a>

                        <!--begin::Actions-->
                        <div class="d-flex align-items-center">
                            <span class="badge badge-light fs-8 me-2">{{ $notification->created_at ? $notification->created_at->diffForHumans(null, true) : '' }}</span>
                            <button class="btn btn-icon btn-sm btn-light-success mark-notification-read-btn" data-id="{{ $notification->id }}" title="Mark as read" style="width: 20px; height: 20px; min-width: 20px; {{ $notification->is_read ? 'display: none;' : '' }}">
                                <i class="ki-duotone ki-check fs-8"></i>
                            </button>
                        </div>
                        <!--end::Actions-->
                    </div>
                    <!--end::Item-->
                    @empty
                    <div class="text-center text-muted py-8 fs-7">
                        No new notifications
                    </div>
                    @endforelse
                </div>
                <!--end::Items-->
            </div>
            <!--end::Tab panel-->
        </div>
        <!--end::Tab content-->
    </div>
    <!--end::Menu--> <!--end::Menu wrapper-->
</div>

@push('script')
<script>
$(document).ready(function() {
    // When clicking a notification item link
    $(document).on('click', '.notification-link', function(e) {
        var notifId = $(this).data('id');
        var notifType = $(this).data('type');
        var href = $(this).attr('href');

        // Mark as read asynchronously
        if (notifId) {
            var readUrl = "{{ route('notifications.read', ['id' => '___id___'], false) }}".replace('___id___', notifId);
            if (navigator.sendBeacon) {
                var formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                navigator.sendBeacon(readUrl, formData);
            } else {
                $.post(readUrl, { _token: '{{ csrf_token() }}' });
            }
        }

        // If already on the contact supports page and clicking a contact support notification
        if (notifType === 'contact_support' && href && window.location.pathname.indexOf('contact-supports') !== -1) {
            var match = href.match(/[?&]id=([^&]+)/);
            if (match && match[1]) {
                e.preventDefault();
                // Close the notification dropdown
                var menuEl = document.querySelector('#kt_menu_notifications');
                if (menuEl && typeof KTMenu !== 'undefined') {
                    var menu = KTMenu.getInstance(menuEl);
                    if (menu) menu.hide();
                }
                window.history.pushState({}, '', href);
                if (typeof window.openSupportDetails === 'function') {
                    window.openSupportDetails(match[1]);
                }
                return;
            }
        }
    });

    // Mark single notification as read button
    $(document).on('click', '.mark-notification-read-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var id = $(this).data('id');
        var btn = $(this);
        var item = $('#notification_item_' + id);
        var url = "{{ route('notifications.read', ['id' => '___id___'], false) }}".replace('___id___', id);
        
        $.post(url, { _token: '{{ csrf_token() }}' }, function(res) {
            if (res.success) {
                item.removeClass('border-start border-4 border-danger bg-light-danger bg-opacity-20');
                btn.hide();
                
                var badges = $('.badge-circle.badge-danger');
                var count = parseInt(badges.first().text()) - 1;
                if (count > 0) {
                    badges.text(count);
                    $('#unread_count_text').text(count + ' unread');
                } else {
                    badges.remove();
                    $('#unread_count_text').text('0 unread');
                    $('#mark_all_notifications_read_btn').hide();
                }
            }
        });
    });

    // Mark all notifications as read
    $(document).on('click', '#mark_all_notifications_read_btn', function(e) {
        e.preventDefault();
        var url = "{{ route('notifications.read-all', [], false) }}";
        $.post(url, { _token: '{{ csrf_token() }}' }, function(res) {
            if (res.success) {
                $('.notification-item').removeClass('border-start border-4 border-danger bg-light-danger bg-opacity-20');
                $('.mark-notification-read-btn').hide();
                $('.badge-circle.badge-danger').remove();
                $('#unread_count_text').text('0 unread');
                $('#mark_all_notifications_read_btn').hide();
            }
        });
    });

    // Clear all notifications
    $(document).on('click', '#clear_all_notifications_btn', function(e) {
        e.preventDefault();
        var url = "{{ route('notifications.clear', [], false) }}";
        $.post(url, { _token: '{{ csrf_token() }}' }, function(res) {
            if (res.success) {
                $('.notification-item').fadeOut(300, function() {
                    $(this).remove();
                });
                $('.badge-circle.badge-danger').remove();
                $('#unread_count_text').text('0 unread');
                $('#mark_all_notifications_read_btn').hide();
                $('#clear_all_notifications_btn').hide();
                $('.scroll-y.mh-325px').html('<div class="text-center text-muted py-8 fs-7">No new notifications</div>');
            }
        });
    });
});
</script>
@endpush
