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
            <p><b>Project:</b> {{ $quotation->Subject }}</p>
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


                        

            <span id="pending-progress-display">Current Selection: {{ $quotation->latest_progress ?? 0 }}%</span>

            <div class="progress mb-3">
                <div id="progress-bar" class="progress-bar" role="progressbar" 
                    style="width:{{ $quotation->latest_progress ?? 0 }}%" 
                    aria-valuenow="{{ $quotation->latest_progress ?? 0 }}" 
                    aria-valuemin="0" 
                    aria-valuemax="100">
                </div>
            </div>

            <!-- <label for="progress-input"><b>Set Progress:</b></label>
            <input type="range" id="progress-input" class="form-range mb-3" min="0" max="100" step="5" 
            value="{{ $quotation->progress ?? 0 }}" oninput="updateProgress(this.value)">

            <label for="progress-report"><b>Progress Report:</b></label>
            <textarea id="progress-report" class="form-control mb-2" rows="3">{{ $quotation->latest_progress_report ?? '' }}</textarea>

            <button class="btn btn-success mb-3" onclick="saveProgress({{ $quotation->id }})">Save Progress</button> -->


            <label for="progress-input"><b>Set Progress:</b></label>
            <input type="range" id="progress-input" class="form-range mb-3" min="0" max="100" step="5" 
                value="{{ $quotation->latest_progress ?? 0 }}" oninput="updateProgress(this.value)">


            <div class="mb-3">
                <label for="progress-report" class="form-label">Progress Report</label>
                <textarea class="form-control" id="progress-report" rows="2"></textarea> 
            </div>

            <button class="btn btn-success mb-3" id="save-button" 
                    onclick="saveProgress({{ $quotation->id }})">Save Progress</button>




            <h4 class="mb-3 border-bottom pb-2">Progress Report History</h4>
           @php
    // REMOVE THIS BLOCK COMPLETELY
    // dd(isset($reports)); 
@endphp

@php
    $reports = $reports ?? collect();
@endphp

<div class="list-group">
    @php /* safe fallback so undefined var won't crash view */ @endphp
@forelse ($reports ?? [] as $report)
        <div class="list-group-item list-group-item-action flex-column align-items-start mb-2 border-primary border-3 border-start shadow-sm">
            
            <div class="d-flex w-100 justify-content-between align-items-center">
                
                {{-- 1. Progress Value (Uses the 'progress' column) --}}
                <h5 class="mb-1">
                    Progress Set To: 
                    <span class="badge {{ $report->progress == 100 ? 'bg-success' : 'bg-primary' }} fs-6">
                        {{ $report->progress }}%
                    </span>
                </h5>
                
                {{-- 2. Timestamp --}}
                <small class="text-muted text-end">
                    Updated: **{{ $report->created_at->format('M d, Y') }}**<br>
                    at {{ $report->created_at->format('h:i A') }}
                </small>
            </div>
            
            <hr class="my-2">

            {{-- 3. Report Text (Uses the 'report' column directly) --}}
            <p class="mb-1 text-dark">
                <strong>Report Details:</strong> 
                {{ $report->report ?? 'No details provided in this report entry.' }}
            </p>
        </div>
    @empty
        <div class="alert alert-info" role="alert">
            No progress reports have been logged for this quotation yet.
        </div>
    @endforelse
</div>




        </div>
    </div>
</div>  
@endsection




<script>
    
    // This function can remain, as it just updates the visual bar
    function updateProgress(value) {
        // Assuming you have a bootstrap progress bar with these IDs
        const progressBar = document.getElementById('progress-bar');
        if (display) {
            // Update the text to show the currently selected value
            display.textContent = 'Current Selection: ' + value + '% (Click "Save Progress" to lock)';
        }
    }

    async function saveProgress(quotationId) {
        const progressInput = document.getElementById('progress-input');
        const reportInput = document.getElementById('progress-report');
        const saveButton = document.getElementById('save-button');
        
        const progressValue = progressInput.value;
        // Fix: Use 'report' to match the Controller's validation and Model's fillable
        const progressReport = reportInput.value; 

        // 1. Temporarily disable button and input while saving
        saveButton.disabled = true;
        progressInput.disabled = true;
        saveButton.textContent = 'Saving...';

        try {
            const response = await fetch(`/quotations/${quotationId}/update-progress`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    progress: progressValue,
                    // Fix: Use the correct key 'report'
                    report: progressReport 
                })
            });

            const data = await response.json();

            if (response.ok) {
                // 2. SUCCESS: Permanent lock for this save action
                alert(data.message);
                saveButton.textContent = 'Saved & Locked ✔️';
                // The inputs remain disabled, achieving the "cannot be revert" goal
                
            } else {
                // 3. FAILURE: Re-enable controls if the save failed (e.g., reversion attempt)
                alert(data.message || 'Failed to update progress.');
                
                // Re-enable input and button so the user can correct the value
                progressInput.disabled = false;
                saveButton.disabled = false;
                saveButton.textContent = 'Save Progress';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while updating progress.');
            
            // Re-enable on complete unexpected failure
            progressInput.disabled = false;
            saveButton.disabled = false;
            saveButton.textContent = 'Save Progress';
        }
    }




</script>
