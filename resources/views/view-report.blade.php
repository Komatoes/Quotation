@extends('layouts.app')
@section('content')
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
                @if(empty($readonly))
                <!-- Generate Link Button -->
                <button type="button" class="btn btn-outline-secondary mb-2" id="generateLinkBtn" onclick="copyPublicLink()">
                    <i class="bi bi-link-45deg"></i> Generate & Copy Public Link
                </button>
                @endif
@if(empty($readonly))
<script>
function copyPublicLink() {
    const token = "{{ $quotation->public_token }}";
    if (!token) {
        Swal.fire({
            icon: 'error',
            title: 'No Link Available',
            text: 'This quotation does not have a public link yet.'
        });
        return;
    }
    const link = `${window.location.origin}/quotation/public/${token}`;
    navigator.clipboard.writeText(link).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Link Copied!',
            text: link,
            timer: 1500,
            showConfirmButton: false
        });
    }, () => {
        Swal.fire({
            icon: 'error',
            title: 'Copy Failed',
            text: 'Could not copy the link.'
        });
    });
}
</script>
@endif
                <h3 class="mb-3">{{ $quotation->subject }}</h3>
                <p><b>Client Name:</b> {{ $quotation->client->first_name }} {{ $quotation->client->last_name }}</p>
                <p><b>Date:</b> {{ $quotation->created_at->format('F d, Y') }}</p>
            </div>
        </div>

        <!-- Materials Table -->
        <div class="card mb-3">
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Material</th>
                            <th>Estimated Quantity</th>
                            <th>Price/Unit</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($quotation->materials as $material)
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
                    <p class="mb-1"><b>Total Material Cost:</b> ₱{{ number_format($totalMaterial, 2) }}</p>
                    <p class="mb-1"><b>Labour Cost:</b> ₱{{ number_format($labourCost, 2) }}</p>
                    <h4 class="mb-0"><b>Grand Total:</b> ₱{{ number_format($totalMaterial + $labourCost, 2) }}</h4>
                </div>
            </div>
        </div>

        <!-- Progress Tracking -->
        <div class="card mb-3">
            <div class="card-body">
                <h3 class="mb-3">Progress Tracking</h3>




                <span id="pending-progress-display">Current Selection: {{ $quotation->latest_progress ?? 0 }}%</span>

                <div class="progress mb-3" style="height: 2rem;">
                    <div id="progress-bar" class="progress-bar" role="progressbar"
                        style="width:{{ $quotation->latest_progress ?? 0 }}%"
                        aria-valuenow="{{ $quotation->latest_progress ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>

                <!-- <label for="progress-input"><b>Set Progress:</b></label>
                        <input type="range" id="progress-input" class="form-range mb-3" min="0" max="100" step="5"
                        value="{{ $quotation->progress ?? 0 }}" oninput="updateProgress(this.value)">

                        <label for="progress-report"><b>Progress Report:</b></label>
                        <textarea id="progress-report" class="form-control mb-2" rows="3">{{ $quotation->latest_progress_report ?? '' }}</textarea>

                        <button class="btn btn-success mb-3" onclick="saveProgress({{ $quotation->id }})">Save Progress</button> -->


                <label for="progress-input"><b>Set Progress:</b></label>
                @if(empty($readonly))
                <input type="range" id="progress-input" class="form-range mb-3 w-100" min="0" max="100"
                    step="5" value="{{ $quotation->latest_progress ?? 0 }}" oninput="updateProgress(this.value)">
                @else
                <span>{{ $quotation->latest_progress ?? 0 }}%</span>
                @endif


                @if(empty($readonly))
                <div class="mb-3">
                    <label for="progress-report" class="form-label">Progress Report</label>
                    <textarea class="form-control" id="progress-report" rows="2"></textarea>
                </div>

                <button class="btn btn-success mb-3" id="save-button" onclick="saveProgress({{ $quotation->id }})">Save
                    Progress</button>
                @endif




                <h4 class="mb-3 border-bottom pb-2">Progress Report History</h4>
                @php
                    // REMOVE THIS BLOCK COMPLETELY
                    // dd(isset($reports));
                @endphp

                @php
                    $reports = $reports ?? collect();
                @endphp

                <div class="list-group" id="report-list">

                    @php
                        /* safe fallback so undefined var won't crash view */
                    @endphp
                    @forelse ($reports ?? [] as $report)
                        <div
                            class="list-group-item list-group-item-action flex-column align-items-start mb-2 border-primary border-3 border-start shadow-sm">

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
    let latestSavedProgress = {{ $quotation->latest_progress ?? 0 }}; // current highest progress

    function updateProgress(value) {
        const progressBar = document.getElementById('progress-bar');
        const display = document.getElementById('pending-progress-display');

        if (parseInt(value) < latestSavedProgress) {
            Swal.fire({
                icon: 'warning',
                title: 'Not Allowed',
                text: `You cannot set progress below ${latestSavedProgress}%!`,
                confirmButtonColor: '#3085d6'
            });
            document.getElementById('progress-input').value = latestSavedProgress;
            return;
        }

        if (progressBar) {
            progressBar.style.width = value + '%';
            progressBar.setAttribute('aria-valuenow', value);
            progressBar.textContent = value + '%';
        }

        if (display) {
            display.textContent = `Current Selection: ${value}% (Click "Save Progress" to lock)`;
        }
    }

    async function saveProgress(quotationId) {
        const progressInput = document.getElementById('progress-input');
        const reportInput = document.getElementById('progress-report');
        const saveButton = document.getElementById('save-button');
        const progressBar = document.getElementById('progress-bar');
        const display = document.getElementById('pending-progress-display');
        const reportList = document.getElementById('report-list');

        const progressValue = parseInt(progressInput.value);
        const progressReport = reportInput.value.trim();

        if (progressValue < latestSavedProgress) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Progress',
                text: `You cannot set progress below ${latestSavedProgress}%!`,
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        saveButton.disabled = true;
        saveButton.textContent = 'Saving...';

        try {
            const response = await fetch(`/quotations/${quotationId}/update-progress`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    progress: progressValue,
                    report: progressReport
                })
            });

            const data = await response.json();

            if (response.ok) {
                latestSavedProgress = progressValue;

                if (progressBar) {
                    progressBar.style.width = progressValue + '%';
                    progressBar.setAttribute('aria-valuenow', progressValue);
                    progressBar.textContent = progressValue + '%';
                    progressBar.classList.remove('bg-warning');
                    progressBar.classList.add('bg-success');
                }

                if (display) {
                    display.textContent = `Progress locked at ${progressValue}% ✔️`;
                    display.classList.add('text-success');
                }

                if (reportList) {
                    const newReport = document.createElement('div');
                    newReport.classList.add(
                        'list-group-item', 'list-group-item-action', 'flex-column',
                        'align-items-start', 'mb-2', 'border-primary', 'border-3',
                        'border-start', 'shadow-sm', 'fade-in'
                    );

                    newReport.innerHTML = `
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <h5 class="mb-1">
                                Progress Set To:
                                <span class="badge ${progressValue == 100 ? 'bg-success' : 'bg-primary'} fs-6">
                                    ${progressValue}%
                                </span>
                            </h5>
                            <small class="text-muted text-end">
                                Updated: ${new Date().toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })}<br>
                                at ${new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}
                            </small>
                        </div>
                        <hr class="my-2">
                        <p class="mb-1 text-dark">
                            <strong>Report Details:</strong>
                            ${progressReport || 'No details provided in this report entry.'}
                        </p>
                    `;
                    reportList.prepend(newReport);
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Progress Updated!',
                    text: data.message || 'Progress successfully saved.',
                    confirmButtonColor: '#28a745',
                    timer: 1200,
                    showConfirmButton: false
                });

                // ✅ If progress reaches 100%, confirm project completion
                if (progressValue === 100) {
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'question',
                            title: 'Mark as Completed?',
                            text: 'Progress has reached 100%. Do you want to mark this project as completed?',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, mark as completed',
                            cancelButtonText: 'Not yet',
                            confirmButtonColor: '#28a745',
                            cancelButtonColor: '#d33'
                        }).then(async (result) => {
                            if (result.isConfirmed) {
                                await markAsCompleted(quotationId);
                            }
                        });
                    }, 1300);
                }

                // Reset input states
                saveButton.textContent = 'Save Progress';
                saveButton.disabled = false;
                progressInput.disabled = false;
                reportInput.value = '';
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to update progress.',
                    confirmButtonColor: '#d33'
                });
                saveButton.textContent = 'Save Progress';
                saveButton.disabled = false;
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while updating progress.',
                confirmButtonColor: '#d33'
            });
            saveButton.textContent = 'Save Progress';
            saveButton.disabled = false;
        }
    }

    // ✅ Function to mark quotation as completed
    async function markAsCompleted(quotationId) {
        try {
            const response = await fetch(`/quotations/${quotationId}/mark-completed`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin'
            });

            const data = await response.json();

            if (response.ok) {
                Swal.fire({
                    icon: 'success',
                    title: 'Project Completed!',
                    text: data.message || 'Quotation successfully marked as completed.',
                    confirmButtonColor: '#28a745'
                });

                // Optional: update UI
                const statusBadge = document.getElementById('quotation-status');
                if (statusBadge) {
                    statusBadge.textContent = 'Completed';
                    statusBadge.classList.remove('bg-primary', 'bg-warning');
                    statusBadge.classList.add('bg-success');
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to mark as completed.',
                    confirmButtonColor: '#d33'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while marking as completed.',
                confirmButtonColor: '#d33'
            });
        }
    }
</script>

