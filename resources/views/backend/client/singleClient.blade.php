@extends('backend.layouts.design')
@section('title')Freelancers @endsection

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
    <!-- Client Information -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5>Client Profile</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 text-center">
                    <img src="{{ $client->profile_picture ?? 'https://via.placeholder.com/150' }}" alt="Profile Picture" class="img-fluid rounded-circle mb-3" style="width: 150px;">
                </div>
                <div class="col-md-8">
                    <p><strong>Name:</strong> {{ $client->name }}</p>
                    <p><strong>Email:</strong> {{ $client->email }}</p>
                    <p><strong>Status:</strong> <span class="badge bg-info">{{ ucfirst($client->status) }}</span></p>
                    <p><strong>About:</strong> {{ $client->about ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Client Tasks -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5>Client Tasks</h5>
        </div>
        <div class="card-body">
            @if ($client->clientTasks->isEmpty())
                <p>No tasks available for this client.</p>
            @else
                @foreach ($client->clientTasks as $task)
                    <div class="border p-3 mb-3">
                        <!-- Task Information -->
                        <h5>{{ $task->task_title }}</h5>
                        <p><strong>Date:</strong> {{ $task->task_date }}</p>
                        <p><strong>Time:</strong> {{ $task->task_time_of_day }}</p>
                        <p><strong>Budget:</strong> ${{ number_format($task->task_budget, 2) }}</p>
                        <p><strong>Description:</strong> {{ $task->task_description }}</p>
                        <div class="d-flex gap-2">
                            @foreach ($task->task_images as $image)
                                <img src="{{ $image }}" alt="Task Image" class="img-thumbnail" style="width: 100px;">
                            @endforeach
                        </div>
                        <p class="mt-3"><strong>Status:</strong> <span class="badge bg-warning">{{ ucfirst($task->status) }}</span></p>

                        <!-- Toggle Offers -->
                        <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#task-{{ $task->id }}-offers" aria-expanded="false">
                            View Offers ({{ $task->offers_count }})
                        </button>

                        <!-- Offers Section -->
                        <div class="collapse mt-3" id="task-{{ $task->id }}-offers">
                            @if ($task->offers_count > 0)
                                <table class="table">
                                    <thead class="table-light">
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
                                                <td>
                                                    {{ $offer->freelancer_date_availability }}<br>
                                                    {{ $offer->freelancer_start_time_available }} - {{ $offer->freelancer_end_time_available }}
                                                </td>
                                                <td><span class="badge bg-warning">{{ ucfirst($offer->status) }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p>No offers available for this task.</p>
                            @endif
                        </div>
                    </div>
                @endforeach
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

