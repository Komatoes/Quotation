@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <i class="fa-solid fa-chart-line" style="font-size: 2rem; color: #0d6efd;"></i>
                <h1 style="margin: 0; color: #212529;">Quotation Reports & Analytics</h1>
            </div>
            <p style="margin: 0; color: #6c757d; font-size: 0.95rem;">Monthly and yearly statistics for your quotations</p>
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div style="padding: 1.5rem; border-radius: 0.5rem; background-color: #f8f9fa; border-top: 3px solid #0d6efd;">
                <p style="margin: 0 0 0.5rem 0; color: #6c757d; font-size: 0.85rem; text-transform: uppercase; font-weight: 500; letter-spacing: 0.5px;">Total</p>
                <h3 style="margin: 0; color: #212529; font-weight: 600; font-size: 2rem;">{{ $stats['totalProjects'] }}</h3>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div style="padding: 1.5rem; border-radius: 0.5rem; background-color: #f8f9fa; border-top: 3px solid #6c757d;">
                <p style="margin: 0 0 0.5rem 0; color: #6c757d; font-size: 0.85rem; text-transform: uppercase; font-weight: 500; letter-spacing: 0.5px;">Draft</p>
                <h3 style="margin: 0; color: #212529; font-weight: 600; font-size: 2rem;">{{ $stats['draftProjects'] }}</h3>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div style="padding: 1.5rem; border-radius: 0.5rem; background-color: #f8f9fa; border-top: 3px solid #198754;">
                <p style="margin: 0 0 0.5rem 0; color: #198754; font-size: 0.85rem; text-transform: uppercase; font-weight: 500; letter-spacing: 0.5px;">Approved</p>
                <h3 style="margin: 0; color: #212529; font-weight: 600; font-size: 2rem;">{{ $stats['approvedProjects'] }}</h3>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div style="padding: 1.5rem; border-radius: 0.5rem; background-color: #f8f9fa; border-top: 3px solid #dc3545;">
                <p style="margin: 0 0 0.5rem 0; color: #dc3545; font-size: 0.85rem; text-transform: uppercase; font-weight: 500; letter-spacing: 0.5px;">Rejected</p>
                <h3 style="margin: 0; color: #212529; font-weight: 600; font-size: 2rem;">{{ $stats['rejectedProjects'] }}</h3>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div style="padding: 1.5rem; border-radius: 0.5rem; background-color: #f8f9fa; border-top: 3px solid #0dcaf0;">
                <p style="margin: 0 0 0.5rem 0; color: #0dcaf0; font-size: 0.85rem; text-transform: uppercase; font-weight: 500; letter-spacing: 0.5px;">Completed</p>
                <h3 style="margin: 0; color: #212529; font-weight: 600; font-size: 2rem;">{{ $stats['completedProjects'] }}</h3>
            </div>
        </div>
    </div>

    <!-- Monthly Stats Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card" style="border: 1px solid #e3e6f0; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);">
                <div class="card-header border-bottom bg-white" style="padding: 1.5rem;">
                    <h5 class="mb-0" style="color: #212529;">
                        <i class="fa-solid fa-calendar-days" style="color: #0d6efd; margin-right: 0.5rem;"></i>
                        Monthly Breakdown (Current Year)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #f8f9fa; border-bottom: 1px solid #e3e6f0;">
                                <tr>
                                    <th style="padding: 1rem; color: #495057; font-weight: 600; font-size: 0.875rem;">Month</th>
                                    <th style="padding: 1rem; text-align: center; color: #495057; font-weight: 600; font-size: 0.875rem;">Draft</th>
                                    <th style="padding: 1rem; text-align: center; color: #495057; font-weight: 600; font-size: 0.875rem;">Approved</th>
                                    <th style="padding: 1rem; text-align: center; color: #495057; font-weight: 600; font-size: 0.875rem;">Rejected</th>
                                    <th style="padding: 1rem; text-align: center; color: #495057; font-weight: 600; font-size: 0.875rem;">Completed</th>
                                    <th style="padding: 1rem; text-align: center; color: #495057; font-weight: 600; font-size: 0.875rem;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($monthlyStats as $month)
                                    <tr style="border-bottom: 1px solid #e3e6f0;">
                                        <td style="padding: 1rem; color: #212529; font-weight: 500;">{{ $month['fullMonth'] }}</td>
                                        <td style="padding: 1rem; text-align: center; color: #6c757d;">{{ $month['draft'] }}</td>
                                        <td style="padding: 1rem; text-align: center; color: #198754; font-weight: 500;">{{ $month['approved'] }}</td>
                                        <td style="padding: 1rem; text-align: center; color: #dc3545; font-weight: 500;">{{ $month['rejected'] }}</td>
                                        <td style="padding: 1rem; text-align: center; color: #0dcaf0; font-weight: 500;">{{ $month['completed'] }}</td>
                                        <td style="padding: 1rem; text-align: center; color: #212529; font-weight: 600;">
                                            {{ $month['draft'] + $month['approved'] + $month['rejected'] + $month['completed'] }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="padding: 2rem; text-align: center; color: #6c757d;">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Reasons Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card" style="border: 1px solid #e3e6f0; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);">
                <div class="card-header border-bottom bg-white" style="padding: 1.5rem;">
                    <h5 class="mb-0" style="color: #212529;">
                        <i class="fa-solid fa-ban" style="color: #dc3545; margin-right: 0.5rem;"></i>
                        Rejection Reasons Summary
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($rejectionReasons && count($rejectionReasons) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead style="background-color: #f8f9fa; border-bottom: 1px solid #e3e6f0;">
                                    <tr>
                                        <th style="padding: 1rem; color: #495057; font-weight: 600; font-size: 0.875rem;">Rejection Reason</th>
                                        <th style="padding: 1rem; text-align: center; color: #495057; font-weight: 600; font-size: 0.875rem;">Count</th>
                                        <th style="padding: 1rem; text-align: center; color: #495057; font-weight: 600; font-size: 0.875rem;">Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rejectionReasons as $reason)
                                        @php
                                            $percentage = round(($reason['count'] / $stats['rejectedProjects']) * 100);
                                        @endphp
                                        <tr style="border-bottom: 1px solid #e3e6f0;">
                                            <td style="padding: 1rem; color: #212529; font-weight: 500;">{{ $reason['rejection_reason'] ?? 'Unknown Reason' }}</td>
                                            <td style="padding: 1rem; text-align: center; color: #dc3545; font-weight: 600;">{{ $reason['count'] }}</td>
                                            <td style="padding: 1rem; text-align: center;">
                                                <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                                                    <div style="width: 120px; background-color: #e3e6f0; height: 6px; border-radius: 3px; overflow: hidden;">
                                                        <div style="background-color: #dc3545; height: 100%; width: {{ $percentage }}%; border-radius: 3px;"></div>
                                                    </div>
                                                    <span style="color: #212529; font-weight: 600; min-width: 35px;">{{ $percentage }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="text-align: center; padding: 2rem; color: #6c757d;">
                            <i class="fa-solid fa-circle-check" style="font-size: 2.5rem; margin-bottom: 1rem; color: #198754;"></i>
                            <p style="margin: 0; font-size: 1.1rem;">No rejections recorded</p>
                            <small style="color: #adb5bd;">All quotations have been approved or are pending</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Yearly Stats Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card" style="border: 1px solid #e3e6f0; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);">
                <div class="card-header border-bottom bg-white" style="padding: 1.5rem;">
                    <h5 class="mb-0" style="color: #212529;">
                        <i class="fa-solid fa-chart-bar" style="color: #0d6efd; margin-right: 0.5rem;"></i>
                        Yearly Breakdown (Last 5 Years)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #f8f9fa; border-bottom: 1px solid #e3e6f0;">
                                <tr>
                                    <th style="padding: 1rem; color: #495057; font-weight: 600; font-size: 0.875rem;">Year</th>
                                    <th style="padding: 1rem; text-align: center; color: #495057; font-weight: 600; font-size: 0.875rem;">Draft</th>
                                    <th style="padding: 1rem; text-align: center; color: #495057; font-weight: 600; font-size: 0.875rem;">Approved</th>
                                    <th style="padding: 1rem; text-align: center; color: #495057; font-weight: 600; font-size: 0.875rem;">Rejected</th>
                                    <th style="padding: 1rem; text-align: center; color: #495057; font-weight: 600; font-size: 0.875rem;">Completed</th>
                                    <th style="padding: 1rem; text-align: center; color: #495057; font-weight: 600; font-size: 0.875rem;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($yearlyStats as $year)
                                    <tr style="border-bottom: 1px solid #e3e6f0;">
                                        <td style="padding: 1rem; color: #212529; font-weight: 500;">{{ $year['year'] }}</td>
                                        <td style="padding: 1rem; text-align: center; color: #6c757d;">{{ $year['draft'] }}</td>
                                        <td style="padding: 1rem; text-align: center; color: #198754; font-weight: 500;">{{ $year['approved'] }}</td>
                                        <td style="padding: 1rem; text-align: center; color: #dc3545; font-weight: 500;">{{ $year['rejected'] }}</td>
                                        <td style="padding: 1rem; text-align: center; color: #0dcaf0; font-weight: 500;">{{ $year['completed'] }}</td>
                                        <td style="padding: 1rem; text-align: center; color: #212529; font-weight: 600;">
                                            {{ $year['draft'] + $year['approved'] + $year['rejected'] + $year['completed'] }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="padding: 2rem; text-align: center; color: #6c757d;">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to Dashboard Button -->
    <div class="row">
        <div class="col-12">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
