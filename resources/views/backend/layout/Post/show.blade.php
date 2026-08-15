@extends('backend.app')

@section('title', 'Post Details')

@push('style')
<style>
    .image-input-placeholder {
        background-image: url({{ asset('backend/assets/media/svg/files/blank-image.svg') }});
    }
    [data-bs-theme="dark"] .image-input-placeholder {
        background-image: url({{ asset('backend/assets/media/svg/files/blank-image-dark.svg') }});
    }
</style>
@endpush

@section('content')
<div class="content fs-6 d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="toolbar" id="kt_toolbar">
        <div class="container-fluid d-flex flex-stack flex-wrap flex-sm-nowrap">
            <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                <h1 class="text-dark fw-bold my-1 fs-2">Post Details</h1>
                <ul class="breadcrumb fw-semibold fs-base my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                    </li>
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('post.index') }}" class="text-muted text-hover-primary">Posts</a>
                    </li>
                    <li class="breadcrumb-item text-dark">View</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if($post->status === 'pending')
                    <button class="btn btn-success btn-sm approve-btn" data-id="{{ $post->id }}">
                        <i class="ki-duotone ki-check fs-4"></i> Approve
                    </button>
                    <button class="btn btn-danger btn-sm reject-btn" data-id="{{ $post->id }}">
                        <i class="ki-duotone ki-cross fs-4"></i> Reject
                    </button>
                @elseif($post->status === 'active')
                    <span class="badge badge-light-success fs-6 px-4 py-3">Active</span>
                @elseif($post->status === 'rejected')
                    <span class="badge badge-light-danger fs-6 px-4 py-3">Rejected</span>
                @endif
                <a href="{{ route('post.index') }}" class="btn btn-secondary btn-sm">
                    <i class="ki-duotone ki-arrow-left fs-4"></i> Back
                </a>
            </div>
        </div>
    </div>

    <div class="post fs-6 d-flex flex-column-fluid" id="kt_post">
        <div class="container-fluid">
            <div class="row g-5">

                {{-- Left column: main info --}}
                <div class="col-xl-8">
                    {{-- Reports Section --}}
                    @php
                        $postReports = \App\Models\Report::where('post_id', $post->id)->with('user')->get();
                        $userReports = \App\Models\Report::where('reported_user_id', $post->user_id)->with('user')->get();
                    @endphp
                    @if($postReports->count() > 0 || $userReports->count() > 0)
                    <div class="card border border-warning mb-5">
                        <div class="card-header border-0 pt-6">
                            <h3 class="card-title fw-bold text-warning">
                                <i class="ki-duotone ki-warning fs-3 text-warning me-2"><span class="path1"></span><span class="path2"></span></i> Reports Summary
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                             @if($postReports->count() > 0)
                                 <div class="d-flex align-items-center justify-content-between mb-3">
                                     <h4 class="fw-bold fs-6 mb-0">Listing Reports ({{ $postReports->count() }})</h4>
                                     <div class="d-flex align-items-center gap-2">
                                         <button type="button" class="btn btn-light-success btn-sm px-3 py-1 fs-8" id="mark_all_show_reports_read_btn" style="{{ $postReports->contains('is_read', false) ? '' : 'display: none;' }}">
                                             <i class="ki-duotone ki-double-check fs-5"><span class="path1"></span><span class="path2"></span></i> Mark all as read
                                         </button>
                                         <button type="button" class="btn btn-danger btn-sm px-3 py-1 fs-8" id="delete_selected_show_reports_btn" style="display: none;">
                                             <i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span class="path2"></span></i> Delete Selected
                                         </button>
                                     </div>
                                 </div>
                                 <div class="table-responsive mb-5">
                                     <table class="table table-row-dashed table-row-gray-300 fs-7 align-middle">
                                         <thead>
                                             <tr class="fw-bold text-muted">
                                                 <th class="w-10px pr-0">
                                                     <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                         <input class="form-check-input" type="checkbox" id="select_all_show_reports">
                                                     </div>
                                                 </th>
                                                 <th>Reporter</th>
                                                 <th>Reason</th>
                                                 <th>Additional Note</th>
                                                 <th>Date</th>
                                                 <th class="text-end">Action</th>
                                             </tr>
                                         </thead>
                                         <tbody>
                                             @foreach($postReports as $rep)
                                             @php
                                                 $isNew = !$rep->is_read;
                                                 $rowBg = $isNew ? 'background-color: rgba(241, 65, 108, 0.05);' : '';
                                             @endphp
                                             <tr id="show_report_row_{{ $rep->id }}" style="{{ $rowBg }}">
                                                 <td>
                                                     <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                         <input class="form-check-input select-show-report-checkbox" type="checkbox" data-id="{{ $rep->id }}">
                                                     </div>
                                                 </td>
                                                 <td class="fw-semibold">
                                                     {{ $rep->user->name ?? 'Anonymous' }}
                                                     @if($isNew)
                                                         <span class="badge badge-light-danger fs-9 ms-1 text-uppercase new-badge">NEW</span>
                                                     @endif
                                                     @if($rep->user?->email)
                                                         <button class="btn btn-icon btn-sm btn-light-primary ms-1 send-direct-email-btn" data-email="{{ $rep->user->email }}" data-name="{{ $rep->user->name }}" title="Email reporter" style="width: 24px; height: 24px;"><i class="ki-duotone ki-sms fs-6"><span class="path1"></span><span class="path2"></span></i></button>
                                                         <br><small class="text-muted">{{ $rep->user->email }}</small>
                                                     @endif
                                                 </td>
                                                 <td><span class="badge badge-light-danger">{{ $rep->reason }}</span></td>
                                                 <td>{{ $rep->other_note ?? '-' }}</td>
                                                 <td>{{ $rep->created_at->format('d M Y, h:i a') }}</td>
                                                 <td class="text-end">
                                                     @if($isNew)
                                                         <button class="btn btn-icon btn-sm btn-light-success mark-show-report-read-btn me-1" data-id="{{ $rep->id }}" title="Mark as read" style="width: 24px; height: 24px;">
                                                             <i class="ki-duotone ki-check fs-6"></i>
                                                         </button>
                                                     @endif
                                                     <button class="btn btn-icon btn-sm btn-light-danger delete-show-report-btn" data-id="{{ $rep->id }}" title="Delete report" style="width: 24px; height: 24px;">
                                                         <i class="ki-duotone ki-trash fs-6"><span class="path1"></span><span class="path2"></span></i>
                                                     </button>
                                                 </td>
                                             </tr>
                                             @endforeach
                                         </tbody>
                                     </table>
                                 </div>
                             @endif

                            @if($userReports->count() > 0)
                                <h4 class="fw-bold mb-3 fs-6 text-danger">User Reports ({{ $userReports->count() }} against owner)</h4>
                                <div class="table-responsive">
                                    <table class="table table-row-dashed table-row-gray-300 fs-7">
                                        <thead>
                                            <tr class="fw-bold text-muted">
                                                <th>Reporter</th>
                                                <th>Reason</th>
                                                <th>Additional Note</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($userReports as $rep)
                                            <tr>
                                                <td class="fw-semibold">
                                                    {{ $rep->user->name ?? 'Anonymous' }}
                                                    @if($rep->user?->email)
                                                        <button class="btn btn-icon btn-sm btn-light-primary ms-1 send-direct-email-btn" data-email="{{ $rep->user->email }}" data-name="{{ $rep->user->name }}" title="Email reporter" style="width: 24px; height: 24px;"><i class="ki-duotone ki-sms fs-6"><span class="path1"></span><span class="path2"></span></i></button>
                                                        <br><small class="text-muted">{{ $rep->user->email }}</small>
                                                    @endif
                                                </td>
                                                <td><span class="badge badge-light-danger">{{ $rep->reason }}</span></td>
                                                <td>{{ $rep->other_note ?? '-' }}</td>
                                                <td>{{ $rep->created_at->format('d M Y, h:i a') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Basic Info --}}
                    <div class="card mb-5">
                        <div class="card-header border-0 pt-6">
                            <h3 class="card-title fw-bold">Basic Information</h3>
                        </div>
                        <div class="card-body pt-0">
                                <div class="row g-4">
                                    {{-- Common Metadata --}}
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Category</label>
                                        <select name="category_id" class="form-select form-select-solid" disabled>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}" {{ $post->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    @php
                                        $catName = strtolower($post->category->name ?? '');
                                    @endphp

                                    {{-- Buy & Sell fields --}}
                                    @if($catName === 'buy & sell' || $post->category_id == 1)
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Title</label>
                                            <input type="text" name="title" class="form-control form-control-solid" value="{{ $post->title }}">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Description</label>
                                            <textarea name="description" class="form-control form-control-solid" rows="4">{{ $post->description }}</textarea>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Price</label>
                                            <input type="number" name="price" class="form-control form-control-solid" value="{{ $post->price }}" step="0.01" min="0">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Condition</label>
                                            <select name="condition[]" class="form-select form-select-solid" multiple data-control="select2" data-tags="true">
                                                @php
                                                    $selectedConditions = is_array($post->condition) ? $post->condition : [];
                                                    $defaultConditions = ['New', 'Like New', 'Excellent', 'Good', 'Fair'];
                                                    $allConditions = array_unique(array_merge($defaultConditions, $selectedConditions));
                                                @endphp
                                                @foreach($allConditions as $cond)
                                                    <option value="{{ $cond }}" {{ in_array($cond, $selectedConditions) ? 'selected' : '' }}>{{ $cond }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    {{-- Jobs fields --}}
                                    @if($catName === 'jobs' || $post->category_id == 2)
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Title</label>
                                            <input type="text" name="title" class="form-control form-control-solid" value="{{ $post->title }}">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Company Name</label>
                                            <input type="text" name="company_name" class="form-control form-control-solid" value="{{ $post->company_name }}">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Description</label>
                                            <textarea name="description" class="form-control form-control-solid" rows="4">{{ $post->description }}</textarea>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Job Type</label>
                                            <select name="job_type[]" class="form-select form-select-solid" multiple data-control="select2" data-tags="true">
                                                @php
                                                    $selectedJobs = is_array($post->job_type) ? $post->job_type : [];
                                                    $defaultJobs = ['Full Time', 'Part Time', 'Contract', 'Freelance', 'Internship', 'Temporary'];
                                                    $allJobs = array_unique(array_merge($defaultJobs, $selectedJobs));
                                                @endphp
                                                @foreach($allJobs as $job)
                                                    <option value="{{ $job }}" {{ in_array($job, $selectedJobs) ? 'selected' : '' }}>{{ $job }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    {{-- Business Directory fields --}}
                                    @if($catName === 'business directory' || $post->category_id == 3)
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Business Name</label>
                                            <input type="text" name="business_name" class="form-control form-control-solid" value="{{ $post->business_name }}">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Business Category</label>
                                            <input type="text" name="business_category" class="form-control form-control-solid" value="{{ $post->business_category }}">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Description</label>
                                            <textarea name="description" class="form-control form-control-solid" rows="4">{{ $post->description }}</textarea>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Website</label>
                                            <input type="text" name="website" class="form-control form-control-solid" value="{{ $post->website }}">
                                        </div>
                                    @endif

                                    {{-- Services fields --}}
                                    @if($catName === 'services' || $post->category_id == 4)
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Title</label>
                                            <input type="text" name="title" class="form-control form-control-solid" value="{{ $post->title }}">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Description</label>
                                            <textarea name="description" class="form-control form-control-solid" rows="4">{{ $post->description }}</textarea>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Service Area</label>
                                            <select name="service_area[]" class="form-select form-select-solid" multiple data-control="select2" data-tags="true">
                                                @php
                                                    $selectedAreas = is_array($post->service_area) ? $post->service_area : [];
                                                    $defaultAreas = ['Local', 'Regional', 'National', 'Remote'];
                                                    $allAreas = array_unique(array_merge($defaultAreas, $selectedAreas));
                                                @endphp
                                                @foreach($allAreas as $area)
                                                    <option value="{{ $area }}" {{ in_array($area, $selectedAreas) ? 'selected' : '' }}>{{ $area }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    {{-- Common fields --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Province</label>
                                        <input type="text" name="province" class="form-control form-control-solid" value="{{ $post->province }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">City</label>
                                        <input type="text" name="city" class="form-control form-control-solid" value="{{ $post->city }}">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Address</label>
                                        <input type="text" name="address" class="form-control form-control-solid" value="{{ $post->address }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Phone</label>
                                        <input type="text" name="phone" class="form-control form-control-solid" value="{{ $post->phone }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">WhatsApp</label>
                                        <input type="text" name="whatsapp" class="form-control form-control-solid" value="{{ $post->whatsapp }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Email</label>
                                        <input type="email" name="email" class="form-control form-control-solid" value="{{ $post->email }}">
                                    </div>

                                    {{-- Common Thumbnail --}}
                                    <div class="col-md-12 d-flex flex-column align-items-start">
                                        <label class="form-label fw-semibold mb-2">Thumbnail</label>
                                        <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true">
                                            <div class="image-input-wrapper w-125px h-125px" style="background-image: url({{ $post->thumbnail ? App\Helpers\helpers::generateTempURL($post->thumbnail, config('app.file_system')) : asset('backend/assets/media/svg/files/blank-image.svg') }});"></div>
                                            <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change thumbnail">
                                                <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                                <input type="file" name="thumbnail" accept=".png, .jpg, .jpeg, .svg" />
                                                <input type="hidden" name="thumbnail_remove" />
                                            </label>
                                            <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel thumbnail">
                                                <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                            </span>
                                            <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove thumbnail">
                                                <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                            </span>
                                        </div>
                                        <div class="form-text mt-2 text-muted">Allowed file types: png, jpg, jpeg, svg.</div>
                                    </div>

                                    {{-- Business Directory Logo --}}
                                    @if($catName === 'business directory' || $post->category_id == 3)
                                        <div class="col-md-12 d-flex flex-column align-items-start">
                                            <label class="form-label fw-semibold mb-2">Logo</label>
                                            <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true">
                                                <div class="image-input-wrapper w-125px h-125px" style="background-image: url({{ $post->logo ? App\Helpers\helpers::generateTempURL($post->logo, config('app.file_system')) : asset('backend/assets/media/svg/files/blank-image.svg') }});"></div>
                                                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change logo">
                                                    <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                                    <input type="file" name="logo" accept=".png, .jpg, .jpeg, .svg" />
                                                    <input type="hidden" name="logo_remove" />
                                                </label>
                                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel logo">
                                                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                                </span>
                                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove logo">
                                                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                                </span>
                                            </div>
                                            <div class="form-text mt-2 text-muted">Allowed file types: png, jpg, jpeg, svg.</div>
                                        </div>
                                    @endif

                                    {{-- Photos (Non-Jobs) --}}
                                    @if($catName !== 'jobs' && $post->category_id != 2)
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold mb-2">Photos</label>
                                            <div id="photos_container" class="d-flex flex-wrap gap-4">
                                                @php
                                                    $photos = is_array($post->photos) ? $post->photos : [];
                                                @endphp
                                                @foreach($photos as $index => $photo)
                                                    <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true">
                                                        <div class="image-input-wrapper w-125px h-125px" style="background-image: url({{ App\Helpers\helpers::generateTempURL($photo, config('app.file_system')) }});"></div>
                                                        <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change photo">
                                                            <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                                            <input type="file" name="photos[{{ $index }}]" accept=".png, .jpg, .jpeg, .svg" />
                                                            <input type="hidden" name="existing_photos[{{ $index }}]" value="{{ $photo }}" />
                                                            <input type="hidden" name="photos_remove[{{ $index }}]" value="0" />
                                                        </label>
                                                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel photo">
                                                            <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                                        </span>
                                                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow photo-remove-btn" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove photo" data-index="{{ $index }}">
                                                            <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="button" id="add_photo_btn" class="btn btn-light-primary btn-sm mt-3">
                                                <i class="ki-duotone ki-plus fs-3"></i> Add Photo
                                            </button>
                                            <div class="form-text mt-2 text-muted">Allowed file types: png, jpg, jpeg, svg.</div>
                                        </div>
                                    @endif

                                    <div class="col-md-12">
                                        <button type="submit" id="update_btn" class="btn btn-primary">
                                            <span class="indicator-label">Save Changes</span>
                                            <span class="indicator-progress">Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Business Hours (Business Directory only) --}}
                    @if(($catName === 'business directory' || $post->category_id == 3) && $post->businessHours)
                    <div class="card mb-5">
                        <div class="card-header border-0 pt-6">
                            <h3 class="card-title fw-bold">Business Hours</h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table table-row-dashed table-row-gray-300 fs-6">
                                    <thead>
                                        <tr class="fw-bold text-muted">
                                            <th>Day</th>
                                            <th>Status</th>
                                            <th>Opening</th>
                                            <th>Closing</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(['sunday','monday','tuesday','wednesday','thursday','friday','saturday'] as $day)
                                            @php $hours = $post->businessHours->$day; @endphp
                                            <tr>
                                                <td class="fw-semibold text-capitalize">{{ $day }}</td>
                                                <td>
                                                    @if(isset($hours['is_closed']) && filter_var($hours['is_closed'], FILTER_VALIDATE_BOOLEAN))
                                                        <span class="badge badge-light-danger">Closed</span>
                                                    @else
                                                        <span class="badge badge-light-success">Open</span>
                                                    @endif
                                                </td>
                                                <td>{{ $hours['opening_hour'] ?? '-' }}</td>
                                                <td>{{ $hours['closing_time'] ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif


                </div>

                {{-- Right column: meta info --}}
                <div class="col-xl-4">


                    {{-- Post Meta --}}
                    <div class="card mb-5">
                        <div class="card-header border-0 pt-6">
                            <h3 class="card-title fw-bold">Details</h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Status</span>
                                    @php
                                        $cls = ['pending'=>'warning','active'=>'success','rejected'=>'danger','draft'=>'secondary'];
                                        $sc = $cls[$post->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge badge-light-{{ $sc }} text-uppercase">{{ $post->status }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Featured</span>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input toggle-featured-detail" type="checkbox" data-id="{{ $post->id }}" {{ $post->is_featured ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">User</span>
                                    <span class="fw-semibold">
                                        {{ $post->user->name ?? '-' }}
                                        @if($post->user?->email)
                                            <button class="btn btn-icon btn-sm btn-light-primary ms-1 send-direct-email-btn" data-email="{{ $post->user->email }}" data-name="{{ $post->user->name }}" title="Email owner" style="width: 24px; height: 24px;"><i class="ki-duotone ki-sms fs-6"><span class="path1"></span><span class="path2"></span></i></button>
                                        @endif
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Category</span>
                                    <span class="fw-semibold">{{ $post->category->name ?? '-' }}</span>
                                </div>
                                @if($post->condition)
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Condition</span>
                                    <span class="fw-semibold">{{ implode(', ', $post->condition) }}</span>
                                </div>
                                @endif
                                @if($post->job_type)
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Job Type</span>
                                    <span class="fw-semibold">{{ implode(', ', $post->job_type) }}</span>
                                </div>
                                @endif
                                @if($post->service_area)
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Service Area</span>
                                    <span class="fw-semibold">{{ implode(', ', $post->service_area) }}</span>
                                </div>
                                @endif
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Is App Chat</span>
                                    <span class="fw-semibold">{{ $post->is_app_chat ? 'Yes' : 'No' }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Created At</span>
                                    <span class="fw-semibold">{{ $post->created_at->format('d M Y') }}</span>
                                </div>
                                @if($post->histories && $post->histories->count() > 0)
                                    <div class="separator my-2"></div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">History</span>
                                        <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_post_history">Log History</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Danger Zone --}}
                    <div class="card border border-danger">
                        <div class="card-header border-0 pt-6">
                            <h3 class="card-title fw-bold text-danger">Danger Zone</h3>
                        </div>
                        <div class="card-body pt-0 d-flex flex-column gap-2">
                            <button class="btn btn-light-danger w-100 reject-btn" data-id="{{ $post->id }}">Reject Post</button>
                            <button class="btn btn-danger w-100 delete-post-btn" data-id="{{ $post->id }}">Delete Post</button>
                            @if($post->user_id)
                                @if($post->user?->status === 'suspended')
                                    <button class="btn btn-success w-100 suspend-user-owner-btn" data-user-id="{{ $post->user_id }}" data-action="unsuspend">Unsuspend User (Owner)</button>
                                @else
                                    <button class="btn btn-danger w-100 suspend-user-owner-btn" data-user-id="{{ $post->user_id }}" data-action="suspend">Suspend User (Owner)</button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
<script>
$(document).ready(function () {
    // Handle photo remove trigger
    $(document).on('click', '.photo-remove-btn', function () {
        var index = $(this).data('index');
        $('input[name="photos_remove[' + index + ']"]').val('1');
    });

    // Handle dynamic photo addition
    var photoIndex = {{ is_array($post->photos) ? count($post->photos) : 0 }};
    $('#add_photo_btn').on('click', function () {
        var html = `
            <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true">
                <div class="image-input-wrapper w-125px h-125px" style="background-image: url({{ asset('backend/assets/media/svg/files/blank-image.svg') }});"></div>
                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change photo">
                    <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                    <input type="file" name="photos[\${photoIndex}]" accept=".png, .jpg, .jpeg, .svg" />
                    <input type="hidden" name="photos_remove[\${photoIndex}]" value="0" />
                </label>
                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel photo">
                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                </span>
                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow photo-remove-btn" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove photo" data-index="\${photoIndex}">
                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                </span>
            </div>
        `;
        var $el = $(html);
        $('#photos_container').append($el);
        new KTImageInput($el[0]);
        photoIndex++;
    });

    // Update post
    $('#update_post_form').on('submit', function (e) {
        e.preventDefault();
        var btn = $('#update_btn');
        btn.attr('data-kt-indicator', 'on').prop('disabled', true);
        var formData = new FormData(this);
        $.ajax({
            url: '/admin/posts/{{ $post->id }}/update',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.success) {
                    toastr.success(res.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(res.message);
                }
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message || 'Update failed.');
            },
            complete: function () {
                btn.removeAttr('data-kt-indicator').prop('disabled', false);
            }
        });
    });

    // Toggle featured (detail page)
    $(document).on('change', '.toggle-featured-detail', function () {
        var id = $(this).data('id');
        var featured = $(this).is(':checked');
        $.post('/admin/posts/' + id + '/featured', { _token: '{{ csrf_token() }}', featured: featured ? 1 : 0 }, function (res) {
            if (res.success) toastr.success(res.message);
            else toastr.error(res.message);
        });
    });

    // Approve
    $(document).on('click', '.approve-btn', function () {
        var id = $(this).data('id');
        $.post('/admin/posts/' + id + '/approve', { _token: '{{ csrf_token() }}' }, function (res) {
            if (res.success) { toastr.success(res.message); setTimeout(() => location.reload(), 1000); }
            else toastr.error(res.message);
        });
    });

    // Reject
    $(document).on('click', '.reject-btn', function () {
        var id = $(this).data('id');
        Swal.fire({
            text: "{{ __('Reject this post?') }}", icon: 'warning',
            showCancelButton: true, confirmButtonText: "{{ __('Yes, reject!') }}",
            cancelButtonText: "{{ __('Cancel') }}",
            buttonsStyling: false,
            customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light ms-2' }
        }).then(function (result) {
            if (result.value) {
                $.post('/admin/posts/' + id + '/reject', { _token: '{{ csrf_token() }}' }, function (res) {
                    if (res.success) { toastr.success(res.message); setTimeout(() => location.reload(), 1000); }
                    else toastr.error(res.message);
                });
            }
        });
    });

    // Delete
    $(document).on('click', '.delete-post-btn', function () {
        var id = $(this).data('id');
        Swal.fire({
            text: "{{ __('Are you sure you want to delete this post?') }}", icon: 'warning',
            showCancelButton: true, confirmButtonText: "{{ __('Yes, delete!') }}",
            cancelButtonText: "{{ __('No, cancel') }}",
            buttonsStyling: false,
            customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light ms-2' }
        }).then(function (result) {
            if (result.value) {
                $.ajax({
                    url: '/admin/posts/' + id + '/delete', type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        if (res.success) {
                            toastr.success(res.message);
                            setTimeout(() => window.location = '{{ route("post.index") }}', 1000);
                        } else toastr.error(res.message);
                    }
                });
            }
        });
    });

    // Suspend User Owner
    $(document).on('click', '.suspend-user-owner-btn', function () {
        var id = $(this).data('user-id');
        var action = $(this).data('action');
        
        var textMsg = action === 'unsuspend' ? "{{ __('Are you sure you want to unsuspend the owner of this post?') }}" : "{{ __('Are you sure you want to suspend the owner of this post?') }}";
        var confirmBtnText = action === 'unsuspend' ? "{{ __('Yes, unsuspend user!') }}" : "{{ __('Yes, suspend user!') }}";
        var confirmBtnClass = action === 'unsuspend' ? 'btn btn-success' : 'btn btn-danger';
        var ajaxUrl = action === 'unsuspend' ? '/admin/user/' + id + '/unsuspend' : '/admin/user/' + id + '/suspend';

        Swal.fire({
            text: textMsg, icon: 'warning',
            showCancelButton: true, confirmButtonText: confirmBtnText,
            cancelButtonText: "{{ __('Cancel') }}",
            buttonsStyling: false,
            customClass: { confirmButton: confirmBtnClass, cancelButton: 'btn btn-light ms-2' }
        }).then(function (result) {
            if (result.value) {
                $.ajax({
                    url: ajaxUrl, type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        if (res.status === 'success') {
                            toastr.success(res.message);
                            setTimeout(() => window.location.reload(), 1000);
                        } else toastr.error(res.message);
                    }
                });
            }
        });
    });

    // Trigger Send Email Modal
    $(document).on('click', '.send-direct-email-btn', function(e) {
        e.preventDefault();
        var email = $(this).data('email');
        var name = $(this).data('name');
        
        $('#admin_send_email_form')[0].reset();
        $('#email_recipient_display').val(name + ' (' + email + ')');
        $('#email_recipient_hidden').val(email);
        
        $('#kt_modal_send_custom_email').modal('show');
    });

    // Submit Send Email Form
    $('#admin_send_email_form').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#send_email_submit_btn');
        btn.prop('disabled', true).text('Sending...');
        
        $.ajax({
            url: "{{ route('user.send-email') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(res) {
                if (res.success) {
                    Swal.fire('Sent!', res.message, 'success');
                    $('#kt_modal_send_custom_email').modal('hide');
                } else {
                    Swal.fire('Failed', res.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to send email.', 'error');
            },
            complete: function() {
                btn.prop('disabled', false).text('Send Email');
            }
        });
    });

    // Toggle show page bulk delete visibility
    function toggleShowBulkDeleteButton() {
        let checkedCount = $('.select-show-report-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#delete_selected_show_reports_btn').show();
        } else {
            $('#delete_selected_show_reports_btn').hide();
            $('#select_all_show_reports').prop('checked', false);
        }
    }

    // Select all show reports
    $(document).on('change', '#select_all_show_reports', function() {
        let checked = $(this).is(':checked');
        $('.select-show-report-checkbox').prop('checked', checked);
        toggleShowBulkDeleteButton();
    });

    $(document).on('change', '.select-show-report-checkbox', function() {
        toggleShowBulkDeleteButton();
    });

    // Mark single report as read on show page
    $(document).on('click', '.mark-show-report-read-btn', function (e) {
        e.preventDefault();
        let reportId = $(this).data('id');
        let btn = $(this);
        let row = $('#show_report_row_' + reportId);

        $.post('/admin/reports/' + reportId + '/read', { _token: '{{ csrf_token() }}' }, function (res) {
            if (res.success) {
                row.css('background-color', '').find('.new-badge').remove();
                btn.fadeOut(300, function() { $(this).remove(); });
                // Check if any unread left
                let hasUnread = false;
                $('.mark-show-report-read-btn').each(function() {
                    if ($(this).data('id') !== reportId) hasUnread = true;
                });
                if (!hasUnread) $('#mark_all_show_reports_read_btn').fadeOut(300);
            }
        });
    });

    // Mark all reports as read on show page
    $(document).on('click', '#mark_all_show_reports_read_btn', function (e) {
        e.preventDefault();
        $.post('/admin/posts/{{ $post->id }}/reports/read-all', { _token: '{{ csrf_token() }}' }, function (res) {
            if (res.success) {
                toastr.success(res.message);
                $('#mark_all_show_reports_read_btn').fadeOut(300);
                $('.mark-show-report-read-btn').fadeOut(300, function() { $(this).remove(); });
                $('.new-badge').remove();
                $('tr[id^="show_report_row_"]').css('background-color', '');
            }
        });
    });

    // Delete single report on show page
    $(document).on('click', '.delete-show-report-btn', function (e) {
        e.preventDefault();
        let reportId = $(this).data('id');
        let row = $('#show_report_row_' + reportId);

        Swal.fire({
            title: "{{ __('Delete Report') }}",
            text: "{{ __('Are you sure you want to delete this report?') }}",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: "{{ __('Yes, delete it!') }}",
            cancelButtonText: "{{ __('Cancel') }}"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/reports/' + reportId,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire("{{ __('Deleted!') }}", res.message, 'success');
                            row.fadeOut(300, function() {
                                $(this).remove();
                                if ($('.select-show-report-checkbox').length === 0) {
                                    location.reload();
                                } else {
                                    toggleShowBulkDeleteButton();
                                }
                            });
                        }
                    }
                });
            }
        });
    });

    // Bulk Delete Selected Reports on show page
    $(document).on('click', '#delete_selected_show_reports_btn', function (e) {
        e.preventDefault();
        let selectedIds = [];
        $('.select-show-report-checkbox:checked').each(function() {
            selectedIds.push($(this).data('id'));
        });

        if (selectedIds.length === 0) return;

        Swal.fire({
            title: "{{ __('Delete Selected Reports') }}",
            text: "{{ __('Are you sure you want to delete the selected reports?') }}",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: "{{ __('Yes, delete them!') }}",
            cancelButtonText: "{{ __('Cancel') }}"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/reports/bulk-delete',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', ids: selectedIds },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire("{{ __('Deleted!') }}", res.message, 'success');
                            selectedIds.forEach(function(id) {
                                $('#show_report_row_' + id).fadeOut(300, function() {
                                    $(this).remove();
                                    if ($('.select-show-report-checkbox').length === 0) {
                                        location.reload();
                                    }
                                });
                            });
                            $('#delete_selected_show_reports_btn').hide();
                            $('#select_all_show_reports').prop('checked', false);
                        }
                    }
                });
            }
        });
    });
});
</script>
@endpush

