@extends('layouts.public')

@section('content')
    @php
        // Detect quotation type: Regular Quotation vs Additional Quotation
        $isAdditional = isset($isAdditional) && $isAdditional;

        // Get client info based on quotation type
        if ($isAdditional) {
            $client = $quotation->parentQuotation->client;
        } else {
            $client = $quotation->client;
        }
    @endphp

    <div class="container-fluid">
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
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h3 class="mb-0">{{ $quotation->subject }}</h3>
                    @php
                        // Prefer a public-token export route for public pages; fall back to the ID-based route
                        $exportRoute = null;
                        if (!empty($quotation->public_token)) {
                            // expects a route named `quotations.export.public` that accepts ['token' => $token]
                            try {
                                $exportRoute = route('quotations.export.public', ['token' => $quotation->public_token]);
                            } catch (\Exception $e) {
                                // route may not exist; fall back below
                                $exportRoute = null;
                            }
                        }
                        if (empty($exportRoute)) {
                            $exportRoute = route('quotations.export', ['id' => $quotation->id]);
                        }
                    @endphp

                </div>
                <p><strong>Customer:</strong> <span id="clientName">{{ $client->first_name }}
                        {{ $client->last_name }}</span></p>
                <p><strong>Contact:</strong> <span id="clientContact">{{ $client->contact_no }}</span></p>
                <p><strong>Address:</strong> <span id="clientAddress">{{ $client->address }}</span></p>
                <p><strong>Description:</strong> <span
                        id="quotationDescription">{{ $quotation->description ?? 'N/A' }}</span></p>




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
                    <span class="fw-500">
                        @php
                            $qStatus = strtolower($quotation->status->status_name ?? '');
                            if ($qStatus === 'completed') {
                                $statusIcon = '<i class="fa-solid fa-circle text-success me-2" style="font-size: 0.5rem;"></i>';
                                $statusText = 'Project has been completed';
                            } elseif ($qStatus === 'rejected') {
                                $statusIcon = '<i class="fa-solid fa-circle text-danger me-2" style="font-size: 0.5rem;"></i>';
                                $statusText = 'Quotation is rejected';
                            } elseif ($quotation->customer_approved) {
                                $statusIcon = '<i class="fa-solid fa-circle text-success me-2" style="font-size: 0.5rem;"></i>';
                                $statusText = 'Approved by Client';
                            } else {
                                $statusIcon = '<i class="fa-solid fa-circle text-warning me-2" style="font-size: 0.5rem;"></i>';
                                $statusText = 'Awaiting Client Approval';
                            }
                        @endphp
                        {!! $statusIcon !!}{{ $statusText }}
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
                                <span><i class="fa-solid fa-circle text-success me-2" style="font-size: 0.5rem;"></i>With Contract</span>
                            @else
                                <span><i class="fa-solid fa-circle text-secondary me-2" style="font-size: 0.5rem;"></i>Without Contract</span>
                            @endif
                        </p>
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-2 mt-3">
                    @if (!$quotation->customer_approved)
                        <button id="approve-btn" class="btn btn-success">
                            <i class="fa-solid fa-check-circle me-1"></i> Approve Quotation
                        </button>
                    @endif
                    <button type="button" class="btn btn-outline-info" id="viewAdditionalQtnBtn"
                        title="View Additional Quotations for this Project" data-parent-id="{{ $quotation->id }}">
                        <i class="fa-solid fa-list me-1"></i> View Additional Quotations
                    </button>
                    <a href="{{ $exportRoute }}" class="btn btn-info" target="_blank" rel="noopener">
                        <i class="fa-solid fa-file-word me-1"></i> Export as DOC
                    </a>
                    <button type="button" class="btn btn-outline-secondary" id="viewRevisionsBtn"
                        data-id="{{ $quotation->id }}">
                        <i class="bi bi-clock-history me-1"></i> View Revisions
                    </button>
                </div>
            </div>
        </div>

        <!-- Materials Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Materials & Services</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Material/Service</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($materials as $material)
                            @php
                                // For additional quotations, unit_cost is in pivot; for regular, it's direct property
                                $unitPrice =
                                    isset($isAdditional) && $isAdditional
                                        ? $material->pivot->unit_cost ?? 0
                                        : $material->unit_price ?? 0;
                                $quantity = $material->pivot->quantity ?? 0;
                            @endphp
                            <tr>
                                <td>{{ $material->name }}</td>
                                <td>{{ $quantity }} {{ $material->unit }}</td>
                                <td>₱{{ number_format($unitPrice, 2) }}</td>
                                <td>₱{{ number_format($unitPrice * $quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @php
                            // Calculate material subtotal - handle both regular and additional quotations
                            $materialSubtotal = $materials->sum(function ($m) use ($isAdditional) {
                                $unitPrice =
                                    isset($isAdditional) && $isAdditional
                                        ? $m->pivot->unit_cost ?? 0
                                        : $m->unit_price ?? 0;
                                $quantity = $m->pivot->quantity ?? 0;
                                return $unitPrice * $quantity;
                            });
                            $laborFee = $quotation->labor_fee ?? 0;
                            $deliveryFee = $quotation->delivery_fee ?? 0;
                            $grandTotal = $materialSubtotal + $laborFee + $deliveryFee;
                        @endphp
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Labor Fee:</td>
                            <td>₱{{ number_format($laborFee, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Delivery Fee:</td>
                            <td>₱{{ number_format($deliveryFee, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                            <td class="fw-bold text-primary fs-5">
                                ₱{{ number_format($grandTotal, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Comments Section -->
        @if ($quotation->customer_approved)
            <!-- Progress Tracking & Report History -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Progress Tracking</h5>
                </div>
                <div class="card-body">
                    <span id="pending-progress-display">
                        Current Progress: {{ $quotation->latest_progress ?? 0 }}%
                    </span>

                    <div class="progress mb-3" style="height: 2rem;">
                        <div id="progress-bar" class="progress-bar" role="progressbar"
                            style="width: {{ $quotation->latest_progress ?? 0 }}%"
                            aria-valuenow="{{ $quotation->latest_progress ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                            {{ $quotation->latest_progress ?? 0 }}%
                        </div>
                    </div>

                    <h5 class="mb-3 border-bottom pb-2">Progress Report History</h5>

                    @php
                        // Ensure reports exist and are sorted latest-first
                        $reports = ($reports ?? collect())->sortByDesc('created_at');
                    @endphp

                    <div class="list-group" id="report-list">
                        @forelse ($reports as $report)
                            <div
                                class="list-group-item list-group-item-action flex-column align-items-start mb-2 border-primary border-3 border-start shadow-sm">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <h6 class="mb-1">
                                        Progress Set To:
                                        <span
                                            class="badge {{ $report->progress == 100 ? 'bg-success' : 'bg-primary' }} fs-6">
                                            {{ $report->progress }}%
                                        </span>
                                    </h6>
                                    <small class="text-muted text-end">
                                        Updated: {{ $report->created_at->setTimezone(config('app.timezone'))->format('M d, Y') }}<br>
                                        at {{ $report->created_at->setTimezone(config('app.timezone'))->format('h:i A') }}
                                    </small>
                                </div>

                                <hr class="my-2">

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
        @endif

        <!-- Threaded Comments Section (Always Visible) -->
        @include('components.threaded-comments', [
            'comments' => $quotation->comments,
            'quotationId' => $quotation->id,
            'publicToken' => $quotation->public_token,
            'commentEndpoint' => route('quotation.comment.submit', $quotation->public_token),
            'commentsEndpoint' => route('quotation.public.comments', $quotation->public_token),
        ])

        <div class="modal fade" id="revisionHistoryModal" tabindex="-1" aria-labelledby="revisionHistoryLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="revisionHistoryLabel">Revision History</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="list-group" id="revisionList">
                            <!-- Past revisions will be loaded here dynamically -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ JS Section --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const approveBtn = document.getElementById('approve-btn');

            // Handle Approval
            if (approveBtn) {
                approveBtn.addEventListener('click', () => {
                    approveBtn.disabled = true;
                    approveBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-1"></span> Approving...';

                    const token = "{{ $quotation->public_token }}";
                    const isAdditional = {{ isset($isAdditional) && $isAdditional ? 'true' : 'false' }};
                    const approveUrl = isAdditional ?
                        `/additional-quotation/public/${token}/approve` :
                        "{{ route('quotation.customer.approve', $quotation->public_token) }}";

                    fetch(approveUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: data.message || 'Approved!',
                                    showConfirmButton: false,
                                    timer: 1200
                                });
                                approveBtn.style.display = 'none';
                                const badge = document.createElement('div');
                                badge.className = 'badge bg-success p-2 mt-2';
                                badge.innerHTML =
                                    '<i class="fa-solid fa-check-circle me-1"></i> You approved this quotation';
                                approveBtn.parentNode.appendChild(badge);
                            } else {
                                Swal.fire('Error', data.error || 'Something went wrong', 'error');
                                approveBtn.disabled = false;
                                approveBtn.innerHTML =
                                    '<i class="fa-solid fa-check-circle me-1"></i> Approve Quotation';
                            }
                        })
                        .catch(() => {
                            Swal.fire('Error', 'Could not approve quotation', 'error');
                            approveBtn.disabled = false;
                            approveBtn.innerHTML =
                                '<i class="fa-solid fa-check-circle me-1"></i> Approve Quotation';
                        });
                });
            }

            // Revision History Button
            const viewRevisionsBtn = document.getElementById('viewRevisionsBtn');
            if (viewRevisionsBtn) {
                viewRevisionsBtn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const token = "{{ $token ?? '' }}";
                    const isAdditional = {{ isset($isAdditional) && $isAdditional ? 'true' : 'false' }};

                    // Use token-based endpoint for additional quotations, ID-based for regular
                    const endpoint = isAdditional && token ?
                        `/additional-quotation/public/${token}/revisions-json` :
                        `/quotations/${id}/revisions-json`;

                    fetch(endpoint)
                        .then(res => res.json())
                        .then(data => {
                            const container = document.getElementById('revisionList');
                            container.innerHTML = '';
                            if (data.length === 0) {
                                container.innerHTML = '<p>No past revisions found.</p>';
                                return;
                            }
                            data.forEach((rev, index) => {
                                const div = document.createElement('div');
                                div.className = 'card mb-3';
                                div.innerHTML = `
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <strong>Revision v${index + 1}</strong> - ${new Date(rev.created_at).toLocaleDateString()}
                                            ${rev.reason ? `<small class='text-muted'>(${rev.reason})</small>` : ''}
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Subject:</strong> ${rev.data.subject}</p>
                                            <p><strong>Description:</strong> ${rev.data.description}</p>
                                            <p><strong>Labor Fee:</strong> ₱${parseFloat(rev.data.labor_fee).toFixed(2)}</p>
                                            <p><strong>Delivery Fee:</strong> ₱${parseFloat(rev.data.delivery_fee).toFixed(2)}</p>
                                            <h6>Materials:</h6>
                                            <table class="table table-sm table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Material</th>
                                                        <th>Unit</th>
                                                        <th>Price/Unit</th>
                                                        <th>Quantity</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${rev.data.materials.map(mat => `
                                                                    <tr>
                                                                        <td>${mat.name}</td>
                                                                        <td>${mat.unit}</td>
                                                                        <td>₱${parseFloat(mat.unit_price).toFixed(2)}</td>
                                                                        <td>${mat.quantity}</td>
                                                                        <td>₱${(parseFloat(mat.unit_price) * mat.quantity).toFixed(2)}</td>
                                                                    </tr>
                                                                `).join('')}
                                                </tbody>
                                            </table>
                                        </div>
                                    `;
                                container.appendChild(div);
                            });
                            new bootstrap.Modal(document.getElementById('revisionHistoryModal')).show();
                        })
                        .catch(err => console.error(err));
                });
            }
        });
    </script>

    <!-- View Additional Quotations Modal -->
    <div class="modal fade" id="additionalQuotationsModal" tabindex="-1" aria-labelledby="additionalQuotationsLabel"
        aria-hidden="true">
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
        document.addEventListener('DOMContentLoaded', function() {
            const viewBtn = document.getElementById('viewAdditionalQtnBtn');
            const modalEl = document.getElementById('additionalQuotationsModal');
            const bodyEl = document.getElementById('additionalQuotationsList');

            if (!viewBtn || !modalEl || !bodyEl) return;

            const bsModal = new bootstrap.Modal(modalEl);

            viewBtn.addEventListener('click', async function() {
                const parentId = this.getAttribute('data-parent-id') || '{{ $quotation->id }}';
                const publicToken = '{{ $quotation->public_token }}';

                try {
                    const res = await fetch(`/quotations/${parentId}/additional-quotations-json`, {
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json' }
                    });

                    if (!res.ok) throw new Error('Network response was not ok');

                    const data = await res.json();
                    console.log('Fetched data:', data); // Debug
                    const quotations = data.quotations || data || [];

                    bodyEl.innerHTML = '';

                    if (quotations.length === 0) {
                        bodyEl.innerHTML = '<p class="text-muted">No additional quotations found.</p>';
                        bsModal.show();
                        return;
                    }

                    // Loop through each quotation and create a card (like revisions modal does)
                    quotations.forEach((quot, index) => {
                        const card = document.createElement('div');
                        card.className = 'card mb-4 shadow-sm';
                        
                        const mats = Array.isArray(quot.materials) ? quot.materials : [];
                        let materialRows = '';
                        let totalMaterial = 0;

                        mats.forEach(m => {
                            const unitPrice = parseFloat(m.unit_price || m.price || 0);
                            const qty = parseFloat(m.quantity || m.qty || 0);
                            const lineTotal = unitPrice * qty;
                            totalMaterial += lineTotal;

                            materialRows += `
                                <tr>
                                    <td>${m.name || m.material_name || '-'}</td>
                                    <td>${m.unit || '-'}</td>
                                    <td>₱${unitPrice.toFixed(2)}</td>
                                    <td>${qty}</td>
                                    <td>₱${lineTotal.toFixed(2)}</td>
                                </tr>
                            `;
                        });

                        const laborfee = parseFloat(quot.labor_fee || 0);
                        const deliveryFee = parseFloat(quot.delivery_fee || 0);
                        const grandTotal = totalMaterial + laborfee + deliveryFee;

                        const createdDate = new Date(quot.created_at).toLocaleDateString();

                        // Determine status badge based on customer_approved flag (check boolean value)
                        const isApproved = Boolean(quot.customer_approved);
                        const statusBadge = isApproved 
                            ? '<span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Approved</span>'
                            : '<span class="badge bg-warning text-dark"><i class="fa-solid fa-hourglass me-1"></i>Pending Approval</span>';

                        card.innerHTML = `
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Additional Quotation</strong> - ${createdDate}
                                </div>
                                <div>
                                    ${statusIndicator}
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <p><strong>Subject:</strong> ${quot.subject || '-'}</p>
                                <p><strong>Description:</strong> ${quot.description || quot.subject || 'QUOTATION'}</p>
                                <p><strong>Labor Fee:</strong> ₱${laborfee.toFixed(2)}</p>
                                <p><strong>Delivery Fee:</strong> ₱${deliveryFee.toFixed(2)}</p>

                                <h6 class="mt-4 mb-3">Materials:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Material</th>
                                                <th>Unit</th>
                                                <th>Price/Unit</th>
                                                <th>Quantity</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${materialRows || '<tr><td colspan="5" class="text-center text-muted">No materials</td></tr>'}
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4" class="text-end fw-bold">Total Material:</td>
                                                <td>₱${totalMaterial.toFixed(2)}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-end fw-bold">Labor Fee:</td>
                                                <td>₱${laborfee.toFixed(2)}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-end fw-bold">Delivery Fee:</td>
                                                <td>₱${deliveryFee.toFixed(2)}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-end fw-bold">Grand Total:</td>
                                                <td class="fw-bold text-primary">₱${grandTotal.toFixed(2)}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                ${!isApproved ? `
                                    <div class="mt-3">
                                        <button class="btn btn-success approve-additional-btn" data-id="${quot.id}" data-token="${publicToken}">
                                            <i class="fa-solid fa-check-circle me-1"></i> Approve This Quotation
                                        </button>
                                    </div>
                                ` : ''}
                            </div>
                        `;

                        bodyEl.appendChild(card);
                    });

                    // Add approval handlers
                    document.querySelectorAll('.approve-additional-btn').forEach(btn => {
                        btn.addEventListener('click', async function() {
                            const quotationId = this.getAttribute('data-id');
                            const token = this.getAttribute('data-token');
                            
                            const result = await Swal.fire({
                                title: 'Approve Additional Quotation?',
                                text: 'Are you sure you want to approve this additional quotation?',
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonColor: '#198754',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: 'Yes, approve it!'
                            });

                            if (!result.isConfirmed) return;

                            this.disabled = true;
                            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Approving...';

                            try {
                                const response = await fetch(`/additional-quotation/public/${token}/approve`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        additional_quotation_id: quotationId
                                    })
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
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire('Error', data.message || 'Failed to approve quotation', 'error');
                                    this.disabled = false;
                                    this.innerHTML = '<i class="fa-solid fa-check-circle me-1"></i> Approve This Quotation';
                                }
                            } catch (error) {
                                console.error('Approval error:', error);
                                Swal.fire('Error', 'Something went wrong!', 'error');
                                this.disabled = false;
                                this.innerHTML = '<i class="fa-solid fa-check-circle me-1"></i> Approve This Quotation';
                            }
                        });
                    });

                    bsModal.show();
                } catch (err) {
                    console.error('Error fetching additional quotations:', err);
                    Swal.fire('Error', `Failed to load additional quotations: ${err.message}`, 'error');
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection
