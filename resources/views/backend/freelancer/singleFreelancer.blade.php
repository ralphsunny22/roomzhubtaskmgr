@extends('backend.layouts.design')
@section('title')Freelancer Profile @endsection

@section('extra_css')
<style>
    .carousel-inner img {
        width: 100%;
        height: 350px;
        object-fit: cover;
        border-radius: 10px;
    }
    .property-section {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.07);
        padding: 30px 24px;
        margin-bottom: 30px;
    }
    .property-title { font-size: 2rem; font-weight: 700; color: #1e2a44; }
    .property-meta { font-size: 1.05rem; color: #6c757d; }
    .feature-badge {
        background: #e9ecef; color: #495057; border-radius: 20px;
        padding: 5px 14px; margin: 2px 6px 2px 0; font-size: .92rem; display: inline-block;
    }
    .owner-card {
        background: #f8f9fa; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.04); padding: 20px;
    }
    .owner-card img { width: 70px; height: 70px; border-radius: 50%; object-fit: cover; }
    .collapse-content { transition: all 0.3s ease; }

    .stat-chip {
        display:inline-flex; align-items:center; gap:6px;
        background:#f1f3f5; color:#343a40; padding:6px 10px; border-radius:999px; font-size:.85rem;
    }
</style>
@endsection

@section('content')
<div class="main-content">
    <a href="{{ route('allFreelancer') }}" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Back to Freelancers
    </a>

    <div class="property-section mb-4">
        <div class="row">
            <div class="col-lg-7">
                {{-- Header --}}
                <div class="mb-2 d-flex align-items-center flex-wrap gap-2">
                    <span class="property-title">{{ $freelancer->name }}</span>
                    <span class="badge bg-primary">Freelancer</span>
                </div>

                <div class="property-meta mb-2">
                    <i class="fas fa-envelope"></i> {{ $freelancer->email ?? 'N/A' }}
                    @if(!empty($freelancer->phone_number))
                        <span class="mx-2">·</span>
                        <i class="fas fa-phone"></i> {{ $freelancer->phone_number }}
                    @endif
                    <span class="mx-2">·</span>
                    <i class="fas fa-calendar"></i> Joined {{ optional($freelancer->created_at)->format('M j, Y') }}
                </div>

                <div class="mb-2">
                    <span class="feature-badge">Status: {{ ucfirst($freelancer->status ?? 'unknown') }}</span>
                    <span class="feature-badge">About: {{ $freelancer->about ?? 'N/A' }}</span>
                </div>

                {{-- Skills badges --}}
                @php
                    $skillsRaw = $freelancer->skills ?? [];
                    if (is_string($skillsRaw)) {
                        $skills = array_filter(array_map('trim', explode(',', $skillsRaw)));
                    } elseif (is_array($skillsRaw)) {
                        $skills = array_filter(array_map('trim', $skillsRaw));
                    } else {
                        $skills = [];
                    }
                @endphp
                @if(count($skills))
                    <div class="mb-3">
                        @foreach ($skills as $sk)
                            <span class="feature-badge"><i class="fas fa-check"></i> {{ $sk }}</span>
                        @endforeach
                    </div>
                @endif

                {{-- Unified carousel from all task_detail images across offers --}}
                @php
                    $images = [];
                    foreach (($freelancer->freelancerTaskOffers ?? []) as $off) {
                        $arr = $off->task_detail->task_images ?? [];
                        if (is_string($arr)) {
                            $dec = json_decode($arr, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($dec)) $arr = $dec;
                        }
                        if (is_array($arr)) {
                            foreach ($arr as $im) if ($im) $images[] = $im;
                        }
                    }
                    $images = array_values(array_filter($images));
                @endphp

                @if (count($images))
                    <div id="freelancerCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            @foreach ($images as $i => $img)
                                <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                                    <img src="{{ $img }}" class="d-block w-100" alt="Task Image {{ $i + 1 }}">
                                </div>
                            @endforeach
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#freelancerCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#freelancerCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                @else
                    <div class="mb-4">
                        <img src="https://via.placeholder.com/900x350?text=No+Images" class="w-100 rounded" alt="No Images">
                        <p class="text-center text-muted mt-2">No images from tasks yet.</p>
                    </div>
                @endif

                {{-- Quick stats chips --}}
                @php
                    $offers = $freelancer->freelancerTaskOffers ?? collect();
                    $total  = $offers->count();
                    $pnd    = $offers->where('status','pending')->count();
                    $acc    = $offers->where('status','accepted')->count();
                    $dec    = $offers->where('status','declined')->count();
                    $cmp    = $offers->where('status','completed')->count();
                    $cnl    = $offers->where('status','cancelled')->count();
                @endphp
                <div class="d-flex flex-wrap gap-2">
                    <span class="stat-chip"><i class="fas fa-gift"></i> Offers: {{ $total }}</span>
                    <span class="stat-chip"><i class="fas fa-hourglass-half"></i> Pending: {{ $pnd }}</span>
                    <span class="stat-chip"><i class="fas fa-thumbs-up"></i> Accepted: {{ $acc }}</span>
                    <span class="stat-chip"><i class="fas fa-thumbs-down"></i> Declined: {{ $dec }}</span>
                    <span class="stat-chip"><i class="fas fa-check-circle"></i> Completed: {{ $cmp }}</span>
                    <span class="stat-chip"><i class="fas fa-times-circle"></i> Cancelled: {{ $cnl }}</span>
                </div>
            </div>

            <div class="col-lg-5">
                {{-- Info card --}}
                <div class="owner-card mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <img src="{{ $freelancer->profile_picture ?? 'https://via.placeholder.com/70' }}" alt="Freelancer Profile">
                        <div class="ms-3">
                            <h5 class="mb-0">{{ $freelancer->name }}</h5>
                            <span class="text-muted">Freelancer</span>
                        </div>
                    </div>
                    <div class="mb-1"><i class="fas fa-envelope"></i> {{ $freelancer->email ?? 'N/A' }}</div>
                    @if(!empty($freelancer->phone_number))
                        <div class="mb-1"><i class="fas fa-phone"></i> {{ $freelancer->phone_number }}</div>
                    @endif
                    <div><i class="fas fa-info-circle"></i> About: {{ $freelancer->about ?? 'N/A' }}</div>
                </div>

                {{-- Offers list with collapsible task details --}}
                <div class="property-section p-3 d-none">
                    <h6 class="mb-3">Task Offers</h6>
                    @if ($offers->isEmpty())
                        <p class="text-muted mb-0">No task offers available for this freelancer.</p>
                    @else
                        <table class="table table-sm align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-2 py-1">Task</th>
                                    <th class="px-2 py-1">Proposal</th>
                                    <th class="px-2 py-1">Amount</th>
                                    <th class="px-2 py-1">Availability</th>
                                    <th class="px-2 py-1">Status</th>
                                    <th class="px-2 py-1">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($offers as $offer)
                                    @php
                                        $oStatus = strtolower($offer->status ?? 'pending');
                                        $oBadge = match ($oStatus) {
                                            'accepted'  => 'success',
                                            'declined'  => 'danger',
                                            'completed' => 'secondary',
                                            'cancelled' => 'danger',
                                            default     => 'warning'
                                        };
                                        $task = $offer->task_detail;
                                        $taskImgs = $task->task_images ?? [];
                                        if (is_string($taskImgs)) {
                                            $dec = json_decode($taskImgs, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($dec)) $taskImgs = $dec;
                                        }
                                        $taskImgs = is_array($taskImgs) ? $taskImgs : [];
                                    @endphp
                                    <tr>
                                        <td class="px-2 py-1">{{ $task->task_title ?? 'N/A' }}</td>
                                        <td class="px-2 py-1">{{ $offer->freelancer_proposal ?? '—' }}</td>
                                        <td class="px-2 py-1">${{ number_format($offer->amount_offered_by_freelancer ?? 0, 2) }}</td>
                                        <td class="px-2 py-1">
                                            {{ $offer->freelancer_date_availability ?? '—' }}<br>
                                            {{ $offer->freelancer_start_time_available ?? '—' }}
                                            -
                                            {{ $offer->freelancer_end_time_available ?? '—' }}
                                        </td>
                                        <td class="px-2 py-1">
                                            <span class="feature-badge bg-{{ $oBadge }} {{ in_array($oBadge,['warning']) ? 'text-dark' : 'text-white' }}">
                                                {{ ucfirst($oStatus) }}
                                            </span>
                                        </td>
                                        <td class="px-2 py-1">
                                            <button class="btn btn-sm btn-primary"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#offer-{{ $offer->id }}-task"
                                                    aria-expanded="false">
                                                View Task
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="collapse" id="offer-{{ $offer->id }}-task">
                                        <td colspan="6">
                                            <div class="p-3 bg-light border collapse-content">
                                                <h6 class="mb-1">Task Details</h6>
                                                <div class="text-muted small mb-2">
                                                    <i class="fas fa-user"></i> Client: {{ $task->creator->name ?? 'N/A' }}
                                                    <span class="mx-2">·</span>
                                                    <i class="fas fa-wallet"></i> Budget: ${{ number_format($task->task_budget ?? 0, 2) }}
                                                </div>
                                                <p class="mb-2">{{ $task->task_description ?? '—' }}</p>
                                                @if(count($taskImgs))
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach ($taskImgs as $im)
                                                            <img src="{{ $im }}" class="rounded" style="width:100px; height:80px; object-fit:cover;" alt="Task Image">
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="owner-card mb-4">
                    {{-- Offers list with collapsible task details --}}
                <div class="property-section p-3">
                    <h6 class="mb-3">Task Offers</h6>
                    @if ($offers->isEmpty())
                        <p class="text-muted mb-0">No task offers available for this freelancer.</p>
                    @else
                        <table class="table table-sm align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-2 py-1">Task</th>
                                    <th class="px-2 py-1">Proposal</th>
                                    <th class="px-2 py-1">Amount</th>
                                    <th class="px-2 py-1">Availability</th>
                                    <th class="px-2 py-1">Status</th>
                                    <th class="px-2 py-1">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($offers as $offer)
                                    @php
                                        $oStatus = strtolower($offer->status ?? 'pending');
                                        $oBadge = match ($oStatus) {
                                            'accepted'  => 'success',
                                            'declined'  => 'danger',
                                            'completed' => 'secondary',
                                            'cancelled' => 'danger',
                                            default     => 'warning'
                                        };
                                        $task = $offer->task_detail;
                                        $taskImgs = $task->task_images ?? [];
                                        if (is_string($taskImgs)) {
                                            $dec = json_decode($taskImgs, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($dec)) $taskImgs = $dec;
                                        }
                                        $taskImgs = is_array($taskImgs) ? $taskImgs : [];
                                    @endphp
                                    <tr>
                                        <td class="px-2 py-1">{{ $task->task_title ?? 'N/A' }}</td>
                                        <td class="px-2 py-1">{{ $offer->freelancer_proposal ?? '—' }}</td>
                                        <td class="px-2 py-1">${{ number_format($offer->amount_offered_by_freelancer ?? 0, 2) }}</td>
                                        <td class="px-2 py-1">
                                            {{ $offer->freelancer_date_availability ?? '—' }}<br>
                                            {{ $offer->freelancer_start_time_available ?? '—' }}
                                            -
                                            {{ $offer->freelancer_end_time_available ?? '—' }}
                                        </td>
                                        <td class="px-2 py-1">
                                            <span class="feature-badge bg-{{ $oBadge }} {{ in_array($oBadge,['warning']) ? 'text-dark' : 'text-white' }}">
                                                {{ ucfirst($oStatus) }}
                                            </span>
                                        </td>
                                        <td class="px-2 py-1">
                                            <button class="btn btn-sm btn-primary"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#offer-{{ $offer->id }}-task"
                                                    aria-expanded="false">
                                                View Task
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="collapse" id="offer-{{ $offer->id }}-task">
                                        <td colspan="6">
                                            <div class="p-3 bg-light border collapse-content">
                                                <h6 class="mb-1">Task Details</h6>
                                                <div class="text-muted small mb-2">
                                                    <i class="fas fa-user"></i> Client: {{ $task->creator->name ?? 'N/A' }}
                                                    <span class="mx-2">·</span>
                                                    <i class="fas fa-wallet"></i> Budget: ${{ number_format($task->task_budget ?? 0, 2) }}
                                                </div>
                                                <p class="mb-2">{{ $task->task_description ?? '—' }}</p>
                                                @if(count($taskImgs))
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach ($taskImgs as $im)
                                                            <img src="{{ $im }}" class="rounded" style="width:100px; height:80px; object-fit:cover;" alt="Task Image">
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('extra_js')
{{-- Base layout likely includes Bootstrap bundle. Add SweetAlert here only if needed for actions. --}}
@endsection
