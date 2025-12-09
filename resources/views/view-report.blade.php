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
                
                @php
                    // Handle both regular and additional quotations
                    $isAdditional = isset($isAdditional) && $isAdditional;
                    $client = $isAdditional ? $quotation->parentQuotation->client : $quotation->client;
                @endphp
                
                <p><strong>Customer:</strong> <span id="clientName">{{ $client->first_name }}
                        {{ $client->last_name }}</span>
                    @php
                        $qStatus = strtolower($quotation->status->status_name ?? '');
                        $canEditClientInReport = empty($readonly) && $qStatus !== 'completed' && $qStatus !== 'rejected';
                    @endphp
                    @if ($canEditClientInReport)
                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" id="editClientBtn">Edit
                            Client</button>
                    @endif
                </p>
                <p><strong>Contact:</strong> <span id="clientContact">{{ $client->contact_no }}</span></p>
                <p><strong>Address:</strong> <span id="clientAddress">{{ $client->address }}</span></p>
                
                @if ($isAdditional)
                    <p><strong>Parent Quotation:</strong> {{ $quotation->parentQuotation->subject }}
                        (ID: {{ $quotation->parent_quotation_id }})
                    </p>
                @endif
                
                @if (!empty($quotation->description))
                    <p class="text-muted mb-2"><strong>Description:</strong>
                        {!! nl2br(e($quotation->description)) !!}
                    </p>
                @endif

                @php
                    $qStatus = strtolower($quotation->status->status_name ?? '');
                    if ($isAdditional) {
                        // Additional Quotation badge logic
                        if ($qStatus === 'completed') {
                            $badgeText = 'Additional Quotation Completed';
                            $badgeClass = 'bg-success';
                        } elseif ($qStatus === 'rejected') {
                            $badgeText = 'Additional Quotation Rejected';
                            $badgeClass = 'bg-danger';
                        } elseif ($quotation->customer_approved && $quotation->progress >= 100) {
                            // ✅ FIXED: Only show as approved if BOTH customer_approved AND progress >= 100
                            $badgeText = 'Approved & Attached to Parent';
                            $badgeClass = 'bg-success';
                        } elseif ($quotation->customer_approved) {
                            $badgeText = 'Approved by Client (Pending Attachment)';
                            $badgeClass = 'bg-success';
                        } else {
                            $badgeText = 'Draft - Awaiting Approval';
                            $badgeClass = 'bg-warning text-dark';
                        }
                    } else {
                        // Regular Quotation badge logic
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
                    }
                @endphp

                <div class="mt-3">
                    <span class="badge {{ $badgeClass }} mb-3 d-inline-flex align-items-center"
                        id="quotation-status-badge">
                        {{ $badgeText }}
                    </span>
                </div>

                @if ($qStatus === 'rejected' && !empty($quotation->rejection_reason))
                    <div class="alert alert-danger mt-3" role="alert">
                        <h5 class="alert-heading">
                            <i class="fa-solid fa-exclamation-circle me-2"></i> Rejection Reason
                        </h5>
                        <p class="mb-0">{!! nl2br(e($quotation->rejection_reason)) !!}</p>
                    </div>
                @endif

                @php
                    // Show contract details only if quotation is approved or later
                    $isApproved = $quotation->status_id >= 2; // 2 = approved
                @endphp

                @if ($isApproved && $quotation->contract_subject)
                    <div class="mt-4 pt-3 border-top">
                        <h5 class="mb-3">Contract Details</h5>
                        <p><strong>Contract Subject:</strong> <span>{{ $quotation->contract_subject }}</span></p>
                        @if ($quotation->project_start_date)
                            <p><strong>Project Start Date:</strong>
                        <span>{{ \Carbon\Carbon::parse($quotation->project_start_date)->setTimezone(config('app.timezone'))->format('M d, Y') }}</span>
                            </p>
                        @endif
                        @if ($quotation->project_end_date)
                            <p><strong>Project End Date:</strong>
                                <span>{{ \Carbon\Carbon::parse($quotation->project_end_date)->setTimezone(config('app.timezone'))->format('M d, Y') }}</span>
                            </p>
                        @endif
                        <p>
                            <strong>Contract Status:</strong>
                            @if ($quotation->with_contract)
                                <span class="badge bg-success">With Contract</span>
                            @else
                                <span class="badge bg-secondary">Without Contract</span>
                            @endif
                        </p>
                    </div>
                @endif

                @if (empty($readonly))
                    <button type="button" class="btn btn-outline-secondary mt-3" id="generateLinkBtn"
                        title="Generate & Copy Public Link">
                        <i class="fa-solid fa-link me-1"></i> Generate Link
                    </button>
                @endif

                @if ($isAdditional && !$quotation->customer_approved && empty($readonly))
                    {{-- ✅ Approve button for additional quotations --}}
                    <button type="button" class="btn btn-success mt-3" id="approveAdditionalBtn"
                        data-additional-id="{{ $quotation->id }}"
                        title="Approve this additional quotation">
                        <i class="fa-solid fa-check-circle me-1"></i> Approve Additional Quotation
                    </button>
                @endif
                
                @if (!$isAdditional)
                    <button type="button" class="btn btn-outline-secondary mt-3" id="additionalQtnBtn"
                        title="Create Additional Quotation for this Project"
                        data-parent-id="{{ $quotation->id }}"
                        data-bs-toggle="modal" data-bs-target="#additionalQuotationModal">
                        <i class="fa-solid fa-plus me-1"></i> Additional Quotation
                    </button>
                    <button type="button" class="btn btn-outline-info mt-3" id="viewAdditionalQtnBtn"
                        title="View Additional Quotations for this Project"
                        data-parent-id="{{ $quotation->id }}">
                        <i class="fa-solid fa-list me-1"></i> View Additional Quotations
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
                                <td>{{ $material->pivot->quantity ?? 0 }} {{ $material->unit }}</td>
                                @if (Auth::user()->can('view_prices'))
                                    @php
                                        // For additional quotations, use pivot unit_cost; for regular, use material unit_price
                                        $unitPrice = $isAdditional && isset($material->pivot->unit_cost) 
                                            ? $material->pivot->unit_cost 
                                            : $material->unit_price;
                                        $lineTotal = $unitPrice * ($material->pivot->quantity ?? 0);
                                    @endphp
                                    <td>₱{{ number_format($unitPrice, 2) }}</td>
                                    <td>₱{{ number_format($lineTotal, 2) }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    @if (Auth::user()->can('view_prices'))
                        @php
                            // Calculate totals properly for both quotation types
                            $totalMaterial = 0;
                            foreach ($quotation->materials as $material) {
                                // Use pivot unit_cost for additional quotations, unit_price for regular
                                $unitPrice = $isAdditional && isset($material->pivot->unit_cost) 
                                    ? $material->pivot->unit_cost 
                                    : $material->unit_price;
                                $totalMaterial += $unitPrice * ($material->pivot->quantity ?? 0);
                            }
                            
                            $laborfee = $quotation->labor_fee ?? 0;
                            $deliveryFee = $quotation->delivery_fee ?? 0;
                            $grandTotal = $totalMaterial + $laborfee + $deliveryFee;
                        @endphp
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Total Material Cost:</td>
                                <td>₱{{ number_format($totalMaterial, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Labor Fee:</td>
                                <td>₱{{ number_format($laborfee, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Delivery Fee:</td>
                                <td>₱{{ number_format($deliveryFee, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                                <td class="fw-bold text-primary fs-5">₱{{ number_format($grandTotal, 2) }}</td>
                            </tr>
                        </tfoot>
                    @else
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-center">
                                    <div class="alert alert-warning mb-0" role="alert">
                                        <i class="fa-solid fa-lock me-2"></i>Pricing information is restricted to authorized users.
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <!-- Progress Tracking -->
        <div class="card mb-3">
            <div class="card-body">
                <h3 class="mb-3">Progress Tracking</h3>

                @php
                    // Use parent quotation's progress if viewing additional quotation
                    $progressQuotation = $isAdditional ? $quotation->parentQuotation : $quotation;
                    $currentProgress = $progressQuotation->latest_progress ?? 0;
                @endphp

                <!-- ✅ NEW: Display project timeline -->
                @if ($progressQuotation->project_start_date && $progressQuotation->project_end_date)
                    @php
                        $startDate = \Carbon\Carbon::parse($progressQuotation->project_start_date);
                        $endDate = \Carbon\Carbon::parse($progressQuotation->project_end_date);
                        $today = \Carbon\Carbon::now();
                        $projectStatus = 'not-started';
                        $statusIcon = '⏳';
                        $statusText = 'Project Not Started';
                        
                        if ($today->greaterThanOrEqualTo($startDate) && $today->lessThanOrEqualTo($endDate)) {
                            $projectStatus = 'ongoing';
                            $statusIcon = '▶️';
                            $statusText = 'Project In Progress';
                        } elseif ($today->greaterThan($endDate)) {
                            $projectStatus = 'overdue';
                            $statusIcon = '⚠️';
                            $statusText = 'Project Past End Date';
                        }
                    @endphp
                    
                    <div class="alert alert-info mb-3" role="alert">
                        <div class="row">
                            <div class="col-md-6">
                    <p class="mb-1"><strong>Start Date:</strong> {{ $startDate->setTimezone(config('app.timezone'))->format('M d, Y') }}</p>
                        <p class="mb-0"><strong>End Date:</strong> {{ $endDate->setTimezone(config('app.timezone'))->format('M d, Y') }}</p>
                            </div>
                            <div class="col-md-6 text-end">
                                <p class="mb-0"><strong>{{ $statusIcon }} Status:</strong> <span class="badge 
                                    @if ($projectStatus === 'not-started') bg-warning text-dark
                                    @elseif ($projectStatus === 'ongoing') bg-info
                                    @else bg-danger
                                    @endif">{{ $statusText }}</span></p>
                            </div>
                        </div>
                    </div>
                @endif

                <span id="pending-progress-display">Current Selection: {{ $currentProgress }}%</span>

                <div class="progress mb-3" style="height: 2rem;">
                    <div id="progress-bar" class="progress-bar" role="progressbar"
                        style="width:{{ $currentProgress }}%"
                        aria-valuenow="{{ $currentProgress }}" aria-valuemin="0" aria-valuemax="100">

                    </div>
                </div>

                <!-- <label for="progress-input"><b>Set Progress:</b></label>
                                    <input type="range" id="progress-input" class="form-range mb-3" min="0" max="100" step="5"
                                    value="{{ $quotation->progress ?? 0 }}" oninput="updateProgress(this.value)">

                                    <label for="progress-report"><b>Progress Report:</b></label>
                                    <textarea id="progress-report" class="form-control mb-2" rows="3">{{ $quotation->latest_progress_report ?? '' }}</textarea>

                                    <button class="btn btn-success mb-3" onclick="saveProgress({{ $quotation->id }})">Save Progress</button> -->



                @php
                    $qStatus = strtolower($quotation->status->status_name ?? '');
                    $isCompleted = $qStatus === 'completed';
                    $canModifyProgress = empty($readonly) && !$isCompleted;
                @endphp

                <label for="progress-input"><b>Set Progress:</b></label>
                <input type="range" id="progress-input" class="form-range mb-3 w-100" min="0" max="100"
                    step="5" value="{{ $currentProgress }}" oninput="updateProgress(this.value)"
                    @if (!$canModifyProgress) disabled @endif>
                @if (!$canModifyProgress)
                    <div class="small text-muted">Progress is locked for this quotation.</div>
                @endif


                <div class="mb-3">
                    <label for="progress-report" class="form-label">Progress Report</label>
                    <textarea class="form-control" id="progress-report" rows="2" @if (!$canModifyProgress) disabled @endif>{{ $progressQuotation->latest_progress_report ?? '' }}</textarea>
                </div>

                <button class="btn btn-success mb-3" id="save-button" onclick="saveProgress({{ $progressQuotation->id }})"
                    @if (!$canModifyProgress) disabled title="Cannot modify progress" @endif>Save
                    Progress</button>

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
                                    Updated: **{{ $report->created_at->setTimezone(config('app.timezone'))->format('M d, Y') }}**<br>
                                    at {{ $report->created_at->setTimezone(config('app.timezone'))->format('h:i A') }}
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
                    @endforelse
                </div>
            </div>
        </div>
    </div>


    <!-- Threaded Comments Section (admin/staff) -->
    @php
        // Use parent comments if viewing additional quotation, otherwise use quotation's own comments
        $commentsToDisplay = isset($parentComments) ? $parentComments : $quotation->comments;
        
        // For additional quotations, use parent quotation ID for comments endpoint
        // For regular quotations, use the quotation ID
        $commentQuotationId = $isAdditional ? $quotation->parentQuotation->id : $quotation->id;
        $commentQuotationType = 'quotation'; // Always use 'quotation' for fetching comments
    @endphp
    @include('components.threaded-comments-admin', [
        'comments' => $commentsToDisplay,
        'quotationId' => $commentQuotationId,
        'quotationType' => $commentQuotationType,
    ])
@endsection




<script>
    let latestSavedProgress = {{ $currentProgress ?? 0 }}; // current highest progress
    const isStaff = @json(auth()->user() && auth()->user()->hasRole('staff'));
    
    // ✅ NEW: Get project dates from quotation for validation
    const projectStartDate = '{{ $progressQuotation->project_start_date ?? null }}';
    const projectEndDate = '{{ $progressQuotation->project_end_date ?? null }}';
    const today = new Date();
    today.setHours(0, 0, 0, 0); // Reset time to start of day for accurate comparison

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

        // ✅ Validation: Cannot set progress below latest saved
        if (progressValue < latestSavedProgress) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Progress',
                text: `You cannot set progress below ${latestSavedProgress}%!`,
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        // ✅ NEW: Check if project has started yet
        // Project cannot have progress updates before the start date
        if (projectStartDate) {
            const startDate = new Date(projectStartDate);
            startDate.setHours(0, 0, 0, 0);
            
            if (today < startDate) {
                const formattedDate = startDate.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
                Swal.fire({
                    icon: 'warning',
                    title: 'Project Not Started Yet',
                    text: `This project starts on ${formattedDate}. You cannot update progress before the start date.`,
                    confirmButtonColor: '#3085d6'
                });
                return;
            }
        }

        // ✅ NEW: Prevent setting progress to 100% immediately on newly approved quotation
        // Only allow 100% if progress is already above 0 (i.e., not the first update)
        if (progressValue === 100 && latestSavedProgress === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Cannot Complete Yet',
                text: 'You cannot mark the project as 100% complete when first starting. Please update progress incrementally.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        // ✅ NEW: Show confirmation dialog before saving progress
        const confirmationResult = await Swal.fire({
            icon: 'question',
            title: 'Confirm Progress Update',
            html: `<p>Are you sure you want to set progress to <strong>${progressValue}%</strong>?</p>
                   <p style="color: #666; font-size: 0.9em;">This action will be recorded and cannot be reversed.</p>`,
            showCancelButton: true,
            confirmButtonText: 'Yes, Confirm',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33'
        });

        // If user cancels, don't proceed
        if (!confirmationResult.isConfirmed) {
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

                //If progress reaches 100%, confirm project completion
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
                                window.location.href = "/dashboard";
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
                generateLinkBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span>Copying...';
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

<!-- Approve Additional Quotation Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const approveAdditionalBtn = document.getElementById('approveAdditionalBtn');
        if (approveAdditionalBtn) {
            approveAdditionalBtn.addEventListener('click', async function() {
                const additionalId = this.dataset.additionalId;
                
                // Confirm action
                const result = await Swal.fire({
                    title: 'Approve Additional Quotation?',
                    text: 'This will mark the additional quotation as approved and set progress to 100%.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, approve it!'
                });

                if (!result.isConfirmed) return;

                approveAdditionalBtn.disabled = true;
                approveAdditionalBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Approving...';

                try {
                    const response = await fetch(`/additional-quotations/${additionalId}/approve`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({})
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        Swal.fire({
                            title: 'Approved!',
                            text: 'Additional quotation has been approved.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            // Reload page to refresh status
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Failed to approve quotation', 'error');
                        approveAdditionalBtn.disabled = false;
                        approveAdditionalBtn.innerHTML = '<i class="fa-solid fa-check-circle me-1"></i> Approve Additional Quotation';
                    }
                } catch (error) {
                    console.error('Approval error:', error);
                    Swal.fire('Error', 'Something went wrong!', 'error');
                    approveAdditionalBtn.disabled = false;
                    approveAdditionalBtn.innerHTML = '<i class="fa-solid fa-check-circle me-1"></i> Approve Additional Quotation';
                }
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
                        <input type="text" class="form-control" id="clientFirstName" name="first_name"
                            value="{{ $client->first_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="clientLastName" class="form-label">Last name</label>
                        <input type="text" class="form-control" id="clientLastName" name="last_name"
                            value="{{ $client->last_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="clientContactInput" class="form-label">Contact</label>
                        <input type="text" class="form-control" id="clientContactInput" name="contact_no"
                            value="{{ $client->contact_no }}">
                    </div>
                    <div class="mb-3">
                        <label for="clientAddressInput" class="form-label">Address</label>
                        <textarea class="form-control" id="clientAddressInput" name="address" rows="3">{{ $client->address }}</textarea>
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
    document.addEventListener('DOMContentLoaded', function() {
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
            saveBtn.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

            const payload = {
                first_name: document.getElementById('clientFirstName').value,
                last_name: document.getElementById('clientLastName').value,
                contact_no: document.getElementById('clientContactInput').value,
                address: document.getElementById('clientAddressInput').value
            };

            // Client-side sanitization & validation
            const sanitize = (s, max = 1000) => String(s || '').replace(/[\x00-\x1F\x7F<>]/g, '')
                .slice(0, max).trim();
            const sanitizeContact = (s) => String(s || '').replace(/[^0-9+\-()\s]/g, '').slice(0,
                40).trim();

            payload.first_name = sanitize(payload.first_name, 100);
            payload.last_name = sanitize(payload.last_name, 100);
            payload.address = sanitize(payload.address, 1000);
            payload.contact_no = sanitizeContact(payload.contact_no);

            if (!payload.first_name) {
                Swal.fire('Validation', 'First name is required.', 'warning');
                saveBtn.disabled = false;
                saveBtn.innerHTML = 'Save changes';
                return;
            }
            if (!payload.last_name) {
                Swal.fire('Validation', 'Last name is required.', 'warning');
                saveBtn.disabled = false;
                saveBtn.innerHTML = 'Save changes';
                return;
            }
            if (!payload.address) {
                Swal.fire('Validation', 'Address is required.', 'warning');
                saveBtn.disabled = false;
                saveBtn.innerHTML = 'Save changes';
                return;
            }

            try {
                const res = await fetch(`/clients/{{ $client->id }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .content
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (data.success) {
                    document.getElementById('clientName').textContent =
                        `${payload.first_name} ${payload.last_name}`;
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

<!-- Additional Quotation Modal -->
<div class="modal fade" id="additionalQuotationModal" tabindex="-1" aria-labelledby="additionalQuotationLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="additionalQuotationLabel">Create Additional Quotation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="additionalQuotationForm">
                    <div class="mb-3">
                        <label for="additionalSubject" class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="additionalSubject" name="subject"
                            placeholder="e.g., Additional Materials & Services" required>
                    </div>
                    <div class="mb-3">
                        <label for="additionalDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="additionalDescription" name="description" rows="3"
                            placeholder="Details about this additional quotation"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="createAdditionalQuotationBtn">Create Quotation</button>
            </div>
        </div>
    </div>
</div>

<!-- Additional Quotation Button Handler -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const additionalQtnBtn = document.getElementById('additionalQtnBtn');
        const createBtn = document.getElementById('createAdditionalQuotationBtn');
        const modalEl = document.getElementById('additionalQuotationModal');
        
        if (!additionalQtnBtn || !createBtn || !modalEl) return;

        const bsModal = new bootstrap.Modal(modalEl);
        let parentQuotationId = null;

        // Open modal when button clicked
        additionalQtnBtn.addEventListener('click', function() {
            parentQuotationId = this.getAttribute('data-parent-id');
            // Reset form
            document.getElementById('additionalQuotationForm').reset();
            bsModal.show();
        });

        // Create quotation when button clicked
        createBtn.addEventListener('click', async function() {
            const subject = document.getElementById('additionalSubject').value.trim();
            const description = document.getElementById('additionalDescription').value.trim();

            // Validate subject
            if (!subject) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please enter a quotation subject',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            // Disable button during submission
            const btnText = createBtn.innerHTML;
            createBtn.disabled = true;
            createBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';

            try {
                const response = await fetch('{{ route('quotations.additional.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        parent_quotation_id: parentQuotationId,
                        subject: subject,
                        description: description,
                        labor_fee: 0,
                        delivery_fee: 0
                    })
                });

                const data = await response.json();

                // Handle both 200 and 201 status codes
                if ((response.status === 200 || response.status === 201) && data.success) {
                    bsModal.hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message || 'Additional quotation created successfully.',
                        confirmButtonColor: '#28a745',
                        allowOutsideClick: false
                    }).then(() => {
                        // Redirect to the additional quotation editor template
                        if (data.additional_quotation_id) {
                            window.location.href = '/additional-quotations/' + data.additional_quotation_id + '/edit';
                        }
                    });
                } else {
                    // Handle error response
                    const errorMessage = data.message || data.error || 'Failed to create additional quotation';
                    const errorDetails = data.errors ? Object.values(data.errors).flat().join('\n') : '';
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage + (errorDetails ? '\n' + errorDetails : ''),
                        confirmButtonColor: '#d33'
                    });

                    // Re-enable button
                    createBtn.disabled = false;
                    createBtn.innerHTML = btnText;
                }
            } catch (error) {
                console.error('Request error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'An unexpected error occurred. Please check your connection and try again.',
                    confirmButtonColor: '#d33'
                });
                createBtn.disabled = false;
                createBtn.innerHTML = btnText;
            }
        });
    });
</script>

<!-- View Additional Quotations Modal -->
<div class="modal fade" id="additionalQuotationsModal" tabindex="-1" aria-labelledby="additionalQuotationsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="additionalQuotationsLabel">Additional Quotations</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="additionalQuotationsList"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Additional Quotations Handler -->
<script>
    // Helper function to escape HTML and prevent XSS
    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    document.addEventListener('DOMContentLoaded', function() {
        const viewAdditionalQtnBtn = document.getElementById('viewAdditionalQtnBtn');
        const modalEl = document.getElementById('additionalQuotationsModal');
        
        if (!viewAdditionalQtnBtn || !modalEl) return;

        const bsModal = new bootstrap.Modal(modalEl);

        viewAdditionalQtnBtn.addEventListener('click', async function() {
            const parentId = this.getAttribute('data-parent-id');
            
            try {
                // Fetch additional quotations as JSON
                const response = await fetch(`/quotations/${parentId}/additional-quotations-json`);
                const data = await response.json();

                if (!response.ok || !data.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to load additional quotations',
                        confirmButtonColor: '#d33'
                    });
                    return;
                }

                // Display quotations
                const container = document.getElementById('additionalQuotationsList');
                container.innerHTML = '';

                if (data.quotations.length === 0) {
                    container.innerHTML = '<div class="alert alert-info">No additional quotations yet.</div>';
                } else {
                    data.quotations.forEach((quotation, index) => {
                        const div = document.createElement('div');
                        div.classList.add('card', 'mb-3', 'border-primary', 'border-start', 'border-5');

                        const statusBadgeClass = {
                            'draft': 'bg-secondary',
                            'pending': 'bg-warning text-dark',
                            'approved': 'bg-success',
                            'rejected': 'bg-danger',
                            'completed': 'bg-success',
                            'ongoing': 'bg-info'
                        }[quotation.status_name?.toLowerCase()] || 'bg-secondary';

                        div.innerHTML = `
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">${escapeHtml(quotation.subject)}</h6>
                                    <small class="text-muted">ID: ${quotation.id}</small>
                                </div>
                                <span class="badge ${statusBadgeClass}">
                                    ${escapeHtml(quotation.status_name || 'Unknown')}
                                </span>
                            </div>
                            <div class="card-body">
                                <p class="mb-2"><strong>Description:</strong> <br> 
                                    ${quotation.description ? escapeHtml(quotation.description) : '<em class="text-muted">No description</em>'}
                                </p>
                                <p class="mb-2"><strong>Created:</strong> ${new Date(quotation.created_at).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })}</p>
                                <p class="mb-2"><strong>Materials:</strong> ${quotation.materials_count || 0}</p>
                                <div class="d-flex gap-2">
                                    ${quotation.customer_approved || quotation.status_id >= 2
                                        ? `<a href="/additional-quotations/${quotation.id}/view" class="btn btn-sm btn-primary">
                                            <i class="fa-solid fa-eye me-1"></i> View/Edit
                                        </a>`
                                        : `<a href="/additional-quotations/${quotation.id}/edit" class="btn btn-sm btn-primary">
                                            <i class="fa-solid fa-edit me-1"></i> View/Edit
                                        </a>`
                                    }
                                    <button class="btn btn-sm btn-danger delete-additional-quotation" data-id="${quotation.id}">
                                        <i class="fa-solid fa-trash me-1"></i> Delete
                                    </button>
                                </div>
                            </div>
                        `;
                        container.appendChild(div);
                    });
                }

                // Show modal
                bsModal.show();

                // Add delete event listeners to newly created delete buttons
                document.querySelectorAll('.delete-additional-quotation').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const quotationId = this.getAttribute('data-id');
                        deleteAdditionalQuotation(quotationId, parentId);
                    });
                });
            } catch (error) {
                console.error('Error fetching additional quotations:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Failed to fetch additional quotations. Please try again.',
                    confirmButtonColor: '#d33'
                });
            }
        });
    });

    // Function to delete additional quotation
    function deleteAdditionalQuotation(quotationId, parentId) {
        Swal.fire({
            icon: 'warning',
            title: 'Delete Additional Quotation?',
            text: 'This action cannot be undone.',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel'
        }).then(async (result) => {
            if (!result.isConfirmed) return;

            try {
                const response = await fetch(`/additional-quotations/${quotationId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: data.message || 'Additional quotation deleted successfully.',
                        confirmButtonColor: '#28a745',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Reload the modal content by clicking the view button again
                        const viewBtn = document.getElementById('viewAdditionalQtnBtn');
                        if (viewBtn) {
                            viewBtn.click();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to delete quotation.',
                        confirmButtonColor: '#d33'
                    });
                }
            } catch (error) {
                console.error('Error deleting quotation:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while deleting the quotation.',
                    confirmButtonColor: '#d33'
                });
            }
        });
    }
</script>

