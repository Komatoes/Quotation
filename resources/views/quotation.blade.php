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
                    <p><strong>Customer:</strong> <span id="clientName">{{ $client->first_name }} {{ $client->last_name }}</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" id="editClientBtn">Edit Client</button>
                    </p>
                    <p><strong>Contact:</strong> <span id="clientContact">{{ $client->contact_no }}</span></p>
                    <p><strong>Address:</strong> <span id="clientAddress">{{ $client->address }}</span></p>

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
                </div>
            </div>


            <!-- Materials Table (Admin only) -->
            @if (Auth::user()->can('view_materials'))
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Materials</h5>
                    <div class="d-flex gap-2">
                        @if (empty($readonly) && !in_array(strtolower($quotation->status->status_name ?? ''), ['completed','rejected']))
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addMatModal">
                                <i class="fa-solid fa-plus me-1"></i> Add Material
                            </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="generateLinkBtn" title="Generate & Copy Public Link">
                            <i class="fa-solid fa-link me-1"></i> Generate Link
                        </button>
                        @if (Auth::user()->can('view_revision_history'))
                            <button type="button" class="btn btn-sm btn-outline-info" id="viewRevisionsBtn" data-id="{{ $quotation->id }}" title="View Revision History">
                                <i class="fa-solid fa-clock-rotate-left me-1"></i> View Revisions
                            </button>
                        @endif
                        @if (Auth::user()->can('create_revision'))
                            <button type="button" class="btn btn-sm btn-outline-warning" id="createRevisionBtn" data-id="{{ $quotation->id }}" title="Create Revision">
                                <i class="fa-solid fa-copy me-1"></i> Create Revision
                            </button>
                        @endif
                    </div>
                </div>
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
                                    <td>
                                        @if (Auth::user()->can('view_prices'))
                                            @if ($qStatus === 'draft' && empty($readonly))
                                                <input type="number" class="form-control update-price"
                                                    data-pivot="{{ $mat->pivot->id }}" data-material="{{ $mat->id }}"
                                                    value="{{ number_format($mat->unit_price, 2, '.', '') }}" min="0" step="0.01"
                                                    style="width: 100px; display:inline-block;">
                                            @else
                                                ₱{{ number_format($mat->pivot->unit_cost, 2) }}
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">Hidden</span>
                                        @endif
                                    </td>
                                    <td class="line-total">
                                        @if (Auth::user()->can('view_prices'))
                                            @if ($qStatus === 'draft' && empty($readonly))
                                                ₱{{ number_format($mat->unit_price * $mat->pivot->quantity, 2) }}
                                            @else
                                                ₱{{ number_format($mat->pivot->unit_cost * $mat->pivot->quantity, 2) }}
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">Hidden</span>
                                        @endif
                                    <td class="text-center">
                                        @if (empty($readonly))
                                            <a href="#" class="text-danger delete-material"
                                                data-id="{{ $mat->pivot->id }}" data-quot="{{ $quotation->id }}">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            @if (Auth::user()->can('manage_fees'))
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
                            @endif
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                                <td colspan="2" class="fw-bold text-danger" id="grandTotal">
                                    @if (Auth::user()->can('view_prices'))
                                        ₱{{ number_format($materials->sum(fn($m) => $m->pivot->unit_cost * $m->pivot->quantity) + $quotation->labor_fee + $quotation->delivery_fee, 2) }}
                                    @else
                                        <span class="badge bg-secondary">Hidden</span>
                                    @endif
            <script>
            // Editable price logic for draft quotations
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.update-price').forEach(function(input) {
                    input.addEventListener('change', async function() {
                        const newPrice = parseFloat(this.value);
                        const pivotId = this.dataset.pivot;
                        const materialId = this.dataset.material;
                        if (isNaN(newPrice) || newPrice < 0) {
                            Swal.fire('Invalid Price', 'Please enter a valid price.', 'warning');
                            return;
                        }
                        this.disabled = true;
                        this.classList.add('is-loading');
                        try {
                            // Update material price
                            const matRes = await fetch(`/materials/${materialId}/update-price`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ price: newPrice })
                            });
                            // Update quotation_materials pivot price
                            const pivotRes = await fetch(`/quotation-materials/${pivotId}/update-unit-cost`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ unit_cost: newPrice })
                            });

                            let matData, pivotData;
                            try {
                                if (matRes.headers.get('content-type')?.includes('application/json')) {
                                    matData = await matRes.json();
                                } else {
                                    throw new Error('Material response not JSON');
                                }
                            } catch (e) {
                                console.error('Material price response error:', e);
                                Swal.fire('Error', 'Material price response not JSON.', 'error');
                                this.disabled = false;
                                this.classList.remove('is-loading');
                                return;
                            }
                            try {
                                if (pivotRes.headers.get('content-type')?.includes('application/json')) {
                                    pivotData = await pivotRes.json();
                                } else {
                                    throw new Error('Pivot response not JSON');
                                }
                            } catch (e) {
                                console.error('Pivot price response error:', e);
                                Swal.fire('Error', 'Pivot price response not JSON.', 'error');
                                this.disabled = false;
                                this.classList.remove('is-loading');
                                return;
                            }

                            if (matRes.ok && matData.success && pivotRes.ok && pivotData.success) {
                                // Update the line total for this row
                                const row = this.closest('tr');
                                const quantity = parseFloat(row.querySelector('.update-quantity').value);
                                const lineTotal = newPrice * quantity;
                                row.querySelector('.line-total').textContent = `₱${lineTotal.toFixed(2)}`;
                                
                                // Update grand total
                                if (pivotData.grand_total !== undefined) {
                                    document.getElementById('grandTotal').textContent =
                                        `₱${parseFloat(pivotData.grand_total).toFixed(2)}`;
                                }
                                
                                Toast('Price updated!');
                            } else {
                                console.error('Update failed:', {matRes, matData, pivotRes, pivotData});
                                Swal.fire('Error', 'Failed to update price.', 'error');
                            }
                        } catch (err) {
                            console.error('Unexpected error:', err);
                            Swal.fire('Error', 'Something went wrong (JS).', 'error');
                        }
                        this.disabled = false;
                        this.classList.remove('is-loading');
                    });
                });
            });
            </script>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @else
                <!-- Staff users see this message if they try to access draft quotations -->
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <p class="text-muted mb-0"><i class="fa-solid fa-lock me-2"></i>Materials and pricing information is restricted to administrators.</p>
                    </div>
                </div>
            @endif

            @if (empty($readonly) && !in_array(strtolower($quotation->status->status_name ?? ''), ['completed','rejected']))
                <!-- Primary Actions: Approve, Save Draft, Reject, Export -->
                <div class="row mt-3">
                    <div class="col-12 d-flex flex-wrap gap-2 justify-content-end">
                        <button type="button" class="btn btn-success" id="approveBtn" data-quot="{{ $quotation->id }}"
                            @if (!$quotation->customer_approved) disabled @endif>
                            <i class="fa-solid fa-check-circle me-1"></i> Approve
                        </button>
                        <button type="button" class="btn btn-primary" id="saveDraftBtn" data-quot="{{ $quotation->id }}">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save Draft
                        </button>
                        <button type="button" class="btn btn-danger" id="rejectBtn" data-quot="{{ $quotation->id }}">
                            <i class="fa-solid fa-ban me-1"></i> Reject
                        </button>
                        <a href="{{ route('quotations.export', ['id' => $quotation->id]) }}"
                            class="btn btn-info d-flex align-items-center">
                            <i class="fa-solid fa-file-word me-1"></i> Export to DOC
                        </a>
                    </div>
                </div>
            @endif




            {{-- 💬 Threaded Comments Section --}}
            @include('components.threaded-comments', ['comments' => $quotation->comments, 'quotationId' => $quotation->id])

