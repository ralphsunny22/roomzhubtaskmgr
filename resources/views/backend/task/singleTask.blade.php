@extends('backend.layouts.design')
@section('title')Single Task @endsection

@section('extra_css')
<style>
    .carousel-inner img {
        width: 100%; height: 350px; object-fit: cover; border-radius: 10px;
    }
    .property-section {
        background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.07);
        padding:30px 24px; margin-bottom:30px;
    }
    .property-title { font-size:2rem; font-weight:700; color:#1e2a44; }
    .property-meta { font-size:1.05rem; color:#6c757d; }
    .feature-badge {
        background:#e9ecef; color:#495057; border-radius:20px; padding:5px 14px;
        margin:2px 6px 2px 0; font-size:.92rem; display:inline-block;
    }
    .owner-card {
        background:#f8f9fa; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,0.04);
        padding:20px;
    }
    .owner-card img { width:70px; height:70px; border-radius:50%; object-fit:cover; }
    .collapse-content { transition:all .3s ease; }
    .stat-chip {
        display:inline-flex; align-items:center; gap:6px; background:#f1f3f5; color:#343a40;
        padding:6px 10px; border-radius:999px; font-size:.85rem;
    }
</style>
@endsection

@section('content')
<div class="main-content">
    <a href="{{ route('allTask') }}" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Back to Tasks
    </a>

    <div class="property-section mb-4">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
            <div>
                <div class="mb-2 d-flex align-items-center flex-wrap gap-2">
                    <span class="property-title">{{ $task->task_title }}</span>
                    @php
                        $status = strtolower($task->status ?? 'pending');
                        $badge = match ($status) {
                            'accepted'  => 'primary',
                            'started'   => 'info',
                            'completed' => 'success',
                            'cancelled' => 'danger',
                            'abandoned' => 'dark',
                            default     => 'warning'
                        };
                    @endphp
                    <span class="feature-badge bg-{{ $badge }} {{ in_array($badge,['warning']) ? 'text-dark' : 'text-white' }}">
                        {{ ucfirst($status) }}
                    </span>
                </div>
                <div class="property-meta">
                    <i class="fas fa-calendar-day"></i> {{ $task->task_date }}
                    <span class="mx-2">·</span>
                    <i class="fas fa-sun"></i> {{ $task->task_part_of_day ?? '—' }}
                    <span class="mx-2">·</span>
                    <i class="fas fa-clock"></i> {{ $task->task_time_of_day ?? '—' }}
                    <span class="mx-2">·</span>
                    <i class="fas fa-wallet"></i> ${{ number_format($task->task_budget, 2) }}
                    <span class="mx-2">·</span>
                    <i class="fas fa-user"></i> Client: {{ $task->creator->name ?? 'N/A' }}
                    @if(!empty($task->freelancer?->name))
                        <span class="mx-2">·</span>
                        <i class="fas fa-briefcase"></i> Freelancer: {{ $task->freelancer->name }}
                    @endif
                </div>
            </div>

            {{-- Update status form --}}
            <form action="{{ route('updateTaskStatus', $task->id) }}" method="post" class="d-flex align-items-center gap-2">
                @csrf
                <select class="form-select form-select-sm" name="task_status" style="min-width:180px;">
                    @foreach (['pending','accepted','started','completed','cancelled','abandoned'] as $s)
                        <option value="{{ $s }}" {{ $task->status == $s ? 'selected' : '' }}>
                            {{ ucfirst($s) }}
                        </option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-primary" type="submit">Update</button>
            </form>
        </div>

        {{-- Task images carousel --}}
        @php
            $imgs = $task->task_images ?? [];
            if (is_string($imgs)) {
                $dec = json_decode($imgs, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($dec)) $imgs = $dec;
            }
            $imgs = is_array($imgs) ? array_values(array_filter($imgs)) : [];
        @endphp

        @if (count($imgs))
            <div id="taskImagesCarousel" class="carousel slide mt-3" data-bs-ride="carousel">
                <div class="carousel-inner">
                    @foreach ($imgs as $i => $pic)
                        <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                            <img src="{{ $pic }}" class="d-block w-100" alt="Task Image {{ $i + 1 }}">
                        </div>
                    @endforeach
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#taskImagesCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#taskImagesCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        @else
            <div class="mt-3">
                <img src="https://via.placeholder.com/900x350?text=No+Task+Images" class="w-100 rounded" alt="No images">
                <p class="text-center text-muted mt-2 mb-0">No images for this task.</p>
            </div>
        @endif

        {{-- Description --}}
        <div class="mt-3">
            <h6>Description</h6>
            <p class="mb-0">{{ $task->task_description }}</p>
        </div>
    </div>

    <div class="row">
        {{-- Client card --}}
        <div class="col-lg-6">
            <div class="owner-card mb-4">
                <div class="d-flex align-items-center mb-2">
                    <img src="{{ $task->creator->profile_picture ?? 'https://via.placeholder.com/70' }}" alt="Client">
                    <div class="ms-3">
                        <h5 class="mb-0">{{ $task->creator->name ?? 'N/A' }}</h5>
                        <span class="text-muted">Client</span>
                    </div>
                </div>
                <div class="mb-1"><i class="fas fa-envelope"></i> {{ $task->creator->email ?? 'N/A' }}</div>
                @if(!empty($task->creator?->phone_number))
                    <div class="mb-1"><i class="fas fa-phone"></i> {{ $task->creator->phone_number }}</div>
                @endif
                @if(!empty($task->creator?->current_address))
                    <div><i class="fas fa-map-marker-alt"></i> {{ $task->creator->current_address }}</div>
                @endif
            </div>
        </div>

        {{-- Assigned freelancer card (if any) --}}
        <div class="col-lg-6">
            <div class="owner-card mb-4">
                <div class="d-flex align-items-center mb-2">
                    <img src="{{ $task->freelancer->profile_picture ?? 'https://via.placeholder.com/70' }}" alt="Freelancer">
                    <div class="ms-3">
                        <h5 class="mb-0">{{ $task->freelancer->name ?? 'N/A' }}</h5>
                        <span class="text-muted">Assigned Freelancer</span>
                    </div>
                </div>
                @if(!empty($task->freelancer?->email))
                    <div class="mb-1"><i class="fas fa-envelope"></i> {{ $task->freelancer->email }}</div>
                @endif
                @if(!empty($task->freelancer?->phone_number))
                    <div class="mb-1"><i class="fas fa-phone"></i> {{ $task->freelancer->phone_number }}</div>
                @endif
                @if(!empty($task->freelancer?->about))
                    <div><i class="fas fa-info-circle"></i> {{ $task->freelancer->about }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Offers --}}
    <div class="property-section p-3">
        <h6 class="mb-3">Offers</h6>
        @php $offers = $task->offers ?? collect(); @endphp
        @if ($offers->isEmpty())
            <p class="text-muted mb-0">No offers available for this task.</p>
        @else
            <table class="table table-sm align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="px-2 py-1">Freelancer</th>
                        <th class="px-2 py-1">Proposal</th>
                        <th class="px-2 py-1">Amount</th>
                        <th class="px-2 py-1">Availability</th>
                        <th class="px-2 py-1">Status</th>
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
                        @endphp
                        <tr>
                            <td class="px-2 py-1">{{ $offer->freelancer->name ?? 'N/A' }}</td>
                            <td class="px-2 py-1">{{ $offer->freelancer_proposal ?? '—' }}</td>
                            <td class="px-2 py-1">${{ number_format($offer->amount_offered_by_freelancer ?? 0, 2) }}</td>
                            <td class="px-2 py-1">
                                {{ $offer->freelancer_date_availability ?? '—' }}
                                @if(!empty($offer->freelancer_start_time_available) || !empty($offer->freelancer_end_time_available))
                                    ({{ $offer->freelancer_start_time_available ?? '—' }} - {{ $offer->freelancer_end_time_available ?? '—' }})
                                @endif
                            </td>
                            <td class="px-2 py-1">
                                <span class="feature-badge bg-{{ $oBadge }} {{ in_array($oBadge,['warning']) ? 'text-dark' : 'text-white' }}">
                                    {{ ucfirst($oStatus) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

</div>
@endsection

@section('extra_js')
{{-- Base layout likely includes Bootstrap bundle and (optionally) SweetAlert via other pages --}}
@endsection
