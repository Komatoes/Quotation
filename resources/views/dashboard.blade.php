@extends('layouts.app')
@include('include.head')
@section('content')
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row g-6">

                <!-- Total Profit -->
                <div class="col-xxl-4 col-md-3 col-7">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="badge p-2 bg-label-danger mb-3 rounded">
                                <i class="icon-base ti tabler-credit-card icon-28px"></i>
                            </div>
                            <h5 class="card-title mb-1">Total Projects</h5>
                            <p class="text-heading mb-3 mt-1">1.28k</p>

                        </div>
                    </div>
                </div>

                <!-- Total Sales -->
                <div class="col-xxl-4 col-md-3 col-7">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="badge p-2 bg-label-success mb-3 rounded">
                                <i class="icon-base ti tabler-credit-card icon-28px"></i>
                            </div>
                            <h5 class="card-title mb-1">Current Projects</h5>
                            <p class="text-heading mb-3 mt-1">24.67k</p>

                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-md-3 col-7">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="badge p-2 bg-label-success mb-3 rounded">
                                <i class="icon-base ti tabler-credit-card icon-28px"></i>
                            </div>
                            <h5 class="card-title mb-1">Finished Projects</h5>
                            <p class="text-heading mb-3 mt-1">24.67k</p>
                        </div>
                    </div>
                </div>
            </div>
            <section id="material-list" class="mt-5">
                <div class="row">
                    @include('materials')
                </div>
            </section>

        </div>
    </div>
@endsection
