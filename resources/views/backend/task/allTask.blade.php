@extends('backend.layouts.design')
@section('title')Tasks @endsection

@section('extra_css')
<style>
    .table-container {
        background: #fff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .pointer { cursor: pointer; }
    .user-name { margin:0; font-size:.95rem; font-weight:600; }
    .nowrap { white-space: nowrap; }
</style>
@endsection

@section('content')
<div class="main-content">
    <h2 class="mb-3">{{ ucFirst($status) }} Tasks</h2>

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                swal({ title: @json(session('success')), icon: 'success' });
            });
        </script>
    @endif

    <div class="table-container">
        <table id="tasks-table" class="table table-striped w-100">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Freelancer</th>
                    <th>Title</th>
                    <th>Client Location</th>
                    <th>Expected Start</th>
                    <th class="nowrap">Budget ($)</th>
                    <th>Created</th>
                    <th>Status</th>
                    <th style="min-width:160px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tasks as $item)
                    <tr>
                        <td><h5 class="user-name">{{ $item->createdBy->name ?? 'N/A' }}</h5></td>
                        <td>{{ $item->freelancer->name ?? 'N/A' }}</td>
                        <td>{{ $item->task_title }}</td>
                        <td>{{ $item->createdBy->current_address ?? 'N/A' }}</td>
                        <td class="nowrap">{{ $item->task_date }}</td>
                        <td class="nowrap">{{ number_format($item->task_budget, 2) }}</td>
                        <td class="nowrap">{{ $item->created_at->format('D, M j, Y') }}</td>
                        <td>
                            <div class="btn-group">
                                <span class="badge bg-{{ $item->getBgColor($item->status) }} pointer" data-bs-toggle="dropdown">
                                    {{ ucFirst($item->status) }}
                                </span>
                                <div class="dropdown-menu">
                                    @foreach ($allStatus as $st)
                                        <a class="dropdown-item {{ $item->status == $st['name'] ? 'd-none' : '' }}"
                                           {{-- href="{{ route('adminUpdateTaskstatus', ['id' => $item->id, 'status' => $st['name']]) }}" --}}
                                        >
                                            {{ ucFirst($st['name']) }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <a class="btn btn-light btn-sm border border-info me-1 d-none">
                                    <i class="fa fa-edit text-info"></i>
                                </a>
                                <a href="{{ route('singleTask', $item->id) }}"
                                   class="btn btn-light btn-sm border border-primary me-1">
                                    <i class="fa fa-eye text-primary"></i>
                                </a>
                                <form method="POST"
                                      {{-- action="{{ route('adminUserDelete', $user->id) }}" --}}
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
                    <tr><td colspan="9" class="text-center text-muted">No tasks found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('extra_js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#tasks-table').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 20, 50, 100]
    });

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
});
</script>
@endsection
