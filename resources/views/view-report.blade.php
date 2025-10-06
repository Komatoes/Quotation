@extends('layouts.app')
@section('content')
<div class="container-fluid py-3">

    <!-- Header -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body text-center bg-light">
            <h1 class="h3 mb-0 text-dark">{{ $quotation->name }}</h1>
        </div>
    </div>

    <!-- Project Info -->
    <div class="card mb-3">
        <div class="card-body">
            <p><b>Client Name:</b> {{ $quotation->client->first_name }} {{ $quotation->client->last_name }}</p>
            <p><b>Project:</b> {{ $quotation->name }}</p>
            <p><b>Date:</b> {{ $quotation->created_at->format('F d, Y') }}</p>
        </div>
    </div>

    <!-- Materials Table -->
    <div class="card mb-3">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Material</th>
                        <th>Estimated Quantity</th>
                        <th>Price/Unit</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quotation->materials as $material)
                    <tr>
                        <td>{{ $material->name }}</td>
                        <td>{{ $material->pivot->quantity ?? 0 }}</td>
                        <td>₱{{ number_format($material->unit_price, 2) }}</td>
                        <td>₱{{ number_format($material->unit_price * ($material->pivot->quantity ?? 0), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @php
                $totalMaterial = $quotation->materials->sum(fn($m) => $m->unit_price * ($m->pivot->quantity ?? 0));
                $labourCost = $quotation->labour_cost ?? 0;
            @endphp

            <div class="text-end mt-3">
                <p><b>Total Material Cost:</b> ₱{{ number_format($totalMaterial, 2) }}</p>
                <p><b>Labour Cost:</b> ₱{{ number_format($labourCost, 2) }}</p>
                <h4><b>Grand Total:</b> ₱{{ number_format($totalMaterial + $labourCost, 2) }}</h4>
            </div>
        </div>
    </div>

    <!-- Progress Tracking -->
    <div class="card mb-3">
        <div class="card-body">
            <h3 class="mb-3">Progress Tracking</h3>
            <div class="progress mb-3">
                <div id="progress-bar" class="progress-bar" role="progressbar" style="width:{{ $quotation->progress ?? 0 }}%" aria-valuenow="{{ $quotation->progress ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>

            <label for="progress-input"><b>Set Progress:</b></label>
            <input type="range" id="progress-input" class="form-range mb-3" min="0" max="100" step="5" value="{{ $quotation->progress ?? 0 }}" oninput="updateProgress(this.value)">

            <label for="progress-report"><b>Progress Report:</b></label>
            <textarea id="progress-report" class="form-control mb-2" rows="3">{{ $quotation->latest_progress_report ?? '' }}</textarea>
            <button class="btn btn-success mb-3" onclick="saveProgress({{ $quotation->id }})">Save Progress</button>


        </div>
    </div>
</div>  
@endsection
