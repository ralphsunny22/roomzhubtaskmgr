@extends('backend.layouts.design')
@section('title')Client Profile @endsection

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
    .property-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1e2a44;
    }
    .property-meta {
        font-size: 1.05rem;
        color: #6c757d;
    }
    .feature-badge {
        background: #e9ecef;
        color: #495057;
        border-radius: 20px;
        padding: 5px 14px;
        margin: 2px 6px 2px 0;
        font-size: 0.92rem;
        display: inline-block;
    }
    .owner-card {
        background: #f8f9fa;
        border-radius: 10px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        padding: 20px;
    }
    .owner-card img {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        object-fit: cover;
    }
    .collapse-content { transition: all 0.3s ease; }

    /* tiny chips for stats */
    .stat-chip {
        display:inline-flex; align-items:center; gap:6px;
        background:#f1f3f5; color:#343a40; padding:6px 10px; border-radius:999px; font-size:.85rem;
    }
</style>
@endsection

@section('content')
<div class="main-content">
    <a href="{{ route('allClient') }}" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Back to Clients
    </a>

    <div class="property-section mb-4">
        <div class="row">
            <div class="col-lg-7">
                {{-- Header --}}
                <div class="mb-2 d-flex align-items-center flex-wrap gap-2">
                    <span class="property-title">{{ $client->name }}</span>
                    <span class="badge bg-primary">Client</span>
                </div>

                <div class="property-meta mb-2">
                    <i class="fas fa-envelope"></i> {{ $client->email ?? 'N/A' }}
                    @if(!empty($client->phone_number))
                        <span class="mx-2">·</span>
                        <i class="fas fa-phone"></i> {{ $client->phone_number }}
                    @endif
                    <span class="mx-2">·</span>
                    <i class="fas fa-calendar"></i> Joined {{ optional($client->created_at)->format('M j, Y') }}
                </div>

                {{-- About --}}
                <div class="mb-3">
                    <span class="feature-badge">About: {{ $client->about ?? 'N/A' }}</span>
                    <span class="feature-badge">Status: {{ ucfirst($client->status ?? 'unknown') }}</span>
                </div>

                {{-- Build a unified carousel from all task images --}}
                @php
                    $allImages = [];
                    foreach (($client->clientTasks ?? []) as $t) {
                        $imgs = $t->task_images ?? [];
                        if (is_string($imgs)) {
                            $decoded = json_decode($imgs, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $imgs = $decoded;
                        }
                        if (is_array($imgs)) {
                            foreach ($imgs as $im) {
                                if ($im) $allImages[] = $im;
                            }
                        }
                    }
                    $allImages = array_values(array_filter($allImages));
                @endphp

                @if (count($allImages))
                    <div id="clientTasksCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            @foreach ($allImages as $idx => $picture)
                                <div class="carousel-item {{ $idx === 0 ? 'active' : '' }}">
                                    <img src="{{ $picture }}" class="d-block w-100" alt="Task Image {{ $idx + 1 }}">
                                </div>
                            @endforeach
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#clientTasksCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#clientTasksCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                @else
                    <div class="mb-4">
                        <img src="https://via.placeholder.com/900x350?text=No+Task+Images" class="w-100 rounded" alt="No Task Images">
                        <p class="text-center text-muted mt-2">This client has no task images yet.</p>
                    </div>
                @endif

                {{-- Quick stats chips --}}
                @php
                    $tasks = $client->clientTasks ?? collect();
                    $countAll = $tasks->count();
                    $cPending = $tasks->where('status','pending')->count();
                    $cStarted = $tasks->where('status','started')->count();
                    $cCompleted = $tasks->where('status','completed')->count();
                @endphp
                <div class="d-flex flex-wrap gap-2">
                    <span class="stat-chip"><i class="fas fa-clipboard-list"></i> Tasks: {{ $countAll }}</span>
                    <span class="stat-chip"><i class="fas fa-hourglass-half"></i> Pending: {{ $cPending }}</span>
                    <span class="stat-chip"><i class="fas fa-play-circle"></i> Started: {{ $cStarted }}</span>
                    <span class="stat-chip"><i class="fas fa-check-circle"></i> Completed: {{ $cCompleted }}</span>
                </div>
            </div>

            <div class="col-lg-5">
                {{-- Client info card --}}
                <div class="owner-card mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <img src="{{ $client->profile_picture ?? 'https://via.placeholder.com/70' }}" alt="Client Profile">
                        <div class="ms-3">
                            <h5 class="mb-0">{{ $client->name }}</h5>
                            <span class="text-muted">Client Profile</span>
                        </div>
                    </div>
                    <div class="mb-1"><i class="fas fa-envelope"></i> {{ $client->email ?? 'N/A' }}</div>
                    @if(!empty($client->phone_number))
                        <div class="mb-1"><i class="fas fa-phone"></i> {{ $client->phone_number }}</div>
                    @endif
                    <div><i class="fas fa-info-circle"></i> About: {{ $client->about ?? 'N/A' }}</div>
                </div>

                {{-- Tasks (with offers collapsible per task) --}}
                <div class="property-section p-3">
                    <h6 class="mb-3">Client Tasks</h6>
                    @if ($tasks->isEmpty())
                        <p class="text-muted mb-0">No tasks available for this client.</p>
                    @else
                        @foreach ($tasks as $task)
                            @php
                                // Images array (array or JSON)
                                $imgs = $task->task_images ?? [];
                                if (is_string($imgs)) {
                                    $dec = json_decode($imgs, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($dec)) $imgs = $dec;
                                }
                                $imgs = is_array($imgs) ? $imgs : [];
                                // Badge color
                                $status = strtolower($task->status ?? 'unknown');
                                $badge = match ($status) {
                                    'pending'   => 'warning',
                                    'started'   => 'info',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    default     => 'secondary'
                                };
                            @endphp

                            <div class="mb-4">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="mb-1">{{ $task->task_title }}</h6>
                                        <div class="text-muted small mb-2">
                                            <i class="fas fa-calendar-day"></i> {{ $task->task_date }}
                                            <span class="mx-2">·</span>
                                            <i class="fas fa-clock"></i> {{ $task->task_time_of_day }}
                                            <span class="mx-2">·</span>
                                            <i class="fas fa-wallet"></i> ${{ number_format($task->task_budget, 2) }}
                                        </div>
                                    </div>
                                    <span class="feature-badge bg-{{ $badge }} {{ in_array($badge,['warning']) ? 'text-dark' : 'text-white' }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </div>

                                @if(count($imgs))
                                    <div class="mt-2 d-flex flex-wrap gap-2">
                                        @foreach($imgs as $im)
                                            <img src="{{ $im }}" class="rounded" style="width:90px; height:72px; object-fit:cover;" alt="Task Image">
                                        @endforeach
                                    </div>
                                @endif

                                <p class="mt-2 mb-2">{{ $task->task_description }}</p>

                                <button class="btn btn-sm btn-primary"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#task-{{ $task->id }}-offers"
                                        aria-expanded="false">
                                    View Offers ({{ $task->offers_count ?? ($task->offers?->count() ?? 0) }})
                                </button>

                                <div class="collapse mt-2 collapse-content" id="task-{{ $task->id }}-offers">
                                    @php $offers = $task->offers ?? collect(); @endphp
                                    @if (($task->offers_count ?? $offers->count()) > 0)
                                        <table class="table table-sm">
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
                                                        $offerStatus = strtolower($offer->status ?? 'pending');
                                                        $offerBadge = match ($offerStatus) {
                                                            'accepted'  => 'success',
                                                            'declined'  => 'danger',
                                                            'cancelled' => 'danger',
                                                            'completed' => 'secondary',
                                                            default     => 'warning',
                                                        };
                                                    @endphp
                                                    <tr>
                                                        <td class="px-2 py-1">{{ $offer->freelancer->name ?? 'N/A' }}</td>
                                                        <td class="px-2 py-1">{{ $offer->freelancer_proposal ?? '—' }}</td>
                                                        <td class="px-2 py-1">
                                                            ${{ number_format($offer->amount_offered_by_freelancer ?? 0, 2) }}
                                                        </td>
                                                        <td class="px-2 py-1">
                                                            {{ $offer->freelancer_date_availability ?? '—' }}<br>
                                                            {{ $offer->freelancer_start_time_available ?? '—' }}
                                                            -
                                                            {{ $offer->freelancer_end_time_available ?? '—' }}
                                                        </td>
                                                        <td class="px-2 py-1">
                                                            <span class="feature-badge bg-{{ $offerBadge }} {{ in_array($offerBadge,['warning']) ? 'text-dark' : 'text-white' }}">
                                                                {{ ucfirst($offerStatus) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="text-muted mb-0">No offers available for this task.</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_js')
{{-- If your base layout already includes Bootstrap Bundle, you can omit any extra includes. --}}
{{-- SweetAlert only needed if you add destructive actions on this page. --}}
@endsection
