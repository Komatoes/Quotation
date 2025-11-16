@extends('layouts.app')
@include('include.head')
@section('content')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-fluid flex-grow-1 container-p-y">
            <div class="row g-6">

                <div class="col-xxl-4 col-md-3 col-7">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="badge p-2 bg-label-success mb-3 rounded">
                                <i class="fa-solid fa-folder-open fa-2x"></i>
                            </div>
                            <h5 class="card-title mb-1">Total Projects</h5>
                            <p class="text-heading mb-3 mt-1">{{ $totalProjects }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-4 col-md-3 col-7">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="badge p-2 bg-label-success mb-3 rounded">
                                <i class="fa-solid fa-spinner fa-2x"></i>
                            </div>
                            <h5 class="card-title mb-1">Current Projects</h5>
                            <p class="text-heading mb-3 mt-1">{{ $currentProjects }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-md-3 col-7">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="badge p-2 bg-label-success mb-3 rounded">
                                <i class="fa-solid fa-check-circle fa-2x"></i>
                            </div>
                            <h5 class="card-title mb-1">Finished Projects</h5>
                            <p class="text-heading mb-3 mt-1">{{ $finishedProjects }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <section id="material-list" class="mt-5">
                <div class="row">
                    @if (Auth::user()->can('view_materials'))
                        @include('materials')
                    @else
                        <div class="col-12">
                            <div class="card border-warning">
                                <div class="card-body text-center py-5">
                                    <i class="fa-solid fa-lock text-warning" style="font-size: 2rem;"></i>
                                    <h5 class="text-warning mt-3 mb-2">Materials Management</h5>
                                    <p class="text-muted mb-0">This section is restricted to owner only.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            <section id="current-projects" class="mt-5">
                <div class="row">
                    @include('currentprojects')
                </div>
            </section>

            <section id="draft-quotations" class="mt-5">
                <div class="row">
                    @include('draftprojects')
                </div>
            </section>
            <section id="archived-projects" class="mt-5">
                <div class="row">
                    @include('archivedprojects')
                </div>

            </section>

        </div>
    </div>
@endsection
