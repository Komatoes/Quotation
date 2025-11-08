@extends('layouts.public')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-2">{{ $quotation->subject }}</h4>
                        <p class="mb-1">{{ $quotation->description }}</p>
                    </div>
                    @if (!$quotation->customer_approved)
                        <button id="approve-btn" class="btn btn-success">
                            <i class="ti ti-check me-1"></i> Approve Quotation
                        </button>
                    @else
                        <div class="badge bg-success p-2 mb-2">
                            <i class="ti ti-check-circle me-1"></i> You approved this quotation
                        </div>
                    @endif
                </div>

                <div class="row">
                    <div class="col-xl-4 col-md-6 col-sm-12">
                        <div class="mb-3">
                            <label class="form-label">Contact Information</label>
                            <p class="mb-1"><strong>Name:</strong> {{ $quotation->client->name }}</p>
                            <p class="mb-1"><strong>Phone:</strong> {{ $quotation->client->contact_no }}</p>
                            <p class="mb-1"><strong>Address:</strong> {{ $quotation->client->address }}</p>
                        </div>
                    </div>
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



        <!-- Comments Section (Always Visible) -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Comments & Feedback</h5>
            </div>
            <div class="card-body">
                <div id="comments-list" class="mb-4">
                    @foreach ($quotation->comments as $comment)
                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0">
                                <div
                                    class="avatar {{ $comment->sender_type === 'customer' ? 'avatar-primary' : 'avatar-success' }}">
                                    <span class="avatar-initial rounded-circle">
                                        {{ $comment->sender_type === 'customer' ? 'C' : 'A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="mb-1">
                                    <span
                                        class="fw-semibold">{{ $comment->sender_type === 'customer' ? 'You' : 'Admin' }}</span>
                                    <small class="text-muted"> • {{ $comment->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-1">{{ $comment->comment }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <!-- Add Comment Form -->
                <div class="mt-3">
                    <textarea id="client-comment-input" class="form-control mb-3" rows="3" placeholder="Write a comment..."></textarea>
                    <button id="client-submit-comment" class="btn btn-primary">
                        <i class="ti ti-send me-1"></i> Send Comment
                    </button>
                </div>
            </div>
        </div>
            <!-- Revision History Button and Modal -->
            <div class="mt-3 mb-4 text-end">
                <button type="button" class="btn btn-outline-secondary" id="viewRevisionsBtn" data-id="{{ $quotation->id }}">
                    <i class="bi bi-clock-history me-1"></i> View Revisions
                </button>
            </div>

            <div class="modal fade" id="revisionHistoryModal" tabindex="-1" aria-labelledby="revisionHistoryLabel" aria-hidden="true">
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
            const commentBtn = document.getElementById('client-submit-comment');

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
                                    '<i class="ti ti-check-circle me-1"></i> You approved this quotation';
                                approveBtn.parentNode.appendChild(badge);
                            } else {
                                Swal.fire('Error', data.error || 'Something went wrong', 'error');
                                approveBtn.disabled = false;
                                approveBtn.innerHTML =
                                    '<i class="ti ti-check me-1"></i> Approve Quotation';
                            }
                        })
                        .catch(() => {
                            Swal.fire('Error', 'Could not approve quotation', 'error');
                            approveBtn.disabled = false;
                            approveBtn.innerHTML = '<i class="ti ti-check me-1"></i> Approve Quotation';
                        });
                });
            }

            // 🟢 Handle Comment
            if (commentBtn) {
                commentBtn.addEventListener('click', e => {
                    e.preventDefault();
                    const message = document.getElementById('client-comment-input').value.trim();
                    if (message === "") {
                        Swal.fire('Error', 'Comment cannot be empty', 'error');
                        return;
                    }

                    fetch("{{ route('quotation.comment.submit', $quotation->public_token) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                comment: message,
                                sender_type: 'customer',
                                client_name: "{{ $quotation->client->name }}"
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('comments-list').insertAdjacentHTML('beforeend', `
                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-primary">
                                    <span class="avatar-initial rounded-circle">C</span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="mb-1">
                                    <span class="fw-semibold">You</span>
                                    <small class="text-muted"> • Just now</small>
                                </div>
                                <p class="mb-1">${message}</p>
                            </div>
                        </div>
                    `);
                                document.getElementById('client-comment-input').value = "";
                            } else {
                                Swal.fire('Error', data.message || 'Could not submit comment', 'error');
                            }
                        })
                        .catch(() => {
                            Swal.fire('Error', 'Something went wrong!', 'error');
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
