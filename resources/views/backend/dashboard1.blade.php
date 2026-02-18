@extends('backend.layouts.design')
@section('title')Dashboard @endsection

@section('extra_css')@endsection

@section('content')
<div class="content">

    <!-- Start Content-->
    <div class="container-fluid">

        <div class="row">
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
                    <h4 class="page-title">Dashboard</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-12">
                                <div class="text-center">
                                    <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $users }}</span></h3>
                                    <p class="text-muted mb-1 text-truncate">Total Users</p>
                                </div>
                            </div>
                        </div> <!-- end row-->
                    </div>
                </div> <!-- end widget-rounded-circle-->
            </div> <!-- end col-->

            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-12">
                                <div class="text-center">
                                    <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $userPending }}</span></h3>
                                    <p class="text-muted mb-1 text-truncate">Pending Users</p>
                                </div>
                            </div>
                        </div> <!-- end row-->
                    </div>
                </div> <!-- end widget-rounded-circle-->
            </div> <!-- end col-->

            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-12">
                                <div class="text-center">
                                    <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $userApproved }}</span></h3>
                                    <p class="text-muted mb-1 text-truncate">Approved Users</p>
                                </div>
                            </div>
                        </div> <!-- end row-->
                    </div>
                </div> <!-- end widget-rounded-circle-->
            </div> <!-- end col-->

            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-12">
                                <div class="text-center">
                                    <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $userSuspended }}</span></h3>
                                    <p class="text-muted mb-1 text-truncate">Suspended Users</p>
                                </div>
                            </div>
                        </div> <!-- end row-->
                    </div>
                </div> <!-- end widget-rounded-circle-->
            </div> <!-- end col-->

            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-12">
                                <div class="text-center">
                                    <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $clients }}</span></h3>
                                    <p class="text-muted mb-1 text-truncate">Total Clients</p>
                                </div>
                            </div>
                        </div> <!-- end row-->
                    </div>
                </div> <!-- end widget-rounded-circle-->
            </div> <!-- end col-->

            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-12">
                                <div class="text-center">
                                    <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $freelancers }}</span></h3>
                                    <p class="text-muted mb-1 text-truncate">Total Freelancers</p>
                                </div>
                            </div>
                        </div> <!-- end row-->
                    </div>
                </div> <!-- end widget-rounded-circle-->
            </div> <!-- end col-->

            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-12">
                                <div class="text-center">
                                    <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $tasks }}</span></h3>
                                    <p class="text-muted mb-1 text-truncate">Total Tasks</p>
                                </div>
                            </div>
                        </div> <!-- end row-->
                    </div>
                </div> <!-- end widget-rounded-circle-->
            </div> <!-- end col-->

            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-12">
                                <div class="text-center">
                                    <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $taskPending }}</span></h3>
                                    <p class="text-muted mb-1 text-truncate">Task Pending</p>
                                </div>
                            </div>
                        </div> <!-- end row-->
                    </div>
                </div> <!-- end widget-rounded-circle-->
            </div> <!-- end col-->

            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-12">
                                <div class="text-center">
                                    <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $taskStarted }}</span></h3>
                                    <p class="text-muted mb-1 text-truncate">Task Started</p>
                                </div>
                            </div>
                        </div> <!-- end row-->
                    </div>
                </div> <!-- end widget-rounded-circle-->
            </div> <!-- end col-->

            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-12">
                                <div class="text-center">
                                    <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $taskCompleted }}</span></h3>
                                    <p class="text-muted mb-1 text-truncate">Task Completed</p>
                                </div>
                            </div>
                        </div> <!-- end row-->
                    </div>
                </div> <!-- end widget-rounded-circle-->
            </div> <!-- end col-->

            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-12">
                                <div class="text-center">
                                    <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $taskCancelled }}</span></h3>
                                    <p class="text-muted mb-1 text-truncate">Task Cancelled</p>
                                </div>
                            </div>
                        </div> <!-- end row-->
                    </div>
                </div> <!-- end widget-rounded-circle-->
            </div> <!-- end col-->

            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-12">
                                <div class="text-center">
                                    <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $taskAbandoned }}</span></h3>
                                    <p class="text-muted mb-1 text-truncate">Task Abandoned</p>
                                </div>
                            </div>
                        </div> <!-- end row-->
                    </div>
                </div> <!-- end widget-rounded-circle-->
            </div> <!-- end col-->

            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-12">
                                <div class="text-center">
                                    <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $taskOffers }}</span></h3>
                                    <p class="text-muted mb-1 text-truncate">Task Offer Pending</p>
                                </div>
                            </div>
                        </div> <!-- end row-->
                    </div>
                </div> <!-- end widget-rounded-circle-->
            </div> <!-- end col-->

            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-12">
                                <div class="text-center">
                                    <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $taskOfferAccepted }}</span></h3>
                                    <p class="text-muted mb-1 text-truncate">Task Offer Accepted</p>
                                </div>
                            </div>
                        </div> <!-- end row-->
                    </div>
                </div> <!-- end widget-rounded-circle-->
            </div> <!-- end col-->

            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-12">
                                <div class="text-center">
                                    <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $taskOfferDeclined }}</span></h3>
                                    <p class="text-muted mb-1 text-truncate">Task Offer Declined</p>
                                </div>
                            </div>
                        </div> <!-- end row-->
                    </div>
                </div> <!-- end widget-rounded-circle-->
            </div> <!-- end col-->

            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-12">
                                <div class="text-center">
                                    <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $walletTransactions }}</span></h3>
                                    <p class="text-muted mb-1 text-truncate">Wallet Transactions</p>
                                </div>
                            </div>
                        </div> <!-- end row-->
                    </div>
                </div> <!-- end widget-rounded-circle-->
            </div> <!-- end col-->

            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-12">
                                <div class="text-center">
                                    <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $walletEarnings }}</span></h3>
                                    <p class="text-muted mb-1 text-truncate">Wallet Earnings</p>
                                </div>
                            </div>
                        </div> <!-- end row-->
                    </div>
                </div> <!-- end widget-rounded-circle-->
            </div> <!-- end col-->

            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-12">
                                <div class="text-center">
                                    <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $walletPayouts }}</span></h3>
                                    <p class="text-muted mb-1 text-truncate">Wallet Payouts</p>
                                </div>
                            </div>
                        </div> <!-- end row-->
                    </div>
                </div> <!-- end widget-rounded-circle-->
            </div> <!-- end col-->

        </div>
        <!-- end row-->

    </div> <!-- container -->

</div>
@endsection

@section('extra_js')@endsection
