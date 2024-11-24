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
    <!-- Freelancer Profile -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5>Freelancer Profile</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center">
                        <img src="{{ $freelancer->profile_picture ?? 'https://via.placeholder.com/150' }}" alt="Profile Picture" class="img-fluid rounded-circle mb-3" style="width: 150px;">
                    </div>
                </div>
                <div class="col-md-8">
                    <p><strong>Name:</strong> {{ $freelancer->name }}</p>
                    <p><strong>Email:</strong> {{ $freelancer->email }}</p>
                    <p><strong>Status:</strong> <span class="badge bg-info">{{ ucfirst($freelancer->status) }}</span></p>
                    <p><strong>About:</strong> {{ $freelancer->about ?? 'N/A' }}</p>
                    <p><strong>Skills:</strong> {{ $freelancer->skills ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Freelancer Task Offers -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5>Task Offers</h5>
        </div>
        <div class="card-body">
            @if($freelancer->freelancerTaskOffers->isEmpty())
                <p>No task offers available for this freelancer.</p>
            @else
                <table class="table">
                    <thead class="table-light">
                        <tr>
                            <th>Task Title</th>
                            <th>Proposal</th>
                            <th>Offered Amount</th>
                            <th>Availability</th>
                            <th>Status</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($freelancer->freelancerTaskOffers as $offer)
                            <tr>
                                <td>{{ $offer->task_detail->task_title }}</td>
                                <td>{{ $offer->freelancer_proposal }}</td>
                                <td>${{ number_format($offer->amount_offered_by_freelancer, 2) }}</td>
                                <td>
                                    {{ $offer->freelancer_date_availability }}<br>
                                    {{ $offer->freelancer_start_time_available }} - {{ $offer->freelancer_end_time_available }}
                                </td>
                                <td><span class="badge bg-warning">{{ ucfirst($offer->status) }}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#task-{{ $offer->id }}" aria-expanded="false">
                                        View Task
                                    </button>
                                </td>
                            </tr>
                            <!-- Task Details (Collapsible) -->
                            <tr class="collapse" id="task-{{ $offer->id }}">
                                <td colspan="6">
                                    <div class="p-3 bg-light border">
                                        <h6>Task Details</h6>
                                        <p><strong>Client Name:</strong> {{ $offer->task_detail->creator->name }}</p>
                                        <p><strong>Description:</strong> {{ $offer->task_detail->task_description }}</p>
                                        <p><strong>Budget:</strong> ${{ number_format($offer->task_detail->task_budget, 2) }}</p>
                                        <div class="d-flex gap-2">
                                            @foreach ($offer->task_detail->task_images as $image)
                                                <img src="{{ $image }}" alt="Task Image" class="img-thumbnail" style="width: 100px;">
                                            @endforeach
                                        </div>
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