<!-- Include Modals -->
@include('include.modals.add_material')
@include('include.modals.new_material')

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
                        <input type="text" class="form-control" id="clientFirstName" name="first_name" value="{{ $client->first_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="clientLastName" class="form-label">Last name</label>
                        <input type="text" class="form-control" id="clientLastName" name="last_name" value="{{ $client->last_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="clientContactInput" class="form-label">Contact</label>
                        <input type="text" class="form-control" id="clientContactInput" name="contact_no" value="{{ $client->contact_no }}">
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
                    // SUCCESS -> toast
                    Toast(data.message || 'Deleted!');
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
                // Prevent form submission on Enter
                input.addEventListener("keypress", (e) => {
                    if (e.key === "Enter") {
                        e.preventDefault();
                    }
                });
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

                if (res.ok && data.success) {
                    // Update line total for this row
                    const row = input.closest("tr");
                    if (row) {
                        const lineTotal = row.querySelector(".line-total");
                        if (lineTotal) {
                            lineTotal.textContent = `₱${parseFloat(data.line_total).toFixed(2)}`;
                        }
                    }

                    // Update grand total
                    if (data.grand_total !== undefined) {
                        const grandTotalEl = document.getElementById("grandTotal");
                        if (grandTotalEl) {
                            grandTotalEl.textContent = `₱${parseFloat(data.grand_total).toFixed(2)}`;
                        }
                    }
                    
                    // Show success toast
                    if (window.Toast && typeof window.Toast === 'function') {
                        Toast('Quantity updated!');
                    }
                } else {
                    console.error("Update failed response:", data);
                    Swal.fire("Update failed", data.message || "Failed to update quantity", "error");
                }
            } catch (error) {
                console.error("Quantity update error:", error);
                Swal.fire("Something went wrong!", error.message || "", "error");
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

                        // SUCCESS -> toast
                        Toast(data.message || 'Fee updated!');
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
                        // SUCCESS -> toast
                        Toast(data.message || 'Success');

                        // Update header and badge based on new status
                        const statusName = data.quotation?.status?.status_name?.toLowerCase() ?? '';
                        const customerApproved = data.quotation?.customer_approved ?? false;
                        let headerText = 'Creating Quotation';
                        let headerClass = 'text-dark';
                        let badgeText = 'Awaiting Client Approval';
                        let badgeClass = 'bg-warning text-dark';

                        if (statusName === 'completed') {
                            headerText = 'Project Completed';
                            headerClass = 'text-success';
                            badgeText = 'Project has been completed';
                            badgeClass = 'bg-success';
                        } else if (statusName === 'rejected') {
                            headerText = 'Quotation Rejected';
                            headerClass = 'text-danger';
                            badgeText = 'Quotation is rejected';
                            badgeClass = 'bg-danger';
                        } else if (customerApproved) {
                            headerText = 'Ongoing Project';
                            headerClass = 'text-primary';
                            badgeText = 'Approved by Client';
                            badgeClass = 'bg-success';
                        }

                        // Update header
                        const headerEl = document.querySelector('.card-body .h3');
                        if (headerEl) {
                            headerEl.textContent = headerText;
                            headerEl.className = `h3 mb-0 ${headerClass}`;
                        }

                        // Update badge
                        const badgeEl = document.getElementById('quotation-status-badge');
                        if (badgeEl) {
                            badgeEl.textContent = badgeText;
                            badgeEl.className = `badge ${badgeClass} mb-3 d-inline-flex align-items-center`;
                        }

                        // Redirect after short delay so toast can show
                        setTimeout(() => {
                            window.location.href = "{{ route('dashboard') }}";
                        }, 900);

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
            const qStatus = "{{ strtolower($quotation->status->status_name ?? '') }}";
            const readonly = {{ empty($readonly) ? 'false' : 'true' }};

            try {
                const res = await fetch(`/quotation/${quotationId}/materials`);
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                const data = await res.json();
                
                if (!data.success) {
                    console.error("Failed to load materials:", data.message);
                    Swal.fire('Error', 'Failed to load materials: ' + (data.message || 'Unknown error'), 'error');
                    return;
                }

                // Store scroll position
                const scrollPosition = window.scrollY;

                const tableBody = document.querySelector("#quotationMaterials tbody");
                if (!tableBody) {
                    console.error("Materials table body not found");
                    return;
                }
                tableBody.innerHTML = "";

                data.materials.forEach(mat => {
                    let priceField = '';
                    
                    // Render price field based on quotation status
                    if (qStatus === 'draft' && !readonly) {
                        // Editable price field for draft
                        priceField = `<input type="number" class="form-control update-price" 
                            data-pivot="${mat.pivot_id}" data-material="${mat.id}"
                            value="${parseFloat(mat.unit_price).toFixed(2)}" min="0" step="0.01"
                            style="width: 100px; display:inline-block;">`;
                    } else {
                        // Display-only price field
                        priceField = `₱${parseFloat(mat.unit_price).toFixed(2)}`;
                    }

                    const row = `
                        <tr>
                            <td>${escapeHtml(mat.name)}</td>
                            <td>
                                <input type="number" class="form-control update-quantity" 
                                    data-pivot="${mat.pivot_id}" data-quot="${quotationId}"
                                    value="${mat.quantity}" min="1"
                                    style="width: 80px; display:inline-block;">
                                <span>${escapeHtml(mat.unit)}</span>
                            </td>
                            <td>${priceField}</td>
                            <td class="line-total">₱${parseFloat(mat.line_total).toFixed(2)}</td>
                            <td class="text-center">
                                ${!readonly ? `
                                    <a href="#" class="text-danger delete-material" 
                                        data-id="${mat.pivot_id}" data-quot="${quotationId}">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                ` : ''}
                            </td>
                        </tr>
                    `;
                    tableBody.insertAdjacentHTML("beforeend", row);
                });

                // ✅ Update grand total
                const grandTotalEl = document.getElementById("grandTotal");
                if (grandTotalEl) {
                    grandTotalEl.textContent = "₱" + parseFloat(data.grand_total).toFixed(2);
                }

                // ✅ Rebind handlers
                try {
                    new QuantityUpdater(".update-quantity");
                    new DeleteMaterialFromQuotation(".delete-material");
                    
                    // Re-bind price update handlers if in draft mode
                    if (qStatus === 'draft' && !readonly) {
                        document.querySelectorAll('.update-price').forEach(function(input) {
                            input.addEventListener('change', async function() {
                                const newPrice = parseFloat(this.value);
                                const pivotId = this.dataset.pivot;
                                const materialId = this.dataset.material;
                                if (isNaN(newPrice) || newPrice < 0) {
                                    Swal.fire('Invalid Price', 'Please enter a valid price.', 'warning');
                                    return;
                                }
                                this.disabled = true;
                                this.classList.add('is-loading');
                                try {
                                    // Update material price
                                    const matRes = await fetch(`/materials/${materialId}/update-price`, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                            'Accept': 'application/json'
                                        },
                                        body: JSON.stringify({ price: newPrice })
                                    });
                                    // Update quotation_materials pivot price
                                    const pivotRes = await fetch(`/quotation-materials/${pivotId}/update-unit-cost`, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                            'Accept': 'application/json'
                                        },
                                        body: JSON.stringify({ unit_cost: newPrice })
                                    });

                                    let matData, pivotData;
                                    try {
                                        if (matRes.headers.get('content-type')?.includes('application/json')) {
                                            matData = await matRes.json();
                                        } else {
                                            throw new Error('Material response not JSON');
                                        }
                                    } catch (e) {
                                        console.error('Material price response error:', e);
                                        Swal.fire('Error', 'Material price response not JSON.', 'error');
                                        this.disabled = false;
                                        this.classList.remove('is-loading');
                                        return;
                                    }
                                    try {
                                        if (pivotRes.headers.get('content-type')?.includes('application/json')) {
                                            pivotData = await pivotRes.json();
                                        } else {
                                            throw new Error('Pivot response not JSON');
                                        }
                                    } catch (e) {
                                        console.error('Pivot price response error:', e);
                                        Swal.fire('Error', 'Pivot price response not JSON.', 'error');
                                        this.disabled = false;
                                        this.classList.remove('is-loading');
                                        return;
                                    }

                                    if (matRes.ok && matData.success && pivotRes.ok && pivotData.success) {
                                        // Update the line total for this row
                                        const row = this.closest('tr');
                                        const quantity = parseFloat(row.querySelector('.update-quantity').value);
                                        const lineTotal = newPrice * quantity;
                                        row.querySelector('.line-total').textContent = `₱${lineTotal.toFixed(2)}`;
                                        
                                        // Update grand total
                                        if (pivotData.grand_total !== undefined) {
                                            document.getElementById('grandTotal').textContent =
                                                `₱${parseFloat(pivotData.grand_total).toFixed(2)}`;
                                        }
                                        
                                        Toast('Price updated!');
                                    } else {
                                        console.error('Update failed:', {matRes, matData, pivotRes, pivotData});
                                        Swal.fire('Error', 'Failed to update price.', 'error');
                                    }
                                } catch (err) {
                                    console.error('Unexpected error:', err);
                                    Swal.fire('Error', 'Something went wrong: ' + err.message, 'error');
                                }
                                this.disabled = false;
                                this.classList.remove('is-loading');
                            });
                        });
                    }
                } catch (handlerErr) {
                    console.error("Error binding handlers:", handlerErr);
                }

                // Restore scroll position after content update
                window.scrollTo(0, scrollPosition);

                // Ensure the page is scrollable to the new content
                document.body.style.height = 'auto';
                document.body.style.overflow = 'visible';
                
            } catch (err) {
                console.error("Failed to reload materials:", err);
                Swal.fire('Error', 'Failed to load materials: ' + err.message, 'error');
            }
        }
    };
    
    // Helper function to escape HTML
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
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

