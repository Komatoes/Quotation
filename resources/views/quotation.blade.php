@extends('layouts.app')
@include('include.head')

@section('content')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="{{ asset('assets/css/quotation-styles.css') }}" rel="stylesheet">
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-fluid flex-grow-1 container-p-y">

            <!-- Header -->
            <div class="card mb-4">
                <div class="card-body text-center bg-light rounded shadow-sm">
                    <h1 class="h3 mb-0 text-dark">Creating Quotation...</h1>
                </div>
            </div>

            <!-- Quotation Info -->
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="mb-3">{{ $quotation->subject }}</h3>
                    <p><strong>Customer:</strong> {{ $client->first_name }} {{ $client->last_name }}</p>
                    <p><strong>Contact:</strong> {{ $client->contact_no }}</p>
                    <p><strong>Address:</strong> {{ $client->address }}</p>

                    @if ($quotation->customer_approved)
                        <span class="badge bg-success mb-3 d-inline-flex align-items-center">
                            <i class="ti ti-check-circle me-1"></i> Approved by Client
                        </span>
                    @else
                        <span class="badge bg-warning text-dark mb-3 d-inline-flex align-items-center">
                            <i class="ti ti-clock me-1"></i> Awaiting Client Approval
                        </span>
                    @endif

                    <!-- Buttons Row: Left and Right -->
                    @if (empty($readonly))
                        <div class="d-flex justify-content-between flex-wrap gap-2 align-items-center">
                            <!-- Left buttons -->
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#addMatModal">
                                    Add Material
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="generateLinkBtn"
                                    title="Generate & Copy Public Link">
                                    <i class="bi bi-link-45deg me-1"></i> Generate Link
                                </button>
                            </div>

                            <!-- Right buttons -->
                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-outline-warning" id="createRevisionBtn"
                                    data-id="{{ $quotation->id }}" style="min-width: 160px;">
                                    <i class="bi bi-pencil-square me-1"></i> Create Revision
                                </button>
                                <button class="btn btn-outline-secondary" id="viewRevisionsBtn"
                                    data-id="{{ $quotation->id }}" style="min-width: 160px;">
                                    <i class="bi bi-clock-history me-1"></i> View Revisions
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>


            <!-- Materials Table -->
            <div class="card">
                <div class="table-responsive">
                    <table id="quotationMaterials" class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Material</th>
                                <th>Estimated Quantity</th>
                                <th>Price/Unit</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($materials as $mat)
                                <tr>
                                    <td>{{ $mat->name }}</td>
                                    <td>
                                        @if (empty($readonly))
                                            <input type="number" class="form-control update-quantity"
                                                data-pivot="{{ $mat->pivot->id }}" data-quot="{{ $quotation->id }}"
                                                value="{{ $mat->pivot->quantity }}" min="1"
                                                style="width: 80px; display:inline-block;">
                                        @else
                                            <span>{{ $mat->pivot->quantity }}</span>
                                        @endif
                                        <span>{{ $mat->unit }}</span>
                                    </td>
                                    <td>₱{{ number_format($mat->unit_price, 2) }}</td>
                                    <td class="line-total">
                                        ₱{{ number_format($mat->unit_price * $mat->pivot->quantity, 2) }}</td>
                                    <td class="text-center">
                                        @if (empty($readonly))
                                            <a href="#" class="text-danger delete-material"
                                                data-id="{{ $mat->pivot->id }}" data-quot="{{ $quotation->id }}">
                                                <i class="ti ti-trash"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Labor Fee:</td>
                                <td colspan="2">
                                    @if (empty($readonly))
                                        <input type="number" class="form-control text-end fee-input" id="laborFee"
                                            value="{{ $quotation->labor_fee }}" step="0.01" data-field="labor_fee"
                                            onfocus="if(this.value == '0.00') this.value = ''">
                                    @else
                                        <span>{{ number_format($quotation->labor_fee, 2) }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Delivery/Hauling Fee:</td>
                                <td colspan="2">
                                    @if (empty($readonly))
                                        <input type="number" class="form-control text-end fee-input" id="deliveryFee"
                                            value="{{ $quotation->delivery_fee }}" step="0.01" data-field="delivery_fee"
                                            onfocus="if(this.value == '0.00') this.value = ''">
                                    @else
                                        <span>{{ number_format($quotation->delivery_fee, 2) }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                                <td colspan="2" class="fw-bold text-danger" id="grandTotal">
                                    ₱{{ number_format($materials->sum(fn($m) => $m->unit_price * $m->pivot->quantity) + $quotation->labor_fee + $quotation->delivery_fee, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if (empty($readonly))
                <!-- Primary Actions: Approve, Save Draft, Reject, Export -->
                <div class="row mt-3">
                    <div class="col-12 d-flex flex-wrap gap-2 justify-content-end">
                        <button type="button" class="btn btn-success" id="approveBtn" data-quot="{{ $quotation->id }}"
                            @if (!$quotation->customer_approved) disabled @endif>
                            <i class="bi bi-check-circle me-1"></i> Approve
                        </button>
                        <button type="button" class="btn btn-primary" id="saveDraftBtn" data-quot="{{ $quotation->id }}">
                            <i class="bi bi-save me-1"></i> Save Draft
                        </button>
                        <button type="button" class="btn btn-danger" id="rejectBtn" data-quot="{{ $quotation->id }}">
                            <i class="bi bi-x-circle me-1"></i> Reject
                        </button>
                        <a href="{{ route('quotations.export', ['id' => $quotation->id]) }}"
                            class="btn btn-info d-flex align-items-center">
                            <i class="ti ti-file-export me-1"></i> Export to DOC
                        </a>
                    </div>
                </div>
            @endif




            {{-- 💬 Comments Section --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Comments & Feedback</h5>
                </div>
                <div class="card-body">

                    {{-- Existing Comments --}}
                    <div id="comments-list" class="mb-4">
                        @forelse($quotation->comments as $comment)
                            <div class="d-flex mb-4">
                                <div class="flex-shrink-0">
                                    <div
                                        class="avatar @if ($comment->sender_type === 'customer') avatar-primary @else avatar-success @endif">
                                        <span class="avatar-initial rounded-circle">
                                            {{ $comment->sender_type === 'customer' ? 'C' : 'A' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="mb-1">
                                        <span class="fw-semibold">
                                            {{ $comment->sender_type === 'customer' ? 'Customer' : 'Admin' }}
                                        </span>
                                        <small class="text-muted"> • {{ $comment->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1">{{ $comment->comment }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">No comments yet.</p>
                        @endforelse
                    </div>
                    <!-- Add Comment Form (Admin Side) -->
                    <div class="mt-3">
                        <textarea id="admin-comment-input" class="form-control mb-3" rows="3" placeholder="Write a comment..."></textarea>
                        <button id="admin-submit-comment" class="btn btn-primary">
                            <i class="ti ti-send me-1"></i> Send Comment
                        </button>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const submitBtn = document.getElementById('admin-submit-comment');
                            const commentInput = document.getElementById('admin-comment-input');
                            if (submitBtn && commentInput) {
                                submitBtn.addEventListener('click', async function() {
                                    const comment = commentInput.value.trim();
                                    if (!comment) {
                                        Swal.fire('Empty Comment', 'Please write a comment before submitting.',
                                            'warning');
                                        return;
                                    }
                                    submitBtn.disabled = true;
                                    submitBtn.innerHTML =
                                        '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
                                    try {
                                        const quotationId = '{{ $quotation->id }}';
                                        const res = await fetch(`/quotation/${quotationId}/comments`, {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Accept': 'application/json'
                                            },
                                            body: JSON.stringify({
                                                comment: comment,
                                                sender_type: 'admin'
                                            })
                                        });
                                        const data = await res.json();
                                        if (res.ok && data.success) {
                                            commentInput.value = '';
                                            Swal.fire('Comment Sent!', '', 'success');
                                            loadComments();
                                        } else {
                                            Swal.fire('Error', data.message || 'Failed to send comment.', 'error');
                                        }
                                    } catch (error) {
                                        console.error('Comment submit error:', error);
                                        Swal.fire('Error', 'Something went wrong!', 'error');
                                    }
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = '<i class="ti ti-send me-1"></i> Send Comment';
                                });
                            }
                        });
                    </script>

                    <!-- Move Generate & Copy Public Link script OUTSIDE Blade control structure -->
                    <script>
                        // Generate & Copy Public Link functionality (uses public_token)
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
                                    generateLinkBtn.innerHTML =
                                        '<i class="bi bi-link-45deg"></i> Generate & Copy Public Link';
                                });
                            }
                        });
                    </script>





                    <!-- Include Modals -->
                    @include('include.modals.add_material')
                    @include('include.modals.new_material')

                    <!-- Revision History Modal -->
                    <div class="modal fade" id="revisionHistoryModal" tabindex="-1"
                        aria-labelledby="revisionHistoryLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="revisionHistoryLabel">Revision History</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
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
            </div>

            <!-- ---------------- Scripts ---------------- -->

            <script>
                function appendMaterialsToTable(materials) {
                    const tbody = document.querySelector("table tbody");
                    materials.forEach(mat => {
                        const row = `<tr>
            <td>${mat.name}</td>
            <td>
                <input type="number" class="form-control update-quantity" 
                    data-pivot="${mat.pivot_id}" data-quot="{{ $quotation->id }}" 
                    value="${mat.quantity}" min="1" style="width: 80px; display:inline-block;">
                <span>${mat.unit}</span>
            </td>
            <td>₱${parseFloat(mat.unit_price).toFixed(2)}</td>
            <td class="line-total">₱${parseFloat(mat.line_total).toFixed(2)}</td>
            <td class="text-center">
                <a href="#" class="text-danger delete-material" data-id="${mat.pivot_id}" data-quot="{{ $quotation->id }}">
                    <i class="fa-solid fa-trash"></i>
                </a>
            </td>
        </tr>`;
                        tbody.insertAdjacentHTML("beforeend", row);
                    });

                    // Rebind events for new quantity inputs and delete links
                    new QuantityUpdater(".update-quantity");
                    new DeleteMaterialFromQuotation(".delete-material");
                }
            </script>


            <!-- Delete Material from Quotation -->
            <script>
                class DeleteMaterialFromQuotation {
                    constructor(selector) {
                        this.selector = selector;
                        this.bindEvents();
                    }
                    bindEvents() {
                        document.addEventListener("click", (e) => {
                            const btn = e.target.closest(this.selector);
                            if (!btn) return;
                            e.preventDefault();
                            this.deleteMaterial(btn.dataset.quot, btn.dataset.id, btn.closest("tr"));
                        });
                    }
                    async deleteMaterial(quotationId, pivotId, rowEl) {
                        const confirm = await Swal.fire({
                            title: "Are you sure?",
                            text: "This material will be removed from the quotation",
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#d33",
                            cancelButtonColor: "#6c757d",
                            confirmButtonText: "Yes, delete it"
                        });
                        if (!confirm.isConfirmed) return;
                        try {
                            const res = await fetch(`/quotation-materials/${pivotId}`, {
                                method: "DELETE",
                                headers: {
                                    "X-CSRF-TOKEN": '{{ csrf_token() }}',
                                    "Accept": "application/json"
                                }
                            });
                            const data = await res.json();
                            if (res.ok && data.success) {
                                Swal.fire("Deleted!", data.message, "success");
                                if (rowEl) rowEl.remove();
                                if (data.grand_total !== undefined) {
                                    document.getElementById("grandTotal").textContent =
                                        "₱" + parseFloat(data.grand_total).toFixed(2);
                                }
                            } else {
                                Swal.fire("Error", data.message || "Failed to delete", "error");
                            }
                        } catch (error) {
                            console.error(error);
                            Swal.fire("Something went wrong!", "", "error");
                        }
                    }
                }
                new DeleteMaterialFromQuotation(".delete-material");
            </script>

            <script>
                class QuantityUpdater {
                    constructor(selector) {
                        this.selector = selector;
                        this.debounceTimers = new Map(); // per-input debounce
                        this.init();
                    }

                    init() {
                        document.querySelectorAll(this.selector).forEach(input => {
                            input.addEventListener("input", (e) => this.debounceUpdate(e));
                        });
                    }

                    debounceUpdate(e) {
                        const input = e.target;

                        // Clear previous timer for this input
                        if (this.debounceTimers.has(input)) {
                            clearTimeout(this.debounceTimers.get(input));
                        }

                        // Short debounce to avoid spamming backend
                        this.debounceTimers.set(input, setTimeout(() => {
                            this.update(input);
                        }, 150)); // 150ms delay
                    }

                    async update(input) {
                        const newQty = input.value,
                            pivotId = input.dataset.pivot,
                            quotId = input.dataset.quot;

                        try {
                            const res = await fetch(`/quotation-materials/update-quantity`, {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    pivot_id: pivotId,
                                    quot_id: quotId,
                                    quantity: newQty
                                })
                            });

                            const data = await res.json();

                            if (data.success) {
                                // Update line total for this row
                                input.closest("tr").querySelector(".line-total").textContent =
                                    `₱${parseFloat(data.line_total).toFixed(2)}`;

                                // Update grand total
                                if (data.grand_total !== undefined) {
                                    document.getElementById("grandTotal").textContent =
                                        `₱${parseFloat(data.grand_total).toFixed(2)}`;
                                }
                            } else {
                                Swal.fire("Update failed", data.message || "", "error");
                            }
                        } catch (error) {
                            console.error("Quantity update error:", error);
                            Swal.fire("Something went wrong!", "", "error");
                        }
                    }
                }

                // Initialize
                new QuantityUpdater(".update-quantity");
            </script>



            <!-- Update Fees -->
            <script>
                class FeeUpdater {
                    constructor(selector, quotationId, csrfToken) {
                        this.selector = selector;
                        this.quotationId = quotationId;
                        this.csrfToken = csrfToken;
                        this.debounceTimer = null;

                        document.querySelectorAll(this.selector).forEach(input => {
                            // Add input event listener
                            input.addEventListener("input", (e) => this.updateFee(e));

                            // Add focus event listener
                            input.addEventListener("focus", (e) => {
                                if (e.target.value === "0.00") {
                                    e.target.value = "";
                                }
                            });

                            // Add blur event listener
                            input.addEventListener("blur", (e) => {
                                if (e.target.value === "" || e.target.value === "0") {
                                    e.target.value = "0.00";
                                }
                            });
                        });
                    }

                    updateFee(e) {
                        if (!e.isTrusted) return; // ignore programmatic changes

                        const input = e.target;
                        const field = input.dataset.field;
                        const value = input.value;

                        console.log('Fee Update Initiated:', {
                            field,
                            value,
                            quotationId: this.quotationId
                        });

                        // ✅ Debounce to avoid spamming backend
                        clearTimeout(this.debounceTimer);
                        this.debounceTimer = setTimeout(async () => {
                            try {
                                console.log('Sending Fee Update Request:', {
                                    url: `/quotations/${this.quotationId}/update-fee`,
                                    field,
                                    value
                                });

                                const res = await fetch(`/quotations/${this.quotationId}/update-fee`, {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json",
                                        "X-CSRF-TOKEN": this.csrfToken,
                                        "Accept": "application/json"
                                    },
                                    body: JSON.stringify({
                                        field,
                                        value
                                    })
                                });

                                console.log('Raw Response:', res);
                                const data = await res.json();
                                console.log("Fee Update Response:", {
                                    status: res.status,
                                    ok: res.ok,
                                    data
                                });

                                if (res.ok && data.success) {
                                    console.log('Fee Update Success:', {
                                        newGrandTotal: data.grand_total,
                                        field,
                                        value
                                    });

                                    // ✅ Update grand total UI
                                    if (document.getElementById("grandTotal")) {
                                        document.getElementById("grandTotal").textContent =
                                            "₱" + parseFloat(data.grand_total).toFixed(2);
                                    }

                                    // Store the updated fee value in the input
                                    input.value = parseFloat(value).toFixed(2);

                                    Swal.fire({
                                        title: data.message,
                                        icon: "success",
                                        timer: 800,
                                        showConfirmButton: false
                                    });
                                } else {
                                    console.error('Fee Update Failed:', data);
                                    Swal.fire("Error", data.message || "Update failed", "error");
                                }
                            } catch (error) {
                                console.error("Fee Update Error:", error);
                                Swal.fire("Error", "Something went wrong!", "error");
                            }
                        }, 500);
                    }
                }

                // ✅ Initialize with quotationId and CSRF
                if (!window.feeUpdater) {
                    window.feeUpdater = new FeeUpdater(
                        ".fee-input",
                        "{{ $quotation->id ?? '' }}",
                        "{{ csrf_token() }}"
                    );
                }
            </script>




            <!-- Quotation Status Buttons -->
            <script>
                class QuotationStatusHandler {
                    constructor() {
                        this.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
                        this.bindEvents();
                    }

                    bindEvents() {
                        document.querySelectorAll("#approveBtn, #saveDraftBtn, #rejectBtn").forEach(button => {
                            button.addEventListener("click", (e) => {
                                e.preventDefault();
                                const quotationId = button.dataset.quot;
                                let statusId = null;

                                switch (button.id) {
                                    case "saveDraftBtn":
                                        statusId = 1;
                                        break;
                                    case "approveBtn":
                                        statusId = 2;
                                        break;
                                    case "rejectBtn":
                                        statusId = 3;
                                        break;
                                }

                                if (statusId) this.updateStatus(quotationId, statusId);
                            });
                        });
                    }

                    async updateStatus(quotationId, statusId) {
                        try {
                            const res = await fetch(`/quotations/${quotationId}/status`, {
                                method: "PUT",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": this.csrfToken,
                                    "Accept": "application/json"
                                },
                                body: JSON.stringify({
                                    status_id: statusId
                                })
                            });

                            const data = await res.json();

                            if (res.ok && data.success) {
                                Swal.fire({
                                    title: "Success",
                                    text: data.message,
                                    icon: "success",
                                    timer: 1200,
                                    showConfirmButton: false
                                }).then(() => {
                                    // 🔄 Redirect after success
                                    window.location.href = "{{ route('dashboard') }}";
                                    // OR: window.location.href = "/quotations";
                                });

                                // ✅ Instantly update the status on the page (if you stay on page)
                                const statusLabel = document.getElementById("quotationStatus");
                                if (statusLabel && data.quotation?.status?.name) {
                                    statusLabel.textContent = data.quotation.status.name;
                                }

                            } else {
                                Swal.fire("Error", data.message || "Failed to update status.", "error");
                            }
                        } catch (error) {
                            console.error("Status update error:", error);
                            Swal.fire("Error", "Something went wrong!", "error");
                        }
                    }
                }

                new QuotationStatusHandler();
            </script>

            <script>
                window.quotationMaterialHandler = {
                    loadMaterials: async function() {
                        const quotationId = "{{ $quotation->id }}";

                        try {
                            const res = await fetch(`/quotation/${quotationId}/materials`);
                            const data = await res.json();
                            // Store scroll position
                            const scrollPosition = window.scrollY;

                            if (data.success) {
                                const tableBody = document.querySelector("#quotationMaterials tbody");
                                tableBody.innerHTML = "";

                                data.materials.forEach(mat => {
                                    const row = `
                            <tr>
                                <td>${mat.name}</td>
                                <td>
                                    <input type="number" class="form-control update-quantity" 
                                        data-pivot="${mat.pivot_id}" data-quot="${quotationId}"
                                        value="${mat.quantity}" min="1"
                                        style="width: 80px; display:inline-block;">
                                    <span>${mat.unit}</span>
                                </td>
                                <td>₱${parseFloat(mat.unit_price).toFixed(2)}</td>
                                <td class="line-total">₱${parseFloat(mat.line_total).toFixed(2)}</td>
                                <td class="text-center">
                                    <a href="#" class="text-danger delete-material" 
                                        data-id="${mat.pivot_id}" data-quot="${quotationId}">
                                        <i class="fa-solid fa-trash"></i>

                                    </a>
                                </td>
                            </tr>
                        `;
                                    tableBody.insertAdjacentHTML("beforeend", row);
                                });

                                // ✅ Update total
                                document.getElementById("grandTotal").textContent =
                                    "₱" + parseFloat(data.grand_total).toFixed(2);

                                // ✅ Rebind handlers
                                new QuantityUpdater(".update-quantity");
                                new DeleteMaterialFromQuotation(".delete-material");

                                // Restore scroll position after content update
                                window.scrollTo(0, scrollPosition);

                                // Ensure the page is scrollable to the new content
                                document.body.style.height = 'auto';
                                document.body.style.overflow = 'visible';
                            }
                        } catch (err) {
                            console.error("Failed to reload materials:", err);
                        }
                    }
                };
            </script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                document.getElementById('createRevisionBtn').addEventListener('click', function() {
                    const id = this.dataset.id;

                    Swal.fire({
                        title: 'Create a revision?',
                        text: "Do you want to create a revision for this quotation?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, create it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        fetch(`/quotations/${id}/create-revision`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    reason: 'Client requested changes' // optional reason
                                }),
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: data.message,
                                        icon: 'success'
                                    }).then(() => {
                                        // redirect to quotation edit page
                                        window.location.href = `/quotations/${data.quotation_id}`;
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Failed',
                                        text: data.message,
                                        icon: 'error'
                                    });
                                }
                            })
                            .catch(err => {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Error creating revision.',
                                    icon: 'error'
                                });
                                console.error(err);
                            });
                    });
                });
            </script>

            <script>
                document.getElementById('viewRevisionsBtn').addEventListener('click', function() {
                    const id = this.dataset.id;

                    fetch(`/quotations/${id}/revisions-json`) // new route returning JSON
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
                        ${rev.reason ? `<small class="text-muted">(${rev.reason})</small>` : ''}
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
            </script>

            <!-- Comments refresh functionality -->
            <script>
                function loadComments() {
                    const quotationId = '{{ $quotation->id }}';
                    const commentsDiv = document.getElementById('comments-list');

                    fetch(`/quotation/${quotationId}/comments`)
                        .then(response => response.json())
                        .then(comments => {
                            if (comments.length === 0) {
                                commentsDiv.innerHTML = '<p class="text-muted">No comments yet.</p>';
                                return;
                            }

                            commentsDiv.innerHTML = comments.map(comment => `
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <div class="avatar ${comment.sender_type === 'customer' ? 'avatar-primary' : 'avatar-success'}">
                                <span class="avatar-initial rounded-circle">
                                    ${comment.sender_type === 'customer' ? 'C' : 'A'}
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="mb-1">
                                <span class="fw-semibold">
                                    ${comment.sender_type === 'customer' ? 'Customer' : 'Admin'}
                                </span>
                                <small class="text-muted"> • ${timeAgo(new Date(comment.created_at))}</small>
                            </div>
                            <p class="mb-1">${comment.comment}</p>
                        </div>
                    </div>
                `).join('');
                        })
                        .catch(error => {
                            console.error('Error loading comments:', error);
                        });
                }

                // Helper function to format dates
                function timeAgo(date) {
                    const seconds = Math.floor((new Date() - date) / 1000);

                    let interval = Math.floor(seconds / 31536000);
                    if (interval >= 1) return interval + ' year' + (interval === 1 ? '' : 's') + ' ago';

                    interval = Math.floor(seconds / 2592000);
                    if (interval >= 1) return interval + ' month' + (interval === 1 ? '' : 's') + ' ago';

                    interval = Math.floor(seconds / 86400);
                    if (interval >= 1) return interval + ' day' + (interval === 1 ? '' : 's') + ' ago';

                    interval = Math.floor(seconds / 3600);
                    if (interval >= 1) return interval + ' hour' + (interval === 1 ? '' : 's') + ' ago';

                    interval = Math.floor(seconds / 60);
                    if (interval >= 1) return interval + ' minute' + (interval === 1 ? '' : 's') + ' ago';

                    return 'just now';
                }

                // Start periodic refresh
                loadComments(); // Load immediately
                setInterval(loadComments, 10000); // Then every 10 seconds
            </script>
        @endsection
