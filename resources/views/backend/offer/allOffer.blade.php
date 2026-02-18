@extends('backend.layouts.design')
@section('title')Offers @endsection

@section('extra_css')
<style>
    .table-container {
        background: #fff; border-radius: 10px; padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .pointer { cursor: pointer; }
    .nowrap { white-space: nowrap; }
    .proposal-snippet {
        max-width: 340px; display: -webkit-box; -webkit-line-clamp: 2;
        -webkit-box-orient: vertical; overflow: hidden;
    }
</style>
@endsection

@section('content')
<div class="main-content">
    <h2 class="mb-3">{{ ucFirst($status ?? 'all') }} Offers</h2>

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                swal({ title: @json(session('success')), icon: 'success' });
            });
        </script>
    @endif

    <div class="table-container">
        <table id="offers-table" class="table table-striped w-100">
            <thead>
            <tr>
                <th>Task</th>
                <th>Client</th>
                <th>Freelancer</th>
                <th>Proposal</th>
                <th class="nowrap">Amount ($)</th>
                <th>Availability</th>
                <th class="nowrap">Created</th>
                <th>Status</th>
                <th style="min-width:180px;">Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($offers as $offer)
                @php
                    $task = $offer->task_detail;
                    $client = $task->creator ?? null;
                    $freelancer = $offer->freelancer ?? null;
                @endphp
                <tr>
                    <td>{{ $task->task_title ?? 'N/A' }}</td>
                    <td>{{ $client->name ?? 'N/A' }}</td>
                    <td>{{ $freelancer->name ?? 'N/A' }}</td>
                    <td><div class="proposal-snippet">{{ $offer->freelancer_proposal ?? '—' }}</div></td>
                    <td class="nowrap">{{ number_format($offer->amount_offered_by_freelancer ?? 0, 2) }}</td>
                    <td class="small">
                        {{ $offer->freelancer_date_availability ?? '—' }}<br>
                        @if(!empty($offer->freelancer_start_time_available) || !empty($offer->freelancer_end_time_available))
                            {{ $offer->freelancer_start_time_available ?? '—' }} - {{ $offer->freelancer_end_time_available ?? '—' }}
                        @endif
                    </td>
                    <td class="nowrap">{{ optional($offer->created_at)->format('D, M j, Y') }}</td>
                    <td>
                        <span class="badge bg-{{ $offer->getBgColor($offer->status) ?? 'warning' }}">
                            {{ ucFirst($offer->status ?? 'pending') }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-1">
                            {{-- View --}}
                            <a href="{{ route('singleOffer', $offer->id) }}"
                               class="btn btn-light btn-sm border border-primary">
                                <i class="fa fa-eye text-primary"></i>
                            </a>

                            {{-- Update status (dropdown -> submits hidden form) --}}
                            <div class="btn-group">
                                <button type="button"
                                        class="btn btn-light btn-sm border dropdown-toggle"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-ruble"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    @foreach ($allStatus as $st)
                                        @php $same = ($offer->status ?? '') === $st['name']; @endphp
                                        <button type="button"
                                                class="dropdown-item js-status-action {{ $same ? 'disabled' : '' }}"
                                                data-offer-id="{{ $offer->id }}"
                                                data-status="{{ $st['name'] }}">
                                            {{ ucFirst($st['name']) }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Hidden form to submit the chosen status --}}
                            <form id="offer-status-form-{{ $offer->id }}"
                                  action="{{ route('updateOfferStatus', $offer->id) }}"
                                  method="POST" class="d-none">
                                @csrf
                                <input type="hidden" name="offer_status" value="">
                            </form>

                            {{-- Delete (optional) --}}
                            <form method="POST"
                                  {{-- action="{{ route('adminOfferDelete', $offer->id) }}" --}}
                                  class="m-0">
                                @csrf
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit"
                                        class="btn btn-light btn-sm border border-danger show_confirm"
                                        title="Delete">
                                    <i class="fa fa-trash text-danger"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted">No offers found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

{{-- @section('extra_js') --}}
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // DataTable
    $('#offers-table').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 20, 50, 100]
    });

    // Delete confirm
    $(document).on('click', '.show_confirm', function (event) {
        event.preventDefault();
        const form = $(this).closest('form');
        swal({
            title: 'Are you sure you want to delete this record?',
            text: 'If you delete this, it will be gone forever.',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) form.submit();
        });
    });

    // Update status from Action dropdown
    $(document).on('click', '.js-status-action', function (e) {
        e.preventDefault();
        if ($(this).hasClass('disabled')) return;

        const status = $(this).data('status');
        const id = $(this).data('offer-id');
        const form = $('#offer-status-form-' + id);
        form.find('input[name="offer_status"]').val(status);

        // Confirm for destructive-ish states
        const confirmNeeded = ['declined', 'cancelled'].includes(String(status).toLowerCase());
        if (confirmNeeded) {
            swal({
                title: 'Confirm status change',
                text: 'Change status to ' + status + '?',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((ok) => { if (ok) form.trigger('submit'); });
        } else {
            form.trigger('submit');
        }
    });
}); --}}

{{-- </script> --}}
{{-- @endsection --}}

@section('extra_js')
{{-- Make sure jQuery is loaded in your base layout before this. --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script>
(function () {
  // Tiny wrapper that falls back to window.confirm if swal isn't available
  function confirmBox(opts) {
    const title = opts.title || 'Are you sure?';
    const text = opts.text || '';
    const danger = !!opts.danger;
    if (typeof swal === 'function') {
      return swal({ title: title, text: text, icon: danger ? 'warning' : 'info', buttons: true, dangerMode: danger });
    }
    // Fallback
    return Promise.resolve(window.confirm(title + (text ? '\n' + text : '')));
  }

  document.addEventListener('DOMContentLoaded', function () {
    // Safely init DataTable if available (prevents JS error from stopping SweetAlert)
    if ($.fn && $.fn.DataTable) {
      $('#offers-table').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 20, 50, 100]
      });
    } else {
      console.warn('DataTables not found — skipping table init');
    }

    // Delete confirm
    $(document).on('click', '.show_confirm', function (event) {
      event.preventDefault();
      const form = $(this).closest('form');
      confirmBox({
        title: 'Are you sure you want to delete this record?',
        text: 'If you delete this, it will be gone forever.',
        danger: true
      }).then(function (ok) {
        if (ok) form.trigger('submit');
      });
    });

    // Update status from Action dropdown
    $(document).on('click', '.js-status-action', function (e) {
      e.preventDefault();
      const $btn = $(this);
      if ($btn.hasClass('disabled')) return;

      const status = String($btn.data('status') || '').toLowerCase();
      const id = $btn.data('offer-id');
      const form = $('#offer-status-form-' + id);
      form.find('input[name="offer_status"]').val(status);

      const needsConfirm = ['declined', 'cancelled'].includes(status);
      if (needsConfirm) {
        confirmBox({
          title: 'Confirm status change',
          text: 'Change status to ' + status + '?',
          danger: true
        }).then(function (ok) {
          if (ok) form.trigger('submit');
        });
      } else {
        form.trigger('submit');
      }
    });

    // Quick smoke test if you want to verify SweetAlert loaded (uncomment):
    // if (typeof swal !== 'function') console.warn('SweetAlert not loaded');
    // else swal({ title: 'SweetAlert ready!', icon: 'success' });
  });
})();
</script>
@endsection

