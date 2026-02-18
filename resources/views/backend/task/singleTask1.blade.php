@extends('backend.layouts.design')
@section('title')Single Task @endsection

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
<div class="container my-5">
    {{-- @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif --}}

    {{-- @if (session('success'))
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                confirmAlert("{{ session('success') }}", 'success');
            });
        </script>
    @endif --}}

    <!-- Task Status -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5>Task Status: <span class="badge bg-info">{{ $task->status }}</span></h5>
        <form action="{{route('updateTaskStatus', $task->id)}}" method="post">@csrf
            <div class="input-group" style="max-width: 300px;">
                <select class="form-select" name="task_status">
                    <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="accepted" {{ $task->status == 'accepted' ? 'selected' : '' }}>Accepted</option>
                    <option value="started" {{ $task->status == 'started' ? 'selected' : '' }}>Started</option>
                    <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $task->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="abandoned" {{ $task->status == 'abandoned' ? 'selected' : '' }}>Abandoned</option>

                </select>
                <button class="btn btn-primary" type="submit">Update Status</button>
            </div>
        </form>
    </div>

    <!-- Task Details -->
    <div class="row">
        <div class="col-md-6">
            <!-- Task Information -->
            <h2>{{ $task->task_title }}</h2>
            <p><strong>Date:</strong> {{ $task->task_date }}</p>
            <p><strong>Part of Day:</strong> {{ $task->task_part_of_day }}</p>
            <p><strong>Time of Day:</strong> {{ $task->task_time_of_day }}</p>
            <p><strong>Budget:</strong> ${{ number_format($task->task_budget, 2) }}</p>
        </div>
        <div class="col-md-6">
            <!-- Task Description -->
            <h5>Description</h5>
            <p>{{ $task->task_description }}</p>
        </div>
    </div>

    <!-- Task Images -->
    <div class="my-4">
        <h5>Task Images</h5>
        <div class="d-flex gap-2">
            @foreach ($task->task_images as $image)
                <img src="{{ $image }}" alt="Task Image" class="img-thumbnail" style="width: 150px;">
            @endforeach
        </div>
    </div>

    <!-- Creator Details -->
    <div class="card my-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="text-white">Creator Details</h5>
        </div>
        <div class="card-body">
            <div class="d-flex gap-2">
                <p class="border border-rounded p-1"><strong>Name:</strong> {{ $task->creator->name }}</p>
                <p class="border border-rounded p-1"><strong>Email:</strong> {{ $task->creator->email }}</p>
                <p class="border border-rounded p-1"><strong>Phone Number:</strong> {{ $task->creator->phone_number }}</p>

            </div>
        </div>
    </div>

    <!-- Offers Section -->
    <div class="card my-4">
        <div class="card-header bg-secondary text-white">
            <h5>Offers</h5>
        </div>
        <div class="card-body">
            @if($task->offers->isEmpty())
                <p>No offers available for this task.</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Freelancer</th>
                            <th>Proposal</th>
                            <th>Amount</th>
                            <th>Availability</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($task->offers as $offer)
                            <tr>
                                <td>{{ $offer->freelancer->name }}</td>
                                <td>{{ $offer->freelancer_proposal }}</td>
                                <td>${{ number_format($offer->amount_offered_by_freelancer, 2) }}</td>
                                <td>{{ $offer->freelancer_date_availability }} ({{ $offer->freelancer_start_time_available }} - {{ $offer->freelancer_end_time_available }})</td>
                                <td><span class="badge bg-warning">{{ $offer->status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
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
