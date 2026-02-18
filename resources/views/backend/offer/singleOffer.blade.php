@extends('backend.layouts.design')
@section('title')Offer Details @endsection

@section('extra_css')
<style>
    .carousel-inner img {
        width:100%; height:350px; object-fit:cover; border-radius:10px;
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
    .proposal-box {
        background:#f9fafb; border-radius:10px; padding:16px; border:1px solid #eef1f4;
        white-space:pre-wrap;
    }
</style>
@endsection

@section('content')
<div class="main-content">
    <a href="{{ route('allOffer') }}" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Back to Offers
    </a>

    @php
        $task = $offer->task_detail;
        $client = $task->creator ?? null;
        $freelancer = $offer->freelancer ?? null;

        $status = strtolower($offer->status ?? 'pending');
        $badge = match ($status) {
            'accepted'  => 'primary',
            'started'   => 'info',
            'completed' => 'success',
            'declined'  => 'danger',
            'cancelled' => 'danger',
            default     => 'warning'
        };

        // images from the task (array or JSON)
        $imgs = $task->task_images ?? [];
        if (is_string($imgs)) {
            $dec = json_decode($imgs, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($dec)) $imgs = $dec;
        }
        $imgs = is_array($imgs) ? array_values(array_filter($imgs)) : [];
    @endphp

    <div class="property-section mb-4">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
            <div>
                <div class="mb-2 d-flex align-items-center flex-wrap gap-2">
                    <span class="property-title">Offer #{{ $offer->id }} • {{ $task->task_title ?? 'Task' }}</span>
                    <span class="feature-badge bg-{{ $badge }} {{ in_array($badge,['warning']) ? 'text-dark' : 'text-white' }}">
                        {{ ucfirst($status) }}
                    </span>
                </div>
                <div class="property-meta">
                    <i class="fas fa-wallet"></i> ${{ number_format($offer->amount_offered_by_freelancer ?? 0, 2) }}
                    <span class="mx-2">·</span>
                    <i class="fas fa-calendar-day"></i> {{ $offer->freelancer_date_availability ?? '—' }}
                    @if(!empty($offer->freelancer_start_time_available) || !empty($offer->freelancer_end_time_available))
                        <span class="mx-2">·</span>
                        <i class="fas fa-clock"></i> {{ $offer->freelancer_start_time_available ?? '—' }} - {{ $offer->freelancer_end_time_available ?? '—' }}
                    @endif
                    <span class="mx-2">·</span>
                    <i class="fas fa-calendar"></i> Offered {{ optional($offer->created_at)->format('M j, Y') }}
                </div>
            </div>

            {{-- Update status --}}
            <form action="{{ route('updateOfferStatus', $offer->id) }}" method="post" class="d-flex align-items-center gap-2">
                @csrf
                <select class="form-select form-select-sm" name="offer_status" style="min-width:180px;">
                    @foreach (['pending','accepted','declined','cancelled'] as $s)
                        <option value="{{ $s }}" {{ ($offer->status ?? '') === $s ? 'selected' : '' }}>
                            {{ ucfirst($s) }}
                        </option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-primary" type="submit">Update</button>
            </form>
        </div>

        {{-- Task images carousel --}}
        @if (count($imgs))
            <div id="offerTaskCarousel" class="carousel slide mt-3" data-bs-ride="carousel">
                <div class="carousel-inner">
                    @foreach ($imgs as $i => $pic)
                        <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                            <img src="{{ $pic }}" class="d-block w-100" alt="Task Image {{ $i + 1 }}">
                        </div>
                    @endforeach
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#offerTaskCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#offerTaskCarousel" data-bs-slide="next">
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

        {{-- Proposal --}}
        <div class="mt-3">
            <h6 class="mb-2">Freelancer Proposal</h6>
            <div class="proposal-box">{{ $offer->freelancer_proposal ?? '—' }}</div>
        </div>
    </div>

    <div class="row">
        {{-- Client card --}}
        <div class="col-lg-4">
            <div class="owner-card mb-4">
                <div class="d-flex align-items-center mb-2">
                    <img src="{{ $client->profile_picture ?? 'https://via.placeholder.com/70' }}" alt="Client">
                    <div class="ms-3">
                        <h5 class="mb-0">{{ $client->name ?? 'N/A' }}</h5>
                        <span class="text-muted">Client</span>
                    </div>
                </div>
                <div class="mb-1"><i class="fas fa-envelope"></i> {{ $client->email ?? 'N/A' }}</div>
                @if(!empty($client?->phone_number))
                    <div class="mb-1"><i class="fas fa-phone"></i> {{ $client->phone_number }}</div>
                @endif
                @if(!empty($client?->current_address))
                    <div><i class="fas fa-map-marker-alt"></i> {{ $client->current_address }}</div>
                @endif
            </div>
        </div>

        {{-- Freelancer card --}}
        <div class="col-lg-4">
            <div class="owner-card mb-4">
                <div class="d-flex align-items-center mb-2">
                    <img src="{{ $freelancer->profile_picture ?? 'https://via.placeholder.com/70' }}" alt="Freelancer">
                    <div class="ms-3">
                        <h5 class="mb-0">{{ $freelancer->name ?? 'N/A' }}</h5>
                        <span class="text-muted">Freelancer</span>
                    </div>
                </div>
                <div class="mb-1"><i class="fas fa-envelope"></i> {{ $freelancer->email ?? 'N/A' }}</div>
                @if(!empty($freelancer?->phone_number))
                    <div class="mb-1"><i class="fas fa-phone"></i> {{ $freelancer->phone_number }}</div>
                @endif
                @if(!empty($freelancer?->about))
                    <div><i class="fas fa-info-circle"></i> {{ $freelancer->about }}</div>
                @endif
            </div>
        </div>

        {{-- Task summary --}}
        <div class="col-lg-4">
            <div class="owner-card mb-4">
                <h6 class="mb-2">Task Summary</h6>
                <div class="mb-1"><i class="fas fa-heading"></i> {{ $task->task_title ?? 'N/A' }}</div>
                <div class="mb-1">
                    <i class="fas fa-calendar-day"></i> {{ $task->task_date ?? '—' }}
                    <span class="mx-2">·</span>
                    <i class="fas fa-clock"></i> {{ $task->task_time_of_day ?? '—' }}
                </div>
                <div class="mb-1"><i class="fas fa-sun"></i> {{ $task->task_part_of_day ?? '—' }}</div>
                <div><i class="fas fa-wallet"></i> ${{ number_format($task->task_budget ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_js')
{{-- Add SweetAlert here if you later add destructive actions on this page --}}
@endsection
