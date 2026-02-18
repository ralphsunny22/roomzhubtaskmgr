@extends('backend.layouts.design')
@section('title')Users @endsection

@section('extra_css')
<style>
    .table-container {
        background: #fff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .pointer { cursor: pointer; }
    .user-cell {
        display: flex; align-items: center; gap: 10px;
    }
    .user-avatar {
        width: 28px; height: 28px; border-radius: 6px; object-fit: cover;
    }
    .user-fallback {
        font-size: 22px; width: 28px; height: 28px; display:flex; align-items:center; justify-content:center;
    }
    .user-name {
        margin: 0; font-size: 0.95rem; font-weight: 600;
    }
</style>
@endsection

@section('content')
<div class="main-content">
    <h2 class="mb-3">{{ ucFirst($status) }} Users</h2>

    @if (session('success'))
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                swal({ title: @json(session('success')), icon: 'success' });
            });
        </script>
    @endif

    <div class="table-container">
        <table id="properties-table" class="table table-striped w-100">
            <thead>
                <tr>
                    <th style="min-width:220px;">Name</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Created At</th>
                    <th>Status</th>
                    <th style="min-width:160px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <div class="user-cell">
                                @if ($user->profile_picture)
                                    <img src="{{ $user->profile_picture }}" class="user-avatar" alt="Profile Picture">
                                @else
                                    <div class="user-fallback"><i class="fa fa-user-circle-o"></i></div>
                                @endif
                                <h5 class="user-name">{{ $user->name }}</h5>
                            </div>
                        </td>

                        <td>{{ $user->email ?: 'N/A' }}</td>
                        <td>{{ $user->phone_number ?: 'N/A' }}</td>
                        <td>{{ $user->created_at->format('D, M j, Y') }}</td>

                        <td>
                            <div class="btn-group">
                                <span class="badge bg-{{ $user->getBgColor($user->status) }} pointer" data-bs-toggle="dropdown">
                                    {{ ucFirst($user->status) }}
                                </span>
                                <div class="dropdown-menu">
                                    @foreach ($allStatus as $st)
                                        <a class="dropdown-item {{ $user->status == $st['name'] ? 'd-none' : '' }}"
                                           {{-- href="{{ route('adminUpdateUserStatus', ['id' => $user->id, 'status' => $st['name']]) }}" --}}
                                        >
                                            {{ ucFirst($st['name']) }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="d-flex align-items-center">
                                <a class="btn btn-light btn-sm border border-info me-1">
                                    <i class="fa fa-edit text-info"></i>
                                </a>

                                <a
                                   {{-- href="{{ route('singleUser', $user->id) }}" --}}
                                   href="/"
                                   class="btn btn-light btn-sm border border-primary me-1">
                                    <i class="fa fa-eye text-primary"></i>
                                </a>

                                <form method="POST"
                                      {{-- action="{{ route('adminUserDelete', $user->id) }}" --}}
                                      class="m-0">
                                    @csrf
                                    <input name="_method" type="hidden" value="DELETE">
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
                    <tr><td colspan="6" class="text-center text-muted">No users found.</td></tr>
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
        // DataTable
        $('#properties-table').DataTable({
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
    });
</script>
@endsection
