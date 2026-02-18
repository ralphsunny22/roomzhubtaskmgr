@extends('backend.layouts.design')
@section('title')Clients @endsection

@php

@endphp

@section('extra_css')
    <style>
        .pointer{
            cursor: pointer;
        }
        .selected .sorting_1{
            color: white;
        }
    </style>
@endsection

@section('content')
<div class="content">

    {{-- @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif --}}

    @if (session('success'))
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                confirmAlert("{{ session('success') }}", 'success');
            });
        </script>
    @endif


    <!-- Start Content-->
    <div class="container-fluid">

        {{-- @if(Session::has('success'))
            <div class="alert alert-success mb-3 text-center">
                {{ Session::get('success') }}
            </div>
        @endif --}}

        <div class="row d-none">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <form class="d-flex align-items-center mb-3">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control border" id="dash-daterange">
                                <span class="input-group-text bg-blue border-blue text-white">
                                    <i class="mdi mdi-calendar-range"></i>
                                </span>
                            </div>
                            <a href="javascript: void(0);" class="btn btn-blue btn-sm ms-2">
                                <i class="mdi mdi-autorenew"></i>
                            </a>
                            <a href="javascript: void(0);" class="btn btn-blue btn-sm ms-1">
                                <i class="mdi mdi-filter-variant"></i>
                            </a>
                        </form>
                    </div>
                    <h4 class="page-title">{{ucFirst($status)}} Clients</h4>
                </div>
            </div>
        </div>
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{route('adminDashboard')}}">Dashboard</a> </li>
                            <li class="breadcrumb-item active"><a href="javascript: void(0);">Clients</a></li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{ucFirst($status)}} Clients</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- table part-->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <h4 class="header-title">User</h4>
                        <p class="text-muted font-13 mb-4">
                            This shows the list of clients.
                        </p>

                        <div>
                            <table id="datatable-buttons" class="table table-striped nowrap w-100" style="overflow-x: auto;">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone Number</th>
                                        <th>Tasks</th>
                                        <th>Created At</th>
                                        <th>Status</th>
                                        <td>Action</td>

                                        {{-- <th>Total bedroom</th>
                                        <th>Total bathroom</th>
                                        <th>Rent per week($)</th> --}}
                                    </tr>
                                </thead>

                                <tbody>
                                    @if (count($clients) > 0)
                                        @foreach ($clients as $user)
                                            <tr>
                                                <td>
                                                    @if ($user->profile_picture)
                                                        <img src="{{ $user->profile_picture }}" alt="Profile Picture" style="width: 25px; height: 25px; border-radius: 5px;">
                                                    @else
                                                        <div style="font-size: 24px; text-align:center;"><i class="fa fa-user-circle-o"></i></div>
                                                    @endif
                                                    <h5>{{ $user->name }}</h5>

                                                </td>
                                                <td>
                                                    {{ $user->email ? $user->email : 'N/A' }}
                                                </td>

                                                <td>{{ $user->phone_number ? $user->phone_number : 'N/A' }}</td>

                                                <td>{{ $user->clientTasks ? $user->clientTasks->count() : 'N/A' }}</td>

                                                <td>{{ $user->created_at->format('D, M j, Y') }}</td>

                                                <td>
                                                    <div class="btn-group">
                                                        <span class="badge bg-{{ $user->getBgColor($user->status) }} pointer" data-bs-toggle="dropdown">{{ucFirst($user->status)}}</span>
                                                        <div class="dropdown-menu">
                                                            @foreach ($allStatus as $status)
                                                                <a class="dropdown-item {{ $user->status == $status['name'] ? 'd-none' : '' }}"
                                                                    {{-- href="{{ route('adminUpdateClientstatus', ['id' => $user->id, 'status' => $status['name']]) }}" --}}
                                                                >
                                                                    {{ ucFirst($status['name']) }}
                                                                </a>
                                                            @endforeach

                                                        </div>
                                                    </div>
                                                </td>

                                                <td>
                                                    <div class="d-flex justify-content-space-between align-items-center">
                                                        <a class="btn btn-light btn-sm border border-info me-1 d-none"><i class="fa fa-edit text-info"></i></a>
                                                        <a
                                                        href="{{ route('singleClient', $user->id) }}"
                                                        class="btn btn-light btn-sm border border-primary me-1"><i class="fa fa-eye text-primary"></i></a>

                                                        <form method="POST"
                                                        {{-- action="{{ route('adminUserDelete', $user->id) }}" --}}
                                                        >
                                                            @csrf
                                                            <input name="_method" type="hidden" value="DELETE">
                                                            <button type="submit" class="btn btn-light btn-sm border border-danger me-1 show_confirm" data-toggle="tooltip" title='Delete'><i class="fa fa-trash text-danger"></i></button>
                                                        </form>
                                                    </div>
                                                </td>

                                                {{-- <td>{{ $user->user_total_bedroom }}</td>
                                                <td>{{ $user->user_total_bathroom }}</td>
                                                <td>{{ $user->user_rent_per_week }}</td> --}}
                                            </tr>

                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>

                    </div> <!-- end card body-->
                </div> <!-- end card -->
            </div><!-- end col-->
        </div>
        <!-- end row-->
        <!-- end row-->



    </div> <!-- container -->

</div>
@endsection

@section('extra_js')
<!-- Datatables init -->
<script src="{{asset('/assets/js/pages/datatables.init.js')}}"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script type="text/javascript">

    $('.show_confirm').click(function(event) {
          var form =  $(this).closest("form");
          var name = $(this).data("name");
          event.preventDefault();
          swal({
              title: `Are you sure you want to delete this record?`,
              text: "If you delete this, it will be gone forever.",
              icon: "warning",
              buttons: true,
              dangerMode: true, //ok btn red color
          })
          .then((willDelete) => {
            if (willDelete) {
            form.submit();
            // confirmAlert('Removed Successfully', "success")

            } else {
                console.log('nothing');

            }
          });
    });

      function confirmAlert(title, icon) {
        swal({
            title: title,
            icon: icon,
        })
      }

</script>

@endsection
