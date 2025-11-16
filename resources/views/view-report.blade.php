@extends($layout ?? 'layouts.app')
@section('content')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <div class="container-fluid py-3">

        <!-- Header -->
        <div class="card mb-4">
            <div class="card-body text-center bg-light rounded shadow-sm">
                @php
                    $qStatus = strtolower($quotation->status->status_name ?? '');
                    if ($qStatus === 'completed') {
                        $headerText = 'Project Completed';
                        $headerClass = 'text-success';
                    } elseif ($qStatus === 'rejected') {
                        $headerText = 'Quotation Rejected';
                        $headerClass = 'text-danger';
                    } elseif ($quotation->customer_approved) {
                        $headerText = 'Ongoing Project';
                        $headerClass = 'text-primary';
                    } else {
                        $headerText = 'Creating Quotation';
                        $headerClass = 'text-dark';
                    }
                @endphp
                <h1 class="h3 mb-0 {{ $headerClass }}">{{ $headerText }}</h1>
            </div>
        </div>

        <!-- Quotation Info -->
        <div class="card mb-4">
            <div class="card-body">
                <h3 class="mb-3">{{ $quotation->subject }}</h3>
                <p><strong>Customer:</strong> <span id="clientName">{{ $quotation->client->first_name }} {{ $quotation->client->last_name }}</span>
                    @if(empty($readonly))
                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" id="editClientBtn">Edit Client</button>
                    @endif
                </p>
                <p><strong>Contact:</strong> <span id="clientContact">{{ $quotation->client->contact_no }}</span></p>
                <p><strong>Address:</strong> <span id="clientAddress">{{ $quotation->client->address }}</span></p>

                @php
                    $qStatus = strtolower($quotation->status->status_name ?? '');
                    if ($qStatus === 'completed') {
                        $badgeText = 'Project has been completed';
                        $badgeClass = 'bg-success';
                    } elseif ($qStatus === 'rejected') {
                        $badgeText = 'Quotation is rejected';
                        $badgeClass = 'bg-danger';
                    } elseif ($quotation->customer_approved) {
                        $badgeText = 'Approved by Client';
                        $badgeClass = 'bg-success';
                    } else {
                        $badgeText = 'Awaiting Client Approval';
                        $badgeClass = 'bg-warning text-dark';
                    }
                @endphp

                <div class="mt-3">
                    <span class="badge {{ $badgeClass }} mb-3 d-inline-flex align-items-center" id="quotation-status-badge">
                        {{ $badgeText }}
                    </span>
                </div>

                @if(empty($readonly))
                <button type="button" class="btn btn-outline-secondary mt-3" id="generateLinkBtn" title="Generate & Copy Public Link">
                    <i class="fa-solid fa-link me-1"></i> Generate Link
                </button>
                @endif
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
                            @if (Auth::user()->can('view_prices'))
                                <th>Price/Unit</th>
                                <th>Total</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($quotation->materials as $material)
                            <tr>
                                <td>{{ $material->name }}</td>
                                <td>{{ $material->pivot->quantity ?? 0 }}</td>
                                @if (Auth::user()->can('view_prices'))
                                    <td>₱{{ number_format($material->unit_price, 2) }}</td>
                                    <td>₱{{ number_format($material->unit_price * ($material->pivot->quantity ?? 0), 2) }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @php
                    $totalMaterial = $quotation->materials->sum(fn($m) => $m->unit_price * ($m->pivot->quantity ?? 0));
                    $laborfee = $quotation->labor_fee ?? 0;
                    $deliveryFee = $quotation->delivery_fee ?? 0;
                    $grandTotal = $totalMaterial + $laborfee + $deliveryFee;
                @endphp

                <div class="text-end mt-3">
                    @if (Auth::user()->can('view_prices'))
                        <p class="mb-1"><b>Total Material Cost:</b> ₱{{ number_format($totalMaterial, 2) }}</p>
                        <p class="mb-1"><b>Labor Fee:</b> ₱{{ number_format($laborfee, 2) }}</p>
                        <p class="mb-1"><b>Delivery Fee:</b> ₱{{ number_format($deliveryFee, 2) }}</p>
                        <hr class="mt-2 mb-2">
                        <h4 class="mb-0"><b>Grand Total:</b> ₱{{ number_format($grandTotal, 2) }}</h4>
                    @else
                        <div class="alert alert-warning" role="alert">
                            <i class="fa-solid fa-lock me-2"></i>Pricing information is restricted to administrators.
                        </div>
                    @endif
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


    <!-- Threaded Comments Section -->
    @include('components.threaded-comments', ['comments' => $quotation->comments, 'quotationId' => $quotation->id])

@endsection




<script>

    let latestSavedProgress = {{ $quotation->latest_progress ?? 0 }}; // current highest progress
    const isStaff = @json(auth()->user() && auth()->user()->hasRole('staff'));

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
                if (progressValue === 100 && !isStaff) {
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

<!-- Generate & Copy Public Link Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const generateLinkBtn = document.getElementById('generateLinkBtn');
        if (generateLinkBtn) {
            generateLinkBtn.addEventListener('click', async function() {
                generateLinkBtn.disabled = true;
                generateLinkBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Copying...';
                try {
                    const token = "{{ $quotation->public_token ?? '' }}";
                    if (!token) {
                        Swal.fire({
                            title: 'No Link Available',
                            text: 'This quotation does not have a public link yet.',
                            icon: 'warning',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        const link = `${window.location.origin}/quotation/public/${token}`;
                        await navigator.clipboard.writeText(link);
                        Swal.fire({
                            title: 'Link Copied!',
                            text: link,
                            icon: 'success',
                            timer: 1200,
                            showConfirmButton: false
                        });
                    }
                } catch (err) {
                    Swal.fire('Error', 'Could not copy the link.', 'error');
                }
                generateLinkBtn.disabled = false;
                generateLinkBtn.innerHTML = '<i class="fa-solid fa-link me-1"></i> Generate Link';
            });
        }
    });
</script>

<!-- Edit Client Modal -->
<div class="modal fade" id="editClientModal" tabindex="-1" aria-labelledby="editClientLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editClientLabel">Edit Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editClientForm">
                    <div class="mb-3">
                        <label for="clientFirstName" class="form-label">First name</label>
                        <input type="text" class="form-control" id="clientFirstName" name="first_name" value="{{ $quotation->client->first_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="clientLastName" class="form-label">Last name</label>
                        <input type="text" class="form-control" id="clientLastName" name="last_name" value="{{ $quotation->client->last_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="clientContactInput" class="form-label">Contact</label>
                        <input type="text" class="form-control" id="clientContactInput" name="contact_no" value="{{ $quotation->client->contact_no }}">
                    </div>
                    <div class="mb-3">
                        <label for="clientAddressInput" class="form-label">Address</label>
                        <textarea class="form-control" id="clientAddressInput" name="address" rows="3">{{ $quotation->client->address }}</textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveClientBtn">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Client Handler Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editBtn = document.getElementById('editClientBtn');
        const saveBtn = document.getElementById('saveClientBtn');
        const modalEl = document.getElementById('editClientModal');
        if (!editBtn || !saveBtn || !modalEl) return;

        const bsModal = new bootstrap.Modal(modalEl);

        editBtn.addEventListener('click', () => {
            bsModal.show();
        });

        saveBtn.addEventListener('click', async () => {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

            const payload = {
                first_name: document.getElementById('clientFirstName').value,
                last_name: document.getElementById('clientLastName').value,
                contact_no: document.getElementById('clientContactInput').value,
                address: document.getElementById('clientAddressInput').value
            };

            // Client-side sanitization & validation
            const sanitize = (s, max = 1000) => String(s || '').replace(/[\x00-\x1F\x7F<>]/g, '').slice(0, max).trim();
            const sanitizeContact = (s) => String(s || '').replace(/[^0-9+\-()\s]/g, '').slice(0, 40).trim();

            payload.first_name = sanitize(payload.first_name, 100);
            payload.last_name = sanitize(payload.last_name, 100);
            payload.address = sanitize(payload.address, 1000);
            payload.contact_no = sanitizeContact(payload.contact_no);

            if (!payload.first_name) { Swal.fire('Validation', 'First name is required.', 'warning'); saveBtn.disabled = false; saveBtn.innerHTML = 'Save changes'; return; }
            if (!payload.last_name) { Swal.fire('Validation', 'Last name is required.', 'warning'); saveBtn.disabled = false; saveBtn.innerHTML = 'Save changes'; return; }
            if (!payload.address) { Swal.fire('Validation', 'Address is required.', 'warning'); saveBtn.disabled = false; saveBtn.innerHTML = 'Save changes'; return; }

            try {
                const res = await fetch(`/clients/{{ $quotation->client->id }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (data.success) {
                    document.getElementById('clientName').textContent = `${payload.first_name} ${payload.last_name}`;
                    document.getElementById('clientContact').textContent = payload.contact_no;
                    document.getElementById('clientAddress').textContent = payload.address;

                    Swal.fire('Success', 'Client updated!', 'success');
                    bsModal.hide();
                } else {
                    Swal.fire('Error', data.message || 'Failed to update', 'error');
                }
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'Something went wrong!', 'error');
            }

            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Save changes';
        });
    });
</script>

