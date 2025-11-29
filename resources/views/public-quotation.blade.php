@extends('layouts.public')

@section('content')
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
                <p><strong>Customer:</strong> <span id="clientName">{{ $quotation->client->first_name }}
                        {{ $quotation->client->last_name }}</span></p>
                <p><strong>Contact:</strong> <span id="clientContact">{{ $quotation->client->contact_no }}</span></p>
                <p><strong>Address:</strong> <span id="clientAddress">{{ $quotation->client->address }}</span></p>
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
                    <span class="badge {{ $badgeClass }} mb-3 d-inline-flex align-items-center"
                        id="quotation-status-badge">
                        {{ $badgeText }}
                    </span>
                </div>

                @if (!$quotation->customer_approved)
                    <button id="approve-btn" class="btn btn-success mt-3">
                        <i class="fa-solid fa-check-circle me-1"></i> Approve Quotation
                    </button>
                @endif
                    <a href="{{ $exportRoute }}" class="btn btn-info btn-sm w-auto me-2" target="_blank" rel="noopener">
                        <i class="fa-solid fa-file-word me-1"></i> Export as DOC
                    </a>
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
                            <tr>
                                <td>{{ $material->name }}</td>
                                <td>{{ $material->pivot->quantity }} {{ $material->unit }}</td>
                                <td>₱{{ number_format($material->unit_price, 2) }}</td>
                                <td>₱{{ number_format($material->unit_price * $material->pivot->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Labor Fee:</td>
                            <td>₱{{ number_format($quotation->labor_fee, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Delivery Fee:</td>
                            <td>₱{{ number_format($quotation->delivery_fee, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                            <td class="fw-bold text-primary fs-5">
                                ₱{{ number_format($materials->sum(fn($m) => $m->unit_price * $m->pivot->quantity) + $quotation->labor_fee + $quotation->delivery_fee, 2) }}
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
                                        Updated: {{ $report->created_at->format('M d, Y') }}<br>
                                        at {{ $report->created_at->format('h:i A') }}
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

        <!-- Revision History Button and Modal -->
        <div class="mt-3 mb-4 text-end">
            <button type="button" class="btn btn-outline-secondary" id="viewRevisionsBtn" data-id="{{ $quotation->id }}">
                <i class="bi bi-clock-history me-1"></i> View Revisions
            </button>
        </div>

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

            // 🟢 Handle Approval
            if (approveBtn) {
                approveBtn.addEventListener('click', () => {
                    approveBtn.disabled = true;
                    approveBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-1"></span> Approving...';

                    fetch("{{ route('quotation.customer.approve', $quotation->public_token) }}", {
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
                    fetch(`/quotations/${id}/revisions-json`)
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection
