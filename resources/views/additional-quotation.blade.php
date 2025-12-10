{{-- @extends('layouts.app')
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
                        $qStatus = strtolower($additionalQuotation->status->status_name ?? '');
                        if ($qStatus === 'completed') {
                            $headerText = 'Additional Quotation - Completed';
                            $headerClass = 'text-success';
                        } elseif ($qStatus === 'rejected') {
                            $headerText = 'Additional Quotation - Rejected';
                            $headerClass = 'text-danger';
                        } elseif ($additionalQuotation->progress >= 100) {
                            $headerText = 'Additional Quotation - Approved & Attached';
                            $headerClass = 'text-success';
                        } else {
                            $headerText = 'Creating Additional Quotation';
                            $headerClass = 'text-dark';
                        }
                    @endphp
                    <h1 class="h3 mb-0 {{ $headerClass }}">{{ $headerText }}</h1>
                </div>
            </div>

            <!-- Additional Quotation Info -->
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="mb-3">{{ $additionalQuotation->subject }}</h3>
                    <p><strong>Parent Quotation:</strong> {{ $additionalQuotation->parentQuotation->subject }}
                        (ID: {{ $additionalQuotation->parent_quotation_id }})
                    </p>
                    <p><strong>Customer:</strong> <span id="clientName">{{ $additionalQuotation->parentQuotation->client->first_name }}
                            {{ $additionalQuotation->parentQuotation->client->last_name }}</span>
                        @php
                            $qStatus = strtolower($additionalQuotation->status->status_name ?? '');
                            $canEditClient = !(
                                $qStatus === 'completed' ||
                                $qStatus === 'rejected' ||
                                ($additionalQuotation->progress >= 100 && $additionalQuotation->customer_approved)
                            );

                            // Detect staff role robustly to hide admin actions on staff accounts
                            $isStaff = false;
                            if (Auth::check()) {
                                $user = Auth::user();
                                if (method_exists($user, 'hasRole')) {
                                    try {
                                        $isStaff = (bool) $user->hasRole('staff');
                                    } catch (\Throwable $e) {
                                        $isStaff = false;
                                    }
                                } elseif (isset($user->role)) {
                                    if (is_string($user->role)) {
                                        $isStaff = strtolower($user->role) === 'staff';
                                    } elseif (is_object($user->role) && isset($user->role->name)) {
                                        $isStaff = strtolower($user->role->name) === 'staff';
                                    }
                                } elseif (isset($user->role_name)) {
                                    $isStaff = strtolower($user->role_name) === 'staff';
                                }
                            }
                        @endphp

                        @if ($canEditClient && !$isStaff)
                            <button type="button" class="btn btn-sm btn-outline-secondary ms-2" id="editClientBtn">Edit
                                Client</button>
                        @endif
                    </p>
                    <p><strong>Contact:</strong> <span id="clientContact">{{ $additionalQuotation->parentQuotation->client->contact_no }}</span></p>
                    <p><strong>Address:</strong> <span id="clientAddress">{{ $additionalQuotation->parentQuotation->client->address }}</span></p>
                    @if (!empty($additionalQuotation->description))
                        <p class="text-muted mb-2"><strong>Description:</strong>
                            {!! nl2br(e($additionalQuotation->description)) !!}
                        </p>
                    @endif

                    @php
                        $qStatus = strtolower($additionalQuotation->status->status_name ?? '');
                        if ($qStatus === 'completed') {
                            $badgeText = 'Additional Quotation Completed';
                            $badgeClass = 'bg-success';
                        } elseif ($qStatus === 'rejected') {
                            $badgeText = 'Additional Quotation Rejected';
                            $badgeClass = 'bg-danger';
                        } elseif ($additionalQuotation->progress >= 100) {
                            $badgeText = 'Approved & Attached to Parent';
                            $badgeClass = 'bg-success';
                        } elseif ($additionalQuotation->customer_approved) {
                            $badgeText = 'Approved by Client (Pending Attachment)';
                            $badgeClass = 'bg-success';
                        } else {
                            $badgeText = 'Draft - Awaiting Approval';
                            $badgeClass = 'bg-warning text-dark';
                        }
                    @endphp

                    <div class="mt-3">
                        <p> <strong> Status: </strong> 
                            <span class="fw-500">
                                @php
                                    if ($badgeClass === 'bg-success') {
                                        $icon = '<i class="fa-solid fa-circle text-success me-2" style="font-size: 0.5rem;"></i>';
                                    } elseif ($badgeClass === 'bg-warning text-dark') {
                                        $icon = '<i class="fa-solid fa-circle text-warning me-2" style="font-size: 0.5rem;"></i>';
                                    } elseif ($badgeClass === 'bg-danger') {
                                        $icon = '<i class="fa-solid fa-circle text-danger me-2" style="font-size: 0.5rem;"></i>';
                                    } else {
                                        $icon = '<i class="fa-solid fa-circle text-secondary me-2" style="font-size: 0.5rem;"></i>';
                                    }
                                @endphp
                                {!! $icon !!}{{ $badgeText }}
                            </span>
                            id="quotation-status-badge">
                            {{ $badgeText }}
                        </span></p>
                    </div>
                </div>
            </div>

            <!-- Materials Table (Admin only) -->
            @if (Auth::user()->can('view_materials'))
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-12 col-md-auto mb-2 mb-md-0">
                                <h5 class="mb-0">Materials</h5>
                            </div>
                            <div class="col-12 col-md">
                                <div class="d-flex flex-wrap gap-2 justify-content-end">
                                    @php
                                        $qStatus = strtolower($additionalQuotation->status->status_name ?? '');
                                        $canAddMaterial =
                                            empty($readonly) &&
                                            $qStatus !== 'completed' &&
                                            $qStatus !== 'rejected' &&
                                            $additionalQuotation->progress < 100;
                                        $canCreateRevision =
                                            empty($readonly) &&
                                            Auth::user()->can('create_revision') &&
                                            $qStatus !== 'completed' &&
                                            $qStatus !== 'rejected';
                                    @endphp

                                    @if ($canAddMaterial)
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#addMatModal">
                                            <i class="fa-solid fa-plus me-1"></i>
                                            <span class="d-none d-sm-inline">Add Material</span>
                                            <span class="d-sm-none">Add</span>
                                        </button>
                                    @endif

                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="generateLinkBtn">
                                        <i class="fa-solid fa-link me-1"></i>
                                        <span class="d-none d-sm-inline">Generate Link</span>
                                        <span class="d-sm-none">Link</span>
                                    </button>

                                    @if (Auth::user()->can('view_revision_history'))
                                        <button type="button" class="btn btn-sm btn-outline-info" id="viewRevisionsBtn"
                                            data-id="{{ $additionalQuotation->id }}">
                                            <i class="fa-solid fa-clock-rotate-left me-1"></i>
                                            <span class="d-none d-sm-inline">View Revisions</span>
                                            <span class="d-sm-none">Revisions</span>
                                        </button>
                                    @endif

                                    @if ($canCreateRevision)
                                        <button type="button" class="btn btn-sm btn-outline-warning" id="createRevisionBtn"
                                            data-id="{{ $additionalQuotation->id }}">
                                            <i class="fa-solid fa-copy me-1"></i>
                                            <span class="d-none d-sm-inline">Create Revision</span>
                                            <span class="d-sm-none">Revision</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="quotationMaterials" class="table table-bordered table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Material</th>
                                    <th>Estimated Quantity</th>
                                    <th>Price/Unit</th>
                                    <th class="text-end">Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($materials as $mat)
                                    <tr>
                                        <td>{{ $mat->name }}</td>
                                        <td>
                                            <input type="number" class="form-control update-quantity"
                                                data-pivot="{{ $mat->pivot->id }}" data-quotation="{{ $additionalQuotation->id }}"
                                                data-type="additional"
                                                value="{{ $mat->pivot->quantity }}" min="1"
                                                style="width: 80px; display:inline-block;">
                                            <span>{{ $mat->unit }}</span>
                                        </td>
                                        <td>
                                            @if (Auth::user()->can('view_prices'))
                                                <input type="text" class="form-control update-price text-end"
                                                    data-pivot="{{ $mat->pivot->id }}"
                                                    data-material="{{ $mat->id }}"
                                                    value="{{ number_format($mat->pivot->unit_cost ?? $mat->unit_price, 2) }}"
                                                    style="width: 100px; display:inline-block;">
                                            @else
                                                <span class="badge bg-secondary">Hidden</span>
                                            @endif
                                        </td>
                                        <td class="line-total text-end">
                                            ₱{{ number_format(($mat->pivot->unit_cost ?? $mat->unit_price) * $mat->pivot->quantity, 2) }}
                                        </td>
                                        </td>
                                        <td class="text-center">
                                            <a href="#" class="text-danger delete-material"
                                                data-id="{{ $mat->pivot->id }}" data-quotation="{{ $additionalQuotation->id }}"
                                                data-type="additional">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                @if (Auth::user()->can('manage_fees'))
                                    @php
                                        $qStatus = strtolower($additionalQuotation->status->status_name ?? '');
                                        $canEditFees =
                                            empty($readonly) &&
                                            $qStatus !== 'completed' &&
                                            $qStatus !== 'rejected' &&
                                            $additionalQuotation->progress < 100;
                                    @endphp
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Labor Fee:</td>
                                        <td class="text-end">
                                            @if ($canEditFees)
                                                <div class="input-group">
                                                    <span class="input-group-text">₱</span>
                                                    <input type="text" class="form-control text-end fee-input labor-fee-display" 
                                                        id="laborFee"
                                                        placeholder="0.00"
                                                        data-field="labor_fee"
                                                        data-validate="price"
                                                        data-quotation="{{ $additionalQuotation->id }}"
                                                        data-type="additional"
                                                        value="{{ number_format($additionalQuotation->labor_fee, 2) }}"
                                                        style="font-family: inherit;">
                                                </div>
                                            @else
                                                <span>₱{{ number_format($additionalQuotation->labor_fee, 2) }}</span>
                                            @endif
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Delivery/Hauling Fee:</td>
                                        <td class="text-end">
                                            @if ($canEditFees)
                                                <div class="input-group">
                                                    <span class="input-group-text">₱</span>
                                                    <input type="text" class="form-control text-end fee-input delivery-fee-display" 
                                                        id="deliveryFee" 
                                                        placeholder="0.00"
                                                        data-field="delivery_fee"
                                                        data-validate="price"
                                                        data-quotation="{{ $additionalQuotation->id }}"
                                                        data-type="additional"
                                                        value="{{ number_format($additionalQuotation->delivery_fee, 2) }}"
                                                        style="font-family: inherit;">
                                                </div>
                                            @else
                                                <span>₱{{ number_format($additionalQuotation->delivery_fee, 2) }}</span>
                                            @endif
                                        </td>
                                        <td></td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                                    <td class="fw-bold text-danger text-end" id="grandTotal">
                                        @if (Auth::user()->can('view_prices'))
                                            <span id="grandTotalValue" class="grand-total-display">
                                                ₱<span id="grandTotalAmount">{{ number_format($materials->sum(fn($m) => ($m->pivot->unit_cost ?? $m->unit_price) * $m->pivot->quantity) + $additionalQuotation->labor_fee + $additionalQuotation->delivery_fee, 2) }}</span>
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Hidden</span>
                                        @endif
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif


            @php
                $qStatus = strtolower($additionalQuotation->status->status_name ?? '');
                $isCompleted = $qStatus === 'completed';
                $isRejected = $qStatus === 'rejected';
                $canApprove = $additionalQuotation->progress >= 100 && $additionalQuotation->customer_approved;

                // Show primary action buttons only if not readonly and not in completed/rejected state
                $showPrimaryActions = empty($readonly) && !$isCompleted && !$isRejected;

                // Show export button for all states (except readonly)
                $showExport = empty($readonly) || $isCompleted || $isRejected;
            @endphp

            @if ($showPrimaryActions)
                <!-- Primary Actions: Approve, Save Draft, Reject, Export -->
                <div class="row mt-3">
                    <div class="col-12 d-flex flex-wrap gap-2 justify-content-end">
                        @if (!$isRejected && !$isStaff)
                            <button type="button" class="btn btn-success" id="approveBtn"
                                data-bs-toggle="modal" data-bs-target="#approveModal"
                                data-quotation="{{ $additionalQuotation->id }}" 
                                data-type="additional"
                                @if (!$canApprove) disabled title="Progress must be 100% and customer must approve first" @endif>
                                <i class="fa-solid fa-check-circle me-1"></i> Approve & Attach
                            </button>
                        @endif

                        <button type="button" class="btn btn-primary" id="saveDraftBtn"
                            data-quotation="{{ $additionalQuotation->id }}"
                            data-type="additional">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save Draft
                        </button>

                        @if (!$isRejected && !$isStaff)
                            <button type="button" class="btn btn-danger" id="rejectBtn"
                                data-quotation="{{ $additionalQuotation->id }}"
                                data-type="additional">
                                <i class="fa-solid fa-ban me-1"></i> Reject
                            </button>
                        @endif

                        @if (!$isStaff)
                            <a href="{{ route('additional-quotations.export', ['id' => $additionalQuotation->id]) }}"
                                class="btn btn-info d-flex align-items-center">
                                <i class="fa-solid fa-file-word me-1"></i> Export to DOC
                            </a>
                        @endif
                    </div>
                </div>
            @elseif ($showExport && !$isStaff)
                <!-- Limited Actions: Export only (for completed or rejected projects) -->
                <div class="row mt-3">
                    <div class="col-12 d-flex flex-wrap gap-2 justify-content-end">
                        <a href="{{ route('additional-quotations.export', ['id' => $additionalQuotation->id]) }}"
                            class="btn btn-info d-flex align-items-center">
                            <i class="fa-solid fa-file-word me-1"></i> Export to DOC
                        </a>
                    </div>
                </div>
            @endif




            {{-- 💬 Threaded Comments Section (admin/staff) --}}
            @include('components.threaded-comments-admin', [
                'comments' => $additionalQuotation->comments,
                'quotationId' => $additionalQuotation->id,
                'quotationType' => 'additional',
            ])

            <!-- Include Modals -->
            @include('include.modals.add_material')
            @include('include.modals.new_material')

            <!-- Edit Client Modal -->
            <div class="modal fade" id="editClientModal" tabindex="-1" aria-labelledby="editClientLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editClientLabel">Edit Client</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editClientForm">
                                <div class="mb-3">
                                    <label for="clientFirstName" class="form-label">First name</label>
                                    <input type="text" class="form-control" id="clientFirstName" name="first_name"
                                        value="{{ $additionalQuotation->parentQuotation->client->first_name }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="clientLastName" class="form-label">Last name</label>
                                    <input type="text" class="form-control" id="clientLastName" name="last_name"
                                        value="{{ $additionalQuotation->parentQuotation->client->last_name }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="clientContactInput" class="form-label">Contact</label>
                                    <input type="text" class="form-control" id="clientContactInput" name="contact_no"
                                        value="{{ $additionalQuotation->parentQuotation->client->contact_no }}">
                                </div>
                                <div class="mb-3">
                                    <label for="clientAddressInput" class="form-label">Address</label>
                                    <textarea class="form-control" id="clientAddressInput" name="address" rows="3">{{ $additionalQuotation->parentQuotation->client->address }}</textarea>
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
            <div class="modal fade" id="revisionHistoryModal" tabindex="-1" aria-labelledby="revisionHistoryLabel"
                aria-hidden="true">
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

            <!-- Approve Quotation Modal -->
            <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="approveModalLabel">Approve Quotation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <form id="approveForm">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="contractSubject" class="form-label">Contract Subject</label>
                                    <input type="text" class="form-control" id="contractSubject" 
                                        name="contract_subject" placeholder="Enter contract subject" required>
                                </div>

                                <div class="mb-3">
                                    <label for="projectStartDate" class="form-label">Project Start Date</label>
                                    <input type="date" class="form-control" id="projectStartDate" 
                                        name="project_start_date" required>
                                </div>

                                <div class="mb-3">
                                    <label for="projectEndDate" class="form-label">Project End Date</label>
                                    <input type="date" class="form-control" id="projectEndDate" 
                                        name="project_end_date" required>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="withContract" 
                                        name="with_contract" value="1">
                                    <label class="form-check-label" for="withContract">
                                        With Contract
                                    </label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fa-solid fa-check-circle me-1"></i> Approve
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ---------------- Scripts ---------------- -->

    <script>
        /**
         * Format a number with thousand separators (commas)
         * Example: 123456.78 => "123,456.78"
         */
        function formatNumberWithCommas(value) {
            if (value === null || value === undefined) return '0.00';
            const num = parseFloat(value);
            if (isNaN(num)) return '0.00';
            // Format number with 2 decimal places, then add commas
            const formatted = num.toFixed(2);
            return formatted.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        /**
         * Update grand total display with proper comma formatting
         * Example: updateGrandTotalDisplay(123456.50) => ₱123,456.50
         */
        function updateGrandTotalDisplay(amount) {
            // Prefer updating the inner amount span if present so we don't strip
            // surrounding markup / classes. Fall back to replacing the cell text.
            const grandTotalAmountEl = document.getElementById('grandTotalAmount');
            if (grandTotalAmountEl) {
                grandTotalAmountEl.textContent = formatNumberWithCommas(amount);
                // Ensure parent shows currency prefix if needed
                const parent = grandTotalAmountEl.closest('#grandTotal');
                if (parent) {
                    // parent may already contain the currency symbol in markup; keep it.
                    // if not, ensure the visible text is correct by leaving structure intact.
                }
                return;
            }

            const grandTotalEl = document.getElementById("grandTotal");
            if (grandTotalEl) {
                grandTotalEl.textContent = "₱" + formatNumberWithCommas(amount);
            }
        }

        /**
         * Compute grand total on the client by summing all visible line totals
         * and fee inputs. Useful when server responses don't include grand_total.
         */
        function computeAndUpdateGrandTotal() {
            let materialsTotal = 0;
            document.querySelectorAll('.line-total').forEach(el => {
                try {
                    const txt = el.textContent || '';
                    // remove currency symbol and commas
                    const raw = txt.replace(/[^0-9.\-]/g, '');
                    const v = parseFloat(raw);
                    if (!isNaN(v)) materialsTotal += v;
                } catch (e) { /* ignore malformed cells */ }
            });

            let feesTotal = 0;
            document.querySelectorAll('.fee-input').forEach(inp => {
                try {
                    const val = inp.value || '0';
                    const raw = val.toString().replace(/,/g, '').replace(/[^0-9.\-]/g, '');
                    const v = parseFloat(raw);
                    if (!isNaN(v)) feesTotal += v;
                } catch (e) { /* ignore malformed inputs */ }
            });

            const grand = materialsTotal + feesTotal;
            updateGrandTotalDisplay(grand);
            return grand;
        }

        /**
         * Bind formatting behavior to editable price inputs (.update-price)
         * - on focus: remove commas so user can type raw number
         * - on blur: format with commas
         */
        function bindPriceInputs() {
            document.querySelectorAll('.update-price').forEach(input => {
                if (input.dataset.priceBound) return;
                input.dataset.priceBound = 'true';

                input.addEventListener('focus', (e) => {
                    const v = e.target.value || '';
                    e.target.value = v.toString().replace(/,/g, '').trim();
                });

                input.addEventListener('blur', (e) => {
                    if (e.target.value === '') {
                        e.target.value = '0.00';
                    }
                    try {
                        e.target.value = formatNumberWithCommas(e.target.value);
                    } catch (err) {
                        console.error('Format price error', err);
                    }
                });

                // Attach change handler to send updated unit price to the server
                // and update the line total + grand total immediately.
                if (!input.dataset.priceChangeBound) {
                    input.dataset.priceChangeBound = 'true';
                    input.addEventListener('change', async function() {
                        // Read raw numeric value (strip commas)
                        const raw = this.value ? this.value.toString().replace(/,/g, '') : '';
                        const newPrice = parseFloat(raw);
                        const pivotId = this.dataset.pivot;
                        const materialId = this.dataset.material;

                        if (isNaN(newPrice) || newPrice < 0) {
                            Swal.fire('Invalid Price', 'Please enter a valid price.', 'warning');
                            // Reformat back to previous or 0.00
                            try { this.value = formatNumberWithCommas(this.value || 0); } catch (e) {}
                            return;
                        }

                        this.disabled = true;
                        this.classList.add('is-loading');

                        try {
                            const pivotRes = await fetch(`/additional-quotation-materials/${pivotId}/update-price`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ unit_cost: newPrice })
                            });

                            let pivotData;
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

                            if (pivotRes.ok && pivotData.success) {
                                // Update the line total for this row
                                const row = this.closest('tr');
                                if (row) {
                                    const quantity = parseFloat(row.querySelector('.update-quantity')?.value || 0);
                                    const lineTotal = newPrice * (isNaN(quantity) ? 0 : quantity);
                                    const lineEl = row.querySelector('.line-total');
                                    if (lineEl) lineEl.textContent = `₱${formatNumberWithCommas(lineTotal)}`;
                                }

                                // Update grand total (use central updater). If server didn't
                                // include grand_total, compute it from the DOM as a fallback.
                                if (pivotData.grand_total !== undefined) {
                                    updateGrandTotalDisplay(pivotData.grand_total);
                                } else {
                                    computeAndUpdateGrandTotal();
                                }

                                // Format and show the new price in the input
                                try { this.value = formatNumberWithCommas(newPrice); } catch (e) {}
                                Toast('Price updated!');
                            } else {
                                console.error('Pivot update failed:', { pivotRes, pivotData });
                                Swal.fire('Error', 'Failed to update price.', 'error');
                            }
                        } catch (err) {
                            console.error('Unexpected error:', err);
                            Swal.fire('Error', 'Something went wrong: ' + (err.message || err), 'error');
                        }

                        this.disabled = false;
                        this.classList.remove('is-loading');
                    });
                }
            });
        }

        function appendMaterialsToTable(materials) {
            // Target the specific quotation materials table tbody to avoid
            // accidentally changing other tables on the page which can cause
            // layout shifts when rows are appended.
            const tbody = document.querySelector("#quotationMaterials tbody");

            // Preserve the horizontal scroll position of the table wrapper
            // and the current vertical scroll so adding rows doesn't shift
            // the entire page (common when scrollbars appear/disappear).
            const tableWrapper = document.querySelector('#quotationMaterials').closest('.table-responsive');
            const prevScrollLeft = tableWrapper ? tableWrapper.scrollLeft : 0;
            const prevWindowScrollY = window.scrollY || window.pageYOffset || 0;

            materials.forEach(mat => {
                const row = `<tr>
<td>${escapeHtml(mat.name)}</td>
<td>
<input type="number" class="form-control update-quantity" 
    data-pivot="${mat.pivot_id}" data-quotation="{{ $additionalQuotation->id }}" 
    data-type="additional"
    value="${mat.quantity}" min="1" style="width: 80px; display:inline-block;">
<span>${escapeHtml(mat.unit)}</span>
</td>
<td>₱${formatNumberWithCommas(mat.unit_price)}</td>
<td class="line-total text-end">₱${formatNumberWithCommas(mat.line_total)}</td>
<td class="text-center">
<a href="#" class="text-danger delete-material" data-id="${mat.pivot_id}" data-material="${mat.id}" data-quotation="{{ $additionalQuotation->id }}" data-type="additional">
<i class="fa-solid fa-trash"></i>
</a>
</td>
</tr>`;
                tbody.insertAdjacentHTML("beforeend", row);
            });

            // Rebind events for new quantity inputs and delete links
            new QuantityUpdater(".update-quantity");
            new DeleteMaterialFromQuotation(".delete-material");
            // Bind price input formatting for new rows
            bindPriceInputs();

            // Ensure grand total reflects newly appended materials
            computeAndUpdateGrandTotal();

            // Restore scroll positions to prevent small layout shifts
            if (tableWrapper) tableWrapper.scrollLeft = prevScrollLeft;
            // restore window vertical scroll after DOM changes
            window.scrollTo(0, prevWindowScrollY);

            // Trigger a resize event so responsive wrappers can recalculate widths
            // (helps avoid one-time layout offsets until a full refresh)
            try { window.dispatchEvent(new Event('resize')); } catch (e) { /* noop */ }
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
                    const quotationId = btn.dataset.quotation || btn.dataset.quot;
                    const materialId = btn.dataset.material || btn.dataset.id;
                    this.deleteMaterial(quotationId, materialId, btn.closest("tr"));
                });
            }
            async deleteMaterial(quotationId, materialId, rowEl) {
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
                    const res = await fetch(`/additional-quotations/${quotationId}/materials/${materialId}`, {
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
                            updateGrandTotalDisplay(data.grand_total);
                        } else {
                            computeAndUpdateGrandTotal();
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
                    quotId = input.dataset.quotation;

                try {
                    const res = await fetch(`/additional-quotation-materials/${pivotId}/update-quantity`, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
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
                                lineTotal.textContent = `₱${formatNumberWithCommas(data.line_total)}`;
                            }
                        }

                        // Update grand total
                        if (data.grand_total !== undefined) {
                            updateGrandTotalDisplay(data.grand_total);
                        } else {
                            computeAndUpdateGrandTotal();
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
            constructor(selector, quotationId, csrfToken, quotationType = 'quotation') {
                this.selector = selector;
                this.quotationId = quotationId;
                this.csrfToken = csrfToken;
                this.quotationType = quotationType;
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
                        const endpoint = this.quotationType === 'additional' 
                            ? `/additional-quotations/${this.quotationId}/update-fee`
                            : `/quotations/${this.quotationId}/update-fee`;
                        
                        console.log('Sending Fee Update Request:', {
                            url: endpoint,
                            field,
                            value
                        });

                        const res = await fetch(endpoint, {
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

                            // ✅ Update grand total UI (fallback to client compute if missing)
                            if (data.grand_total !== undefined) {
                                updateGrandTotalDisplay(data.grand_total);
                            } else {
                                computeAndUpdateGrandTotal();
                            }

                            // Store the updated fee value in the input (formatted with commas)
                            input.value = formatNumberWithCommas(value);

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
                "{{ $additionalQuotation->id ?? '' }}",
                "{{ csrf_token() }}",
                "additional"
            );
        }
    </script>




    <!-- Quotation Status Buttons -->
    <script>
        class QuotationStatusHandler {
            constructor(quotationType = 'quotation') {
                this.quotationType = quotationType;
                this.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
                this.bindEvents();
                this.bindApproveForm();
            }

            bindEvents() {
                // Handle Save Draft and Reject buttons
                document.querySelectorAll("#saveDraftBtn, #rejectBtn").forEach(button => {
                    button.addEventListener("click", (e) => {
                        e.preventDefault();
                        const quotationId = button.dataset.quotation || button.dataset.quot;
                        let statusId = null;

                        switch (button.id) {
                            case "saveDraftBtn":
                                statusId = 1;
                                break;
                            case "rejectBtn":
                                statusId = 3;
                                break;
                        }

                        if (statusId) this.updateStatus(quotationId, statusId);
                    });
                });
            }

            bindApproveForm() {
                const approveForm = document.getElementById('approveForm');
                if (!approveForm) return;

                approveForm.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    const quotationId = document.getElementById('approveBtn').dataset.quotation || document.getElementById('approveBtn').dataset.quot;
                    const contractSubject = document.getElementById('contractSubject').value.trim();
                    const projectStartDate = document.getElementById('projectStartDate').value;
                    const projectEndDate = document.getElementById('projectEndDate').value;
                    const withContract = document.getElementById('withContract').checked;

                    // Validate dates
                    if (projectStartDate && projectEndDate && projectStartDate > projectEndDate) {
                        Swal.fire('Validation Error', 'Project start date must be before end date.', 'warning');
                        return;
                    }

                    // For additional quotations, we don't send contract data
                    // For regular quotations, we send full data
                    const contractData = this.quotationType === 'additional'
                        ? {}  // No contract data for additional quotations
                        : {
                            contract_subject: contractSubject,
                            project_start_date: projectStartDate,
                            project_end_date: projectEndDate,
                            with_contract: withContract ? 1 : 0
                          };

                    // Approve with status ID 2
                    await this.updateStatus(quotationId, 2, contractData);

                    // Close modal on success
                    const modal = bootstrap.Modal.getInstance(document.getElementById('approveModal'));
                    if (modal) modal.hide();
                });
            }

            async updateStatus(quotationId, statusId, contractData = {}) {
                try {
                    const payload = {
                        status_id: statusId,
                        ...contractData  // Include contract data if provided (for approve action)
                    };

                    const endpoint = this.quotationType === 'additional'
                        ? `/additional-quotations/${quotationId}/status`
                        : `/quotations/${quotationId}/status`;

                    const res = await fetch(endpoint, {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": this.csrfToken,
                            "Accept": "application/json"
                        },
                        body: JSON.stringify(payload)
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

        new QuotationStatusHandler('additional');
    </script>

    <script>
        window.quotationMaterialHandler = {
            loadMaterials: async function() {
                const quotationId = "{{ $additionalQuotation->id }}";
                const qStatus = "{{ strtolower($additionalQuotation->status->status_name ?? '') }}";
                const readonly = {{ empty($readonly) ? 'false' : 'true' }};

                try {
                    const res = await fetch(`/additional-quotation/${quotationId}/materials`);
                    if (!res.ok) {
                        throw new Error(`HTTP error! status: ${res.status}`);
                    }
                    const data = await res.json();

                    if (!data.success) {
                        console.error("Failed to load materials:", data.message);
                        Swal.fire('Error', 'Failed to load materials: ' + (data.message || 'Unknown error'),
                            'error');
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
                            // Editable price field for draft (use text so we can show commas)
                            priceField = `<input type="text" class="form-control update-price text-end" 
                            data-pivot="${mat.pivot_id}" data-material="${mat.id}"
                            value="${formatNumberWithCommas(mat.unit_price)}" 
                            style="width: 100px; display:inline-block;">`;
                        } else {
                            // Display-only price field
                            priceField = `₱${formatNumberWithCommas(mat.unit_price)}`;
                        }

                        const row = `
                        <tr>
                            <td>${escapeHtml(mat.name)}</td>
                            <td>
                                <input type="number" class="form-control update-quantity" 
                                    data-pivot="${mat.pivot_id}" data-quotation="${quotationId}" data-type="additional"
                                    value="${mat.quantity}" min="1"
                                    style="width: 80px; display:inline-block;">
                                <span>${escapeHtml(mat.unit)}</span>
                            </td>
                            <td>${priceField}</td>
                            <td class="line-total text-end">₱${formatNumberWithCommas(mat.line_total)}</td>
                            <td class="text-center">
                                ${!readonly ? `
                                                <a href="#" class="text-danger delete-material" 
                                                    data-id="${mat.pivot_id}" data-quotation="${quotationId}" data-type="additional">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            ` : ''}
                            </td>
                        </tr>
                    `;
                        tableBody.insertAdjacentHTML("beforeend", row);
                    });

                    // ✅ Update grand total using centralized updater (preserves markup)
                    if (data.grand_total !== undefined) {
                        updateGrandTotalDisplay(data.grand_total);
                    } else {
                        computeAndUpdateGrandTotal();
                    }

                        // Ensure price inputs are bound (formatting) before attaching handlers
                        bindPriceInputs();

                        // ✅ Rebind handlers
                    try {
                        new QuantityUpdater(".update-quantity");
                        new DeleteMaterialFromQuotation(".delete-material");

                        // Price inputs are bound via bindPriceInputs() above which attaches
                        // both formatting and the change handler. Avoid duplicate bindings
                        // here to prevent conflicting updates.
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
            // Coerce to string to avoid calling replace on null/undefined
            const s = String(unsafe ?? '');
            return s
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/\"/g, "&quot;")
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

                fetch(`/additional-quotations/${id}/create-revision`, {
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

            fetch(`/additional-quotations/${id}/revisions-json`) // new route returning JSON
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
                        <p><strong>Labor Fee:</strong> ₱${formatNumberWithCommas(rev.data.labor_fee)}</p>
                        <p><strong>Delivery Fee:</strong> ₱${formatNumberWithCommas(rev.data.delivery_fee)}</p>

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
                                                                                                                                                                                                                                <td>₱${formatNumberWithCommas(mat.unit_price)}</td>
                                                                                                                                                                                                                                <td>${mat.quantity}</td>
                                                                                                                                                                                                                                <td>₱${formatNumberWithCommas(parseFloat(mat.unit_price) * mat.quantity)}</td>
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

    <!-- Comments handled by threaded-comments component with built-in JS handlers -->

    <script>
        // Edit Client: open modal, submit update via fetch, and update UI
        document.addEventListener('DOMContentLoaded', function() {
            const editBtn = document.getElementById('editClientBtn');
            const saveBtn = document.getElementById('saveClientBtn');
            const modalEl = document.getElementById('editClientModal');
            if (!editBtn || !saveBtn || !modalEl) return;

            editBtn.addEventListener('click', () => {
                // Use getOrCreateInstance to avoid stale instances after disposal
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            });

            saveBtn.addEventListener('click', async () => {
                saveBtn.disabled = true;
                saveBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

                const payload = {
                    first_name: document.getElementById('clientFirstName').value,
                    last_name: document.getElementById('clientLastName').value,
                    contact_no: document.getElementById('clientContactInput').value,
                    address: document.getElementById('clientAddressInput').value,
                };

                // Client-side sanitization and validation to avoid sending null/empty fields
                const sanitize = (s, max = 1000) => String(s || '').replace(/[\x00-\x1F\x7F<>]/g, '')
                    .slice(0, max).trim();
                const sanitizeContact = (s) => String(s || '').replace(/[^0-9+\-()\s]/g, '').slice(0,
                    40).trim();

                payload.first_name = sanitize(payload.first_name, 100);
                payload.last_name = sanitize(payload.last_name, 100);
                payload.address = sanitize(payload.address, 1000);
                payload.contact_no = sanitizeContact(payload.contact_no);

                // Basic validation
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
                    const clientId = '{{ $additionalQuotation->parentQuotation->client->id }}';
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
                        document.getElementById('clientName').textContent = data.client.first_name +
                            ' ' + data.client.last_name;
                        document.getElementById('clientContact').textContent = data.client.contact_no ||
                            '';
                        document.getElementById('clientAddress').textContent = data.client.address ||
                            '';

                        // Get fresh modal instance and hide it
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Client updated',
                            showConfirmButton: false,
                            timer: 1200
                        });
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
         * ✅ Global Toast helper for success messages
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

        /**
         * CRITICAL: Ensure modal-open class is reapplied when new modal opens
         * Bootstrap 5 removes modal-open when last modal closes, but we need it
         * if another modal is being opened immediately or is already open.
         */
        document.addEventListener('show.bs.modal', function(e) {
            // Ensure modal-open class is on body when any modal shows
            document.body.classList.add('modal-open');
        }, true);

        /**
         * Clean up modal-open class only when ALL modals are truly closed
         * Do NOT remove backdrops - Bootstrap manages them automatically
         */
        document.addEventListener('hidden.bs.modal', function(e) {
            // Debounce to ensure all modals have finished their hide transitions
            setTimeout(() => {
                const openModals = document.querySelectorAll('.modal.show');

                // Only remove modal-open class if NO modals are open
                if (openModals.length === 0) {
                    document.body.classList.remove('modal-open');
                }
            }, 200);
        }, true);
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const generateLinkBtn = document.getElementById('generateLinkBtn');
            if (generateLinkBtn) {
                generateLinkBtn.addEventListener('click', async function() {
                    generateLinkBtn.disabled = true;
                    generateLinkBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>Generating...';
                    try {
                        // Call API endpoint to generate/get token
                        const res = await fetch(`/additional-quotations/{{ $additionalQuotation->id }}/generate-token`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            }
                        });

                        if (!res.ok) {
                            throw new Error(`HTTP error! status: ${res.status}`);
                        }

                        const data = await res.json();
                        
                        if (data.public_link) {
                            await navigator.clipboard.writeText(data.public_link);
                            Swal.fire({
                                title: 'Link Copied!',
                                text: data.public_link,
                                icon: 'success',
                                timer: 1200,
                                showConfirmButton: false
                            });
                        } else {
                            throw new Error('No public_link in response');
                        }
                    } catch (err) {
                        console.error(err);
                        Swal.fire('Error', 'Could not generate or copy the link.', 'error');
                    }
                    generateLinkBtn.disabled = false;
                    generateLinkBtn.innerHTML = '<i class="fa-solid fa-link me-1"></i> Generate Link';
                });
            }
        });
    </script>
    <script>
        // Ensure price inputs get formatting behavior on initial load
        document.addEventListener('DOMContentLoaded', function() {
            try { bindPriceInputs(); } catch (e) { console.error('bindPriceInputs error', e); }
        });
    </script>
@endsection --}}