<script>
    // Edit Client: open modal, submit update via fetch, and update UI
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
                first_name: document.getElementById('clientFirstName').value.trim(),
                last_name: document.getElementById('clientLastName').value.trim(),
                contact_no: document.getElementById('clientContactInput').value.trim(),
                address: document.getElementById('clientAddressInput').value.trim(),
            };

            try {
                const clientId = '{{ $client->id }}';
                const res = await fetch(`/clients/${clientId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    // Update UI
                    document.getElementById('clientName').textContent = data.client.first_name + ' ' + data.client.last_name;
                    document.getElementById('clientContact').textContent = data.client.contact_no || '';
                    document.getElementById('clientAddress').textContent = data.client.address || '';

                    bsModal.hide();
                    Swal.fire({toast:true, position:'top-end', icon:'success', title:'Client updated', showConfirmButton:false, timer:1200});
                } else {
                    Swal.fire('Error', data.message || 'Failed to update client', 'error');
                }
            } catch (err) {
                console.error('Client update error:', err);
                Swal.fire('Error', 'Something went wrong', 'error');
            }

            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Save changes';
        });
    });
</script>

<!-- Updated: Safe cleanup + Toast helper (placed after other scripts) -->
<script>
/**
 * ✅ Safe Modal Cleanup + Global Toast helper
 * - Keeps your existing modal handling and methods intact.
 * - DOES NOT remove .modal-backdrop or body.modal-open (Bootstrap handles that).
 * - Disposes instances on hidden to avoid stale instances.
 * - Provides Toast(message) for success toasts. Use Toast(...) in place of Swal.fire success.
 */

// (redundant disposal is harmless; ensures any modal hidden will have instance disposed)
document.querySelectorAll('.modal').forEach(modalEl => {
    modalEl.addEventListener('hidden.bs.modal', () => {
        const instance = bootstrap.Modal.getInstance(modalEl);
        if (instance) instance.dispose();
        // Accessibility: blur any focused element inside a now-hidden modal
        if (document.activeElement && document.activeElement.closest && document.activeElement.closest('.modal[aria-hidden="true"]')) {
            try { document.activeElement.blur(); } catch (e) { /* ignore */ }
        }
    });
});

/**
 * Global Toast helper for success messages.
 * Keep errors as Swal.fire(...) (modal) so they remain prominent.
 */
window.Toast = (message = 'Success', icon = 'success') => {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icon,
        title: message,
        showConfirmButton: false,
        timer: 1200,
        timerProgressBar: true
    });
};
</script>
@endsection