<!--begin::Modal - Send Custom Email-->
<div class="modal fade" id="kt_modal_send_custom_email" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Send Email to User</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" onclick="$('#kt_modal_send_custom_email').modal('hide')">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 my-3">
                <form id="admin_send_email_form">
                    @csrf
                    <div class="mb-5">
                        <label class="form-label fw-semibold">Recipient</label>
                        <input type="text" id="email_recipient_display" class="form-control form-control-solid" readonly>
                        <input type="hidden" name="email" id="email_recipient_hidden">
                    </div>
                    <div class="mb-5">
                        <label class="form-label fw-semibold">Subject</label>
                        <input type="text" name="subject" class="form-control form-control-solid" placeholder="Enter subject" required>
                    </div>
                    <div class="mb-5">
                        <label class="form-label fw-semibold">Message</label>
                        <textarea name="message" class="form-control form-control-solid" rows="5" placeholder="Write your email body here..." required></textarea>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-light btn-sm me-2" onclick="$('#kt_modal_send_custom_email').modal('hide')">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="send_email_submit_btn">Send Email</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--end::Modal - Send Custom Email-->

@if($post->histories && $post->histories->count() > 0)
<!--begin::Modal - Post History-->
<div class="modal fade" id="kt_modal_post_history" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Proposed Changes / History</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" onclick="$('#kt_modal_post_history').modal('hide')">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 my-3">
                <div class="table-responsive">
                    <table class="table table-row-dashed align-middle fs-6 gy-4">
                        <thead>
                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                <th>Field</th>
                                <th>Old Value</th>
                                <th>New Value</th>
                                <th>Updated At</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            @foreach($post->histories as $history)
                                @php
                                    $old = $history->old_data ?? [];
                                    $new = $history->new_data ?? [];
                                    $fields = array_keys($new);
                                @endphp
                                @foreach($fields as $field)
                                    @if($field === 'status') @continue @endif
                                    <tr>
                                        <td><strong class="text-capitalize">{{ str_replace('_', ' ', $field) }}</strong></td>
                                        <td>
                                            @if(is_array($old[$field] ?? null))
                                                {{ json_encode($old[$field]) }}
                                            @else
                                                {{ $old[$field] ?? '-' }}
                                            @endif
                                        </td>
                                        <td class="text-primary">
                                            @if(is_array($new[$field] ?? null))
                                                {{ json_encode($new[$field]) }}
                                            @else
                                                {{ $new[$field] ?? '-' }}
                                            @endif
                                        </td>
                                        <td>{{ $history->created_at ? $history->created_at->diffForHumans() : '' }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end::Modal - Post History-->
@endif
@endsection
