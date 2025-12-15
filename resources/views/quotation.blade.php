@extends('layouts.app')
@include('include.head')

@section('content')
    @php
        // Detect quotation type: Regular Quotation vs Additional Quotation
        $isAdditional = isset($additionalQuotation) && !isset($quotation);
        $quotation = $isAdditional ? $additionalQuotation : $quotation ?? null;
        $quotationType = $isAdditional ? 'additional' : 'regular';

        // Get client info based on quotation type
        if ($isAdditional) {
            $client = $quotation->parentQuotation->client;
            $parentQuotationId = $quotation->parent_quotation_id;
            $quotationId = $quotation->id;
        } else {
            $client = $quotation->client;
            $parentQuotationId = null;
            $quotationId = $quotation->id;
        }
    @endphp

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

                        if ($isAdditional) {
                            // Additional Quotation header logic
                            if ($qStatus === 'completed') {
                                $headerText = 'Additional Quotation - Completed';
                                $headerClass = 'text-success';
                            } elseif ($qStatus === 'rejected') {
                                $headerText = 'Additional Quotation - Rejected';
                                $headerClass = 'text-danger';
                            } elseif ($quotation->progress >= 100) {
                                $headerText = 'Additional Quotation - Approved & Attached';
                                $headerClass = 'text-success';
                            } else {
                                $headerText = 'Creating Additional Quotation';
                                $headerClass = 'text-dark';
                            }
                        } else {
                            // Regular Quotation header logic
                            if ($qStatus === 'completed') {
                                $headerText = 'Project Completed';
                                $headerClass = 'text-success';
                            } elseif ($qStatus === 'rejected') {
                                $headerText = 'Quotation Rejected';
                                $headerClass = 'text-danger';
                            } elseif ($quotation->provider_approved) {
                                $headerText = 'Ongoing Project';
                                $headerClass = 'text-primary';
                            } else {
                                $headerText = 'Creating Quotation';
                                $headerClass = 'text-dark';
                            }
                        }
                    @endphp
                    <h1 class="h3 mb-0 {{ $headerClass }}">{{ $headerText }}</h1>
                </div>
            </div>

            <!-- Quotation Info -->
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="mb-3">{{ $quotation->subject }}</h3>

                    @if ($isAdditional)
                        <p><strong>Parent Quotation:</strong> {{ $quotation->parentQuotation->subject }}
                            (ID: {{ $quotation->parent_quotation_id }})
                        </p>
                    @endif

                    <p><strong>Customer:</strong> <span id="clientName">{{ $client->first_name }}
                            {{ $client->last_name }}</span>
                        @php
                            $qStatus = strtolower($quotation->status->status_name ?? '');
                            if ($isAdditional) {
                                $canEditClient = !(
                                    $qStatus === 'completed' ||
                                    $qStatus === 'rejected' ||
                                    ($quotation->progress >= 100 && $quotation->customer_approved)
                                );
                            } else {
                                $canEditClient = !(
                                    $qStatus === 'completed' ||
                                    $qStatus === 'rejected' ||
                                    $qStatus === 'ongoing'
                                );
                            }

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
                    <p><strong>Contact:</strong> <span id="clientContact">{{ $client->contact_no }}</span></p>
                    <p><strong>Address:</strong> <span id="clientAddress">{{ $client->address }}</span></p>
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
                            } elseif ($quotation->progress >= 100) {
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
                        </p>
                    </div>

                    @if (!$isAdditional)
                        @php
                            // Show contract details only for regular quotations and only if approved
                            $isApproved = $quotation->status_id >= 2; // 2 = approved
                        @endphp

                        @if ($isApproved && $quotation->contract_subject)
                            <div class="mt-4 pt-3 border-top">
                                <h5 class="mb-3">Contract Details</h5>
                                <p><strong>Contract Subject:</strong> <span>{{ $quotation->contract_subject }}</span></p>
                                
                                <!-- ✅ NEW: Show rush project badge -->
                                @if ($quotation->is_rush_project)
                                    <p>
                                        <strong>Project Type:</strong>
                                        <span class="badge bg-warning text-dark">
                                            <i class="fa-solid fa-bolt me-1"></i> Rush Project (No Contract Dates)
                                        </span>
                                    </p>
                                @else
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
                                @endif
                                
                                <p>
                                    <strong>Contract Status:</strong>
                                    @if ($quotation->is_rush_project)
                                        <span class="badge bg-warning text-dark">
                                            <i class="fa-solid fa-bolt me-1"></i> Rush / No Contract
                                        </span>
                                    @elseif ($quotation->with_contract)
                                        <span><i class="fa-solid fa-circle text-success me-2" style="font-size: 0.5rem;"></i>With Contract</span>
                                    @else
                                        <span><i class="fa-solid fa-circle text-secondary me-2" style="font-size: 0.5rem;"></i>Without Contract</span>
                                    @endif
                                </p>
                            </div>
                        @endif
                    @endif
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
                                        $qStatus = strtolower($quotation->status->status_name ?? '');

                                        // For additional quotations, check if customer_approved or status >= 2 (approved)
                                        $isApprovedAdditional =
                                            $isAdditional &&
                                            ($quotation->customer_approved || $quotation->status_id >= 2);

                                        $canAddMaterial =
                                            empty($readonly) &&
                                            $qStatus !== 'completed' &&
                                            $qStatus !== 'rejected' &&
                                            !($quotation->service_approved && $qStatus === 'ongoing') &&
                                            !$isApprovedAdditional;
                                        $canCreateRevision =
                                            empty($readonly) &&
                                            Auth::user()->can('create_revision') &&
                                            $qStatus !== 'completed' &&
                                            $qStatus !== 'rejected' &&
                                            !($quotation->service_approved && $qStatus === 'ongoing') &&
                                            !$isApprovedAdditional;
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

                                    @if (!$isAdditional)
                                        <button type="button" class="btn btn-sm btn-outline-info" id="viewAdditionalQtnBtn"
                                            data-parent-id="{{ $quotation->id }}">
                                            <i class="fa-solid fa-list me-1"></i>
                                            <span class="d-none d-sm-inline">Additional Quotations</span>
                                            <span class="d-sm-none">Additional</span>
                                        </button>
                                    @endif

                                    @if (Auth::user()->can('view_revision_history'))
                                        <button type="button" class="btn btn-sm btn-outline-info" id="viewRevisionsBtn"
                                            data-id="{{ $quotation->id }}">
                                            <i class="fa-solid fa-clock-rotate-left me-1"></i>
                                            <span class="d-none d-sm-inline">View Revisions</span>
                                            <span class="d-sm-none">Revisions</span>
                                        </button>
                                    @endif

                                    @if ($canCreateRevision)
                                        <button type="button" class="btn btn-sm btn-outline-warning" id="createRevisionBtn"
                                            data-id="{{ $quotation->id }}">
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
                                            @if (empty($readonly) && !$isApprovedAdditional)
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
                                                @if ($qStatus === 'draft' && empty($readonly) && !$isApprovedAdditional)
                                                    <input type="text" class="form-control update-price text-end"
                                                        data-pivot="{{ $mat->pivot->id }}"
                                                        data-material="{{ $mat->id }}"
                                                        value="{{ number_format($mat->pivot->unit_cost ?? $mat->unit_price, 2) }}"
                                                        style="width: 100px; display:inline-block;">
                                                @else
                                                    ₱{{ number_format($mat->pivot->unit_cost, 2) }}
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">Hidden</span>
                                            @endif
                                        </td>
                                        <td class="line-total text-end">
                                            @if (Auth::user()->can('view_prices'))
                                                ₱{{ number_format(($mat->pivot->unit_cost ?? $mat->unit_price) * $mat->pivot->quantity, 2) }}
                                            @else
                                                <span class="badge bg-secondary">Hidden</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $qStatus = strtolower($quotation->status->status_name ?? '');
                                                $canDeleteMaterial =
                                                    empty($readonly) &&
                                                    $qStatus !== 'completed' &&
                                                    $qStatus !== 'rejected' &&
                                                    !($quotation->service_approved && $qStatus === 'ongoing') &&
                                                    !$isApprovedAdditional;
                                            @endphp
                                            @if ($canDeleteMaterial)
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
                                    @php
                                        $qStatus = strtolower($quotation->status->status_name ?? '');
                                        $canEditFees =
                                            empty($readonly) &&
                                            $qStatus !== 'completed' &&
                                            $qStatus !== 'rejected' &&
                                            !($quotation->service_approved && $qStatus === 'ongoing') &&
                                            !$isApprovedAdditional;
                                    @endphp
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Total Material Cost:</td>
                                        <td class="text-end fw-bold" id="totalMaterialCost">
                                            ₱{{ number_format($materials->sum(fn($m) => ($m->pivot->unit_cost ?? $m->unit_price) * $m->pivot->quantity), 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Labor Fee:</td>
                                        <td>
                                            @if ($canEditFees)
                                                <div class="input-group">
                                                    <span class="input-group-text">₱</span>
                                                    <input type="text"
                                                        class="form-control text-end fee-input labor-fee-display"
                                                        id="laborFee" placeholder="0.00" data-field="labor_fee"
                                                        data-validate="price" value="{{ $quotation->labor_fee }}"
                                                        step="0.01" min="0" style="font-family: inherit;">
                                                </div>
                                            @else
                                                <span>₱{{ number_format($quotation->labor_fee, 2) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Delivery Fee:</td>
                                        <td>
                                            @if ($canEditFees)
                                                <div class="input-group">
                                                    <span class="input-group-text">₱</span>
                                                    <input type="text"
                                                        class="form-control text-end fee-input delivery-fee-display"
                                                        id="deliveryFee" placeholder="0.00" data-field="delivery_fee"
                                                        data-validate="price" value="{{ $quotation->delivery_fee }}"
                                                        step="0.01" min="0" style="font-family: inherit;">
                                                </div>
                                            @else
                                                <span>₱{{ number_format($quotation->delivery_fee, 2) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                                    <td class="fw-bold text-primary fs-5 text-end" id="grandTotal">
                                        @if (Auth::user()->can('view_prices'))
                                            <span id="grandTotalValue" class="grand-total-display">
                                                ₱<span
                                                    id="grandTotalAmount">{{ number_format($materials->sum(fn($m) => ($m->pivot->unit_cost ?? $m->unit_price) * $m->pivot->quantity) + $quotation->labor_fee + $quotation->delivery_fee, 2) }}</span>
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Hidden</span>
                                        @endif
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif


            @php
                $qStatus = strtolower($quotation->status->status_name ?? '');
                $isCompleted = $qStatus === 'completed';
                $isRejected = $qStatus === 'rejected';
                $isOngoing = $quotation->service_approved && $qStatus === 'ongoing';

                // Show primary action buttons only if not readonly and not in completed/ongoing state
                // For additional quotations, also hide if customer_approved or status >= 2 (approved)
                $showPrimaryActions = empty($readonly) && !$isCompleted && !$isOngoing && !$isApprovedAdditional;

                // Show export button for all states (except readonly)
                $showExport = empty($readonly) || $isCompleted || $isRejected || $isOngoing;
            @endphp

            @if ($showPrimaryActions)
                <!-- Primary Actions: Approve, Save Draft, Reject, Export -->
                <div class="row mt-3">
                    <div class="col-12 d-flex flex-wrap gap-2 justify-content-end">
                        @if (!$isRejected && !$isStaff)
                            @if ($isAdditional)
                                {{-- Additional quotations: No modal toggle, handled by JavaScript --}}
                                <button type="button" class="btn btn-success" id="approveBtn"
                                    data-quot="{{ $quotation->id }}" @if (!$quotation->customer_approved) disabled @endif>
                                    <i class="fa-solid fa-check-circle me-1"></i> Approve
                                </button>
                            @else
                                {{-- Regular quotations: Show contract form modal --}}
                                <button type="button" class="btn btn-success" id="approveBtn" data-bs-toggle="modal"
                                    data-bs-target="#approveModal" data-quot="{{ $quotation->id }}"
                                    @if (!$quotation->customer_approved) disabled @endif>
                                    <i class="fa-solid fa-check-circle me-1"></i> Approve
                                </button>
                            @endif
                        @endif

                        <button type="button" class="btn btn-primary" id="saveDraftBtn"
                            data-quot="{{ $quotation->id }}">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save Draft
                        </button>

                        @if (!$isRejected && !$isStaff)
                            <button type="button" class="btn btn-danger" id="rejectBtn"
                                data-quot="{{ $quotation->id }}">
                                <i class="fa-solid fa-ban me-1"></i> Reject
                            </button>
                        @endif

                        @if (!$isStaff)
                            @if ($isAdditional)
                                <a href="{{ route('additional-quotations.export', ['id' => $quotation->id]) }}"
                                    class="btn btn-info d-flex align-items-center">
                                    <i class="fa-solid fa-file-word me-1"></i> Export to DOC
                                </a>
                            @else
                                <a href="{{ route('quotations.export', ['id' => $quotation->id]) }}"
                                    class="btn btn-info d-flex align-items-center">
                                    <i class="fa-solid fa-file-word me-1"></i> Export to DOC
                                </a>
                            @endif
                        @endif
                    </div>
                </div>
            @elseif ($showExport && !$isStaff)
                <!-- Limited Actions: Export only (for completed, rejected, or ongoing projects) -->
                <div class="row mt-3">
                    <div class="col-12 d-flex flex-wrap gap-2 justify-content-end">
                        @if ($isAdditional)
                            <a href="{{ route('additional-quotations.export', ['id' => $quotation->id]) }}"
                                class="btn btn-info d-flex align-items-center">
                                <i class="fa-solid fa-file-word me-1"></i> Export to DOC
                            </a>
                        @else
                            <a href="{{ route('quotations.export', ['id' => $quotation->id]) }}"
                                class="btn btn-info d-flex align-items-center">
                                <i class="fa-solid fa-file-word me-1"></i> Export to DOC
                            </a>
                        @endif
                    </div>
                </div>
            @endif




            {{-- 💬 Threaded Comments Section (admin/staff) --}}
            @include('components.threaded-comments-admin', [
                'comments' => $quotation->comments,
                'quotationId' => $quotation->id,
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

                                <div class="alert alert-info d-flex align-items-start" role="alert">
                                    <i class="fa-solid fa-info-circle me-2 mt-1"></i>
                                    <div>
                                        <strong>Note:</strong> Basic fields are required to approve this quotation.
                                        For rush projects, you can skip contract dates.
                                    </div>
                                </div>

                                <div id="contractSubjectContainer" class="mb-3">
                                    <label for="contractSubject" class="form-label">
                                        Contract Subject <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="contractSubject"
                                        name="contract_subject" placeholder="Auto-filled from quotation subject" required>
                                    <small class="form-text text-muted">
                                        <i class="fa-solid fa-lightbulb me-1"></i> 
                                        This is auto-filled with the quotation subject. Edit if needed.
                                    </small>
                                </div>

                                <!-- ✅ NEW: Rush Project Checkbox -->
                                <div class="border border-warning border-2 rounded p-3 mb-3">
                                    <div class="form-check d-flex align-items-center">
                                        <input class="form-check-input me-2" type="checkbox" id="isRushProject"
                                            name="is_rush_project" value="1">
                                        <label class="form-check-label" for="isRushProject">
                                            <strong>This is a rush project</strong>
                                            <small class="d-block text-muted">(Skip all contract details)</small>
                                        </label>
                                    </div>
                                </div>

                                <!-- ✅ UPDATED: Date fields shown conditionally based on rush project -->
                                <div id="dateFieldsContainer">
                                    <div class="mb-3">
                                        <label for="projectStartDate" class="form-label">
                                            Project Start Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control" id="projectStartDate"
                                            name="project_start_date">
                                        <small class="form-text text-muted">
                                            You can set the start date to today or earlier (backtrack up to 3 days).
                                        </small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="projectEndDate" class="form-label">
                                            Project End Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control" id="projectEndDate"
                                            name="project_end_date">
                                    </div>
                                </div>

                                <!-- ✅ UPDATED: Contract confirmation hidden for rush projects -->
                                <div id="contractConfirmationContainer" class="border border-primary border-2 rounded p-3">
                                    <div class="form-check d-flex align-items-center">
                                        <input class="form-check-input me-2" type="checkbox" id="withContract"
                                            name="with_contract" value="1">
                                        <label class="form-check-label" for="withContract">
                                            <strong>I confirm this quotation is backed by a valid contract</strong>
                                        </label>
                                    </div>
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

                <!-- ✅ NEW: Reject Quotation Modal 
                -->
            <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="rejectModalLabel">Reject Quotation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="rejectForm">
                            <div class="modal-body">
                                <div class="alert alert-warning" role="alert">
                                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                                    <strong>Warning:</strong> Rejecting a quotation will mark it as rejected. You must provide a
                                    reason.
                                </div>

                                <div class="mb-3">
                                    <label for="rejectionReason" class="form-label">
                                        Rejection Reason <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" id="rejectionReason" name="rejection_reason" rows="3"
                                        placeholder="Enter your rejection reason..." required></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Quick Select Reason</label>
                                    <div class="btn-group d-flex flex-wrap gap-2" role="group" style="gap: 0.5rem !important;">
                                        <button type="button" class="btn btn-outline-danger btn-sm common-reason-btn"
                                            data-reason="Client budget exceeded - quotation too expensive">
                                            <i class="fa-solid fa-money-bill me-1"></i> Budget Issue
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm common-reason-btn"
                                            data-reason="Client decided to go with another vendor">
                                            <i class="fa-solid fa-building me-1"></i> Other Vendor
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm common-reason-btn"
                                            data-reason="Materials not available or out of stock">
                                            <i class="fa-solid fa-box me-1"></i> No Stock
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm common-reason-btn"
                                            data-reason="Timeline does not meet client requirements">
                                            <i class="fa-solid fa-calendar me-1"></i> Timeline Issue
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm common-reason-btn"
                                            data-reason="Client canceled the project">
                                            <i class="fa-solid fa-ban me-1"></i> Project Canceled
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm common-reason-btn"
                                            data-reason="Specifications do not match client requirements">
                                            <i class="fa-solid fa-list-check me-1"></i> Specs Mismatch
                                        </button>
                                    </div>
                                </div>

                                <div class="alert alert-info small" role="alert">
                                    <i class="fa-solid fa-info-circle me-2"></i>
                                    Click any quick reason button above, or type your own reason in the text area above.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">
                                    <i class="fa-solid fa-times-circle me-1"></i> Reject Quotation
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
                    console.log('🔄 updateGrandTotalDisplay called with amount:', amount);
                    // Prefer updating the inner amount span if present so we don't strip
                    // surrounding markup / classes. Fall back to replacing the cell text.
                    const grandTotalAmountEl = document.getElementById('grandTotalAmount');
                    console.log('🔍 Found grandTotalAmount element:', grandTotalAmountEl);
                    if (grandTotalAmountEl) {
                        const formattedAmount = formatNumberWithCommas(amount);
                        console.log('✨ Setting grandTotalAmount text to:', formattedAmount);
                        grandTotalAmountEl.textContent = formattedAmount;
                        // Ensure parent shows currency prefix if needed
                        const parent = grandTotalAmountEl.closest('#grandTotal');
                        if (parent) {
                            // parent may already contain the currency symbol in markup; keep it.
                            // if not, ensure the visible text is correct by leaving structure intact.
                        }
                        return;
                    }

                    const grandTotalEl = document.getElementById("grandTotal");
                    console.log('🔍 Found grandTotal element:', grandTotalEl);
                    if (grandTotalEl) {
                        const formattedAmount = "₱" + formatNumberWithCommas(amount);
                        console.log('✨ Setting grandTotal text to:', formattedAmount);
                        grandTotalEl.textContent = formattedAmount;
                    }
                }

                /**
                 * Update total material cost (sum of all line totals)
                 * This is separate from grand total which includes fees
                 */
                function updateTotalMaterialCost() {
                    const totalMaterialEl = document.getElementById('totalMaterialCost');
                    if (!totalMaterialEl) return;

                    let materialsTotal = 0;
                    document.querySelectorAll('.line-total').forEach(el => {
                        try {
                            const txt = el.textContent || '';
                            const raw = txt.replace(/[^0-9.\-]/g, '');
                            const v = parseFloat(raw);
                            if (!isNaN(v)) materialsTotal += v;
                        } catch (e) {}
                    });

                    console.log('📦 Updating Total Material Cost to:', materialsTotal);
                    totalMaterialEl.textContent = '₱' + formatNumberWithCommas(materialsTotal);
                }

                /**
                 * Compute grand total on the client by summing all visible line totals
                 * and fee inputs. Useful when server responses don't include grand_total.
                 * Handles both editable inputs (.fee-input) and display-only spans (when disabled).
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
                        } catch (e) {
                            /* ignore malformed cells */ }
                    });

                    let feesTotal = 0;
                    // Try to read from editable fee inputs first
                    document.querySelectorAll('.fee-input').forEach(inp => {
                        try {
                            const val = inp.value || '0';
                            const raw = val.toString().replace(/,/g, '').replace(/[^0-9.\-]/g, '');
                            const v = parseFloat(raw);
                            if (!isNaN(v)) feesTotal += v;
                        } catch (e) {
                            /* ignore malformed inputs */ }
                    });

                    // If no fee inputs found (disabled state), read from labor and delivery fee display spans
                    if (feesTotal === 0) {
                        // Labor fee: look for the text after "Labor Fee:" label
                        const laborFeeSpans = document.querySelectorAll('tfoot tr');
                        laborFeeSpans.forEach(tr => {
                            const tdText = tr.querySelector('td:first-child')?.textContent || '';
                            if (tdText.includes('Labor Fee')) {
                                const priceSpan = tr.querySelector('td:nth-child(4) span');
                                if (priceSpan) {
                                    const raw = priceSpan.textContent.replace(/[^0-9.\-]/g, '');
                                    const v = parseFloat(raw);
                                    if (!isNaN(v)) feesTotal += v;
                                }
                            }
                        });

                        // Delivery fee: look for the text after "Delivery/Hauling Fee:" label
                        laborFeeSpans.forEach(tr => {
                            const tdText = tr.querySelector('td:first-child')?.textContent || '';
                            if (tdText.includes('Delivery') || tdText.includes('Hauling')) {
                                const priceSpan = tr.querySelector('td:nth-child(4) span');
                                if (priceSpan) {
                                    const raw = priceSpan.textContent.replace(/[^0-9.\-]/g, '');
                                    const v = parseFloat(raw);
                                    if (!isNaN(v)) feesTotal += v;
                                }
                            }
                        });
                    }

                    const grand = materialsTotal + feesTotal;
                    updateGrandTotalDisplay(grand);
                    return grand;
                }

                /**
                 * Bind formatting behavior to editable price inputs (.update-price)
                 * - on focus: remove commas so user can type raw number
                 * - on blur: format with commas
                 */
                function bindPriceInputs(quotationType = 'regular') {
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
                                    try {
                                        this.value = formatNumberWithCommas(this.value || 0);
                                    } catch (e) {}
                                    return;
                                }

                                this.disabled = true;
                                this.classList.add('is-loading');

                                try {
                                    const endpoint = quotationType === 'additional' ?
                                        `/additional-quotation-materials/${pivotId}/update-price` :
                                        `/quotation-materials/${pivotId}/update-unit-cost`;

                                    const pivotRes = await fetch(endpoint, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector(
                                                'meta[name="csrf-token"]').content,
                                            'Accept': 'application/json'
                                        },
                                        body: JSON.stringify({
                                            unit_cost: newPrice
                                        })
                                    });

                                    let pivotData;
                                    try {
                                        if (pivotRes.headers.get('content-type')?.includes(
                                            'application/json')) {
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
                                            const quantity = parseFloat(row.querySelector('.update-quantity')
                                                ?.value || 0);
                                            const lineTotal = newPrice * (isNaN(quantity) ? 0 : quantity);
                                            const lineEl = row.querySelector('.line-total');
                                            if (lineEl) lineEl.textContent =
                                                `₱${formatNumberWithCommas(lineTotal)}`;
                                        }

                                        // Update grand total (use central updater). If server didn't
                                        // include grand_total, compute it from the DOM as a fallback.
                                        if (pivotData.grand_total !== undefined) {
                                            updateGrandTotalDisplay(pivotData.grand_total);
                                        } else {
                                            computeAndUpdateGrandTotal();
                                        }

                                        // ✅ NEW: Also update the Total Material Cost
                                        updateTotalMaterialCost();

                                        // Format and show the new price in the input
                                        try {
                                            this.value = formatNumberWithCommas(newPrice);
                                        } catch (e) {}
                                        Toast('Price updated!');
                                    } else {
                                        console.error('Pivot update failed:', {
                                            pivotRes,
                                            pivotData
                                        });
                                        Swal.fire('Error', 'Failed to update price.', 'error');
                                    }
                                } catch (err) {
                                    console.error('Unexpected error:', err);
                                    Swal.fire('Error', 'Something went wrong: ' + (err.message || err),
                                    'error');
                                }

                                this.disabled = false;
                                this.classList.remove('is-loading');
                            });
                        }
                    });
                }

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
            <td>₱${formatNumberWithCommas(mat.unit_price)}</td>
            <td class="line-total text-end">₱${formatNumberWithCommas(mat.line_total)}</td>
            <td class="text-center">
                <a href="#" class="text-danger delete-material" data-id="${mat.pivot_id}" data-quot="{{ $quotation->id }}">
                    <i class="fa-solid fa-trash"></i>
                </a>
            </td>
        </tr>`;
                        tbody.insertAdjacentHTML("beforeend", row);
                    });

                    // Rebind events for new quantity inputs and delete links
                    new QuantityUpdater(".update-quantity", "{{ $quotationType ?? 'regular' }}");
                    new DeleteMaterialFromQuotation(".delete-material", "{{ $quotationType ?? 'regular' }}");
                    // Bind price input formatting for new rows
                    bindPriceInputs("{{ $quotationType ?? 'regular' }}");
                    // Ensure grand total reflects newly appended materials
                    computeAndUpdateGrandTotal();
                }
            </script>


            <!-- Delete Material from Quotation -->
            <script>
                class DeleteMaterialFromQuotation {
                    constructor(selector, quotationType = 'regular') {
                        this.selector = selector;
                        this.quotationType = quotationType;
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
                            const endpoint = this.quotationType === 'additional' ?
                                `/additional-quotation-materials/${pivotId}` :
                                `/quotation-materials/${pivotId}`;

                            const res = await fetch(endpoint, {
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
                new DeleteMaterialFromQuotation(".delete-material", "{{ $quotationType ?? 'regular' }}");
            </script>

            <script>
                class QuantityUpdater {
                    constructor(selector, quotationType = 'regular') {
                        this.selector = selector;
                        this.quotationType = quotationType;
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
                            const endpoint = this.quotationType === 'additional' ?
                                `/additional-quotation-materials/${pivotId}/update-quantity` :
                                `/quotation-materials/update-quantity`;

                            const res = await fetch(endpoint, {
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
                                        lineTotal.textContent = `₱${formatNumberWithCommas(data.line_total)}`;
                                    }
                                }

                                // Update grand total
                                console.log('📊 Quantity Update - Grand Total from server:', data.grand_total);
                                if (data.grand_total !== undefined) {
                                    console.log('✅ Updating grand total display to:', data.grand_total);
                                    updateGrandTotalDisplay(data.grand_total);
                                } else {
                                    console.log('⚠️ No grand_total in response, computing from page');
                                    computeAndUpdateGrandTotal();
                                }

                                // ✅ NEW: Also update the Total Material Cost
                                updateTotalMaterialCost();

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
                new QuantityUpdater(".update-quantity", "{{ $quotationType ?? 'regular' }}");
            </script>



            <!-- Update Fees -->
            <script>
                class FeeUpdater {
                    constructor(selector, quotationId, csrfToken, quotationType = 'regular') {
                        this.selector = selector;
                        this.quotationId = quotationId;
                        this.csrfToken = csrfToken;
                        this.quotationType = quotationType;
                        this.debounceTimers = new Map(); // Per-input debounce timer
                        this.inputStates = new Map(); // Track current value per input
                        this.initialized = false;

                        // Initialize all fee inputs
                        this.initializeInputs();
                    }

                    initializeInputs() {
                        document.querySelectorAll(this.selector).forEach(input => {
                            // Skip if already initialized
                            if (input.dataset.feeInitialized === 'true') {
                                // Just restore state tracking for this input
                                this.inputStates.set(input, input.value);
                                return;
                            }

                            // Mark as initialized
                            input.dataset.feeInitialized = 'true';

                            // Store initial value
                            this.inputStates.set(input, input.value);

                            // Utility: sanitize a fee input string (allow digits, commas, one dot)
                            const sanitizeValue = (val) => {
                                if (val === null || val === undefined) return '';
                                // Remove all characters except digits, dot and comma
                                let s = val.toString();
                                s = s.replace(/[^0-9.,]/g, '');
                                // Ensure only one dot
                                const parts = s.split('.');
                                if (parts.length > 1) {
                                    s = parts.shift() + '.' + parts.join(''); // keep first dot only
                                }
                                // Prevent leading negative signs entirely (strip '-')
                                s = s.replace(/-/g, '');
                                return s;
                            };

                            // Keydown: prevent characters that are not digits, control keys, dot, comma
                            input.addEventListener('keydown', (e) => {
                                // Allow control/navigation keys
                                const allowedKeys = [
                                    'Backspace', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
                                    'Delete', 'Tab', 'Enter', 'Home', 'End'
                                ];
                                if (allowedKeys.includes(e.key)) return;

                                // Allow Ctrl/Cmd combinations (copy/paste/select all)
                                if (e.ctrlKey || e.metaKey) return;

                                // Allow digits, period and comma only
                                const isDigit = /[0-9]/.test(e.key);
                                const isDot = e.key === '.';
                                const isComma = e.key === ',';
                                if (isDigit || isDot || isComma) {
                                    return; // allow
                                }

                                // Otherwise prevent key
                                e.preventDefault();
                            });

                            // Paste: sanitize pasted content
                            input.addEventListener('paste', (e) => {
                                e.preventDefault();
                                const text = (e.clipboardData || window.clipboardData).getData('text');
                                const sanitized = sanitizeValue(text);
                                // Insert at cursor position if needed; simple replacement for now
                                input.value = sanitized;
                                this.inputStates.set(input, sanitized);
                                // Trigger update manually (debounced)
                                this.debounceUpdate({
                                    target: input
                                });
                            });

                            // Add focus event listener - clear "0.00" display
                            input.addEventListener("focus", (e) => {
                                const currentVal = e.target.value || '';
                                if (currentVal === "0.00" || currentVal === "0") {
                                    e.target.value = "";
                                }
                            });

                            // Add blur event listener - format and restore if needed
                            input.addEventListener("blur", (e) => {
                                const currentVal = e.target.value || '';

                                // If empty or 0, set to "0.00"
                                if (currentVal === "" || currentVal === "0") {
                                    e.target.value = "0.00";
                                    this.inputStates.set(input, "0.00");
                                } else {
                                    // Format with commas
                                    try {
                                        const numVal = parseFloat(currentVal.toString().replace(/,/g, ''));
                                        if (!isNaN(numVal)) {
                                            const formatted = formatNumberWithCommas(numVal);
                                            e.target.value = formatted;
                                            this.inputStates.set(input, formatted);
                                        } else {
                                            // If not a number after sanitization, restore last good
                                            e.target.value = this.inputStates.get(input) || "0.00";
                                        }
                                    } catch (err) {
                                        console.error('Format error on blur:', err);
                                    }
                                }
                            });

                            // Add input event listener - sanitize and trigger update
                            input.addEventListener("input", (e) => {
                                const raw = e.target.value || '';
                                const sanitized = sanitizeValue(raw);

                                // If sanitized differs, update the input value but try to preserve UX
                                if (sanitized !== raw) {
                                    // Replace the value with sanitized version
                                    const cursorPos = e.target.selectionStart;
                                    e.target.value = sanitized;
                                    try {
                                        e.target.setSelectionRange(cursorPos - 1, cursorPos - 1);
                                    } catch (err) {
                                        // ignore setSelection errors
                                    }
                                }

                                // Don't allow leading zeros-only like '-' or empty to be treated as number
                                this.inputStates.set(input, e.target.value);

                                // Debounce the server update; pass a synthetic event-like object
                                this.debounceUpdate({
                                    target: input
                                });
                            });
                        });

                        this.initialized = true;
                    }

                    // ✅ NEW: Restore state tracking without re-attaching event listeners
                    restoreStateTracking() {
                        document.querySelectorAll(this.selector).forEach(input => {
                            // Just restore the current value to state tracking
                            // Event listeners are already attached, don't re-attach
                            if (!this.inputStates.has(input)) {
                                this.inputStates.set(input, input.value);
                            }
                        });
                    }

                    debounceUpdate(e) {
                        const input = e.target;

                        // Clear previous timer for this input
                        if (this.debounceTimers.has(input)) {
                            clearTimeout(this.debounceTimers.get(input));
                        }

                        // Set new debounced timer
                        const timer = setTimeout(() => {
                            this.updateFee(input);
                        }, 800); // Increased to 800ms for better UX

                        this.debounceTimers.set(input, timer);
                    }

                    async updateFee(input) {
                        // Get the raw value (without commas, as user typed it)
                        const rawValue = (input.value || '').toString().replace(/,/g, '').trim();
                        const field = input.dataset.field;

                        // Validate the value
                        const numValue = parseFloat(rawValue);
                        if (isNaN(numValue) || numValue < 0) {
                            console.warn('Invalid fee value:', rawValue);
                            // Restore to previous valid value
                            input.value = this.inputStates.get(input) || "0.00";
                            return;
                        }

                        console.log('Fee Update Initiated:', {
                            field,
                            rawValue,
                            numValue,
                            quotationId: this.quotationId
                        });

                        try {
                            const endpoint = this.quotationType === 'additional' ?
                                `/additional-quotations/${this.quotationId}/update-fee` :
                                `/quotations/${this.quotationId}/update-fee`;

                            const res = await fetch(endpoint, {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": this.csrfToken,
                                    "Accept": "application/json"
                                },
                                body: JSON.stringify({
                                    field,
                                    value: numValue // Send numeric value to server
                                })
                            });

                            const data = await res.json();

                            if (res.ok && data.success) {
                                console.log('Fee Update Success:', {
                                    field,
                                    newGrandTotal: data.grand_total
                                });

                                // Store the formatted value back
                                const formatted = formatNumberWithCommas(numValue);
                                input.value = formatted;
                                this.inputStates.set(input, formatted);

                                // Update grand total UI
                                if (data.grand_total !== undefined) {
                                    updateGrandTotalDisplay(data.grand_total);
                                } else {
                                    computeAndUpdateGrandTotal();
                                }

                                // Show success toast
                                if (window.Toast && typeof window.Toast === 'function') {
                                    Toast('Fee updated!');
                                }
                            } else {
                                console.error('Fee Update Failed:', data);
                                // Restore to last known good value
                                const lastGoodValue = this.inputStates.get(input) || "0.00";
                                input.value = lastGoodValue;
                                Swal.fire("Error", data.message || "Failed to update fee", "error");
                            }
                        } catch (error) {
                            console.error("Fee Update Error:", error);
                            // Restore to last known good value
                            const lastGoodValue = this.inputStates.get(input) || "0.00";
                            input.value = lastGoodValue;
                            Swal.fire("Error", "Network error: " + error.message, "error");
                        }
                    }
                }

                // ✅ Initialize with quotationId and CSRF
                if (!window.feeUpdater) {
                    window.feeUpdater = new FeeUpdater(
                        ".fee-input",
                        "{{ $quotationId ?? '' }}",
                        "{{ csrf_token() }}",
                        "{{ $quotationType ?? 'regular' }}"
                    );
                }
            </script>




            <!-- Quotation Status Buttons -->
            <script>
                class QuotationStatusHandler {
                    constructor(quotationType = 'regular') {
                        this.quotationType = quotationType;
                        this.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
                        this.bindEvents();
                        this.bindApproveForm();
                        this.bindRejectForm();
                    }

                    bindEvents() {
                        // Handle Approve button - different behavior for regular vs additional quotations
                        const approveBtn = document.getElementById('approveBtn');
                        if (approveBtn) {
                            approveBtn.addEventListener('click', (e) => {
                                // If it's an additional quotation, skip the modal and directly approve
                                if (this.quotationType === 'additional') {
                                    e.preventDefault();
                                    const quotationId = approveBtn.dataset.quot;
                                    // Directly update status to 2 (approved) without contract form
                                    this.updateStatus(quotationId, 2);
                                }
                                // Otherwise, let the modal open (data-bs-toggle will handle it)
                            });
                        }

                        // Handle Save Draft button
                        const saveDraftBtn = document.getElementById('saveDraftBtn');
                        if (saveDraftBtn) {
                            saveDraftBtn.addEventListener("click", (e) => {
                                e.preventDefault();
                                const quotationId = saveDraftBtn.dataset.quot;
                                this.updateStatus(quotationId, 1); // 1 = Draft
                            });
                        }

                        // ✅ NEW: Handle Reject button - show modal instead of directly rejecting
                        const rejectBtn = document.getElementById('rejectBtn');
                        if (rejectBtn) {
                            rejectBtn.addEventListener("click", (e) => {
                                e.preventDefault();
                                const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
                                rejectModal.show();
                                // Store quotation ID for later use in form submission
                                document.getElementById('rejectModal').dataset.quotationId = rejectBtn.dataset.quot;
                            });
                        }
                    }

                    bindRejectForm() {
                        const rejectForm = document.getElementById('rejectForm');
                        if (!rejectForm) return;

                        // ✅ NEW: Handle common reason buttons
                        document.querySelectorAll('.common-reason-btn').forEach(btn => {
                            btn.addEventListener('click', (e) => {
                                e.preventDefault();
                                const reason = btn.dataset.reason;
                                document.getElementById('rejectionReason').value = reason;
                                // Highlight the clicked button
                                document.querySelectorAll('.common-reason-btn').forEach(b => b.classList.remove(
                                    'active'));
                                btn.classList.add('active');
                            });
                        });

                        // ✅ NEW: Handle reject form submission
                        rejectForm.addEventListener('submit', async (e) => {
                            e.preventDefault();

                            const quotationId = document.getElementById('rejectModal').dataset.quotationId;
                            const rejectionReason = document.getElementById('rejectionReason').value.trim();

                            // Validate reason
                            if (!rejectionReason) {
                                Swal.fire('Validation Error', 'Please provide a rejection reason.', 'warning');
                                return;
                            }

                            // Confirm rejection
                            const confirmed = await Swal.fire({
                                icon: 'warning',
                                title: 'Confirm Rejection',
                                text: 'Are you sure you want to reject this quotation? This action cannot be undone.',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: 'Yes, reject it',
                                cancelButtonText: 'Cancel'
                            });

                            if (!confirmed.isConfirmed) return;

                            // Reject with status ID 3
                            await this.updateStatus(quotationId, 3, {
                                rejection_reason: rejectionReason
                            });

                            // Close modal on success
                            const modal = bootstrap.Modal.getInstance(document.getElementById('rejectModal'));
                            if (modal) modal.hide();
                        });
                    }

                    bindApproveForm() {
                        const approveForm = document.getElementById('approveForm');
                        if (!approveForm) return;

                        // ✅ NEW: Auto-fill contract subject with quotation subject when modal opens
                        const approveBtn = document.getElementById('approveBtn');
                        if (approveBtn) {
                            approveBtn.addEventListener('click', () => {
                                const quotationSubject = document.querySelector('h3.mb-3')?.textContent?.trim() || 'Quotation';
                                document.getElementById('contractSubject').value = quotationSubject;
                            });
                        }

                        // ✅ NEW: Handle rush project checkbox - toggle date fields, contract subject, AND contract confirmation
                        const rushProjectCheckbox = document.getElementById('isRushProject');
                        const contractSubjectContainer = document.getElementById('contractSubjectContainer');
                        const dateFieldsContainer = document.getElementById('dateFieldsContainer');
                        const contractConfirmationContainer = document.getElementById('contractConfirmationContainer');
                        
                        if (rushProjectCheckbox) {
                            rushProjectCheckbox.addEventListener('change', (e) => {
                                const isRush = e.target.checked;
                                
                                // ✅ NEW: Hide/show contract subject for rush projects
                                if (contractSubjectContainer) {
                                    contractSubjectContainer.style.display = isRush ? 'none' : 'block';
                                    // Update required status on contract subject input
                                    document.getElementById('contractSubject').required = !isRush;
                                    // Auto-fill if hiding (we don't need to save it anyway)
                                    if (isRush) {
                                        document.getElementById('contractSubject').value = '';
                                    }
                                }
                                
                                // Hide/show date fields
                                if (dateFieldsContainer) {
                                    dateFieldsContainer.style.display = isRush ? 'none' : 'block';
                                    // Update required status on date inputs
                                    document.getElementById('projectStartDate').required = !isRush;
                                    document.getElementById('projectEndDate').required = !isRush;
                                }
                                
                                // ✅ NEW: Hide/show contract confirmation for rush projects
                                if (contractConfirmationContainer) {
                                    contractConfirmationContainer.style.display = isRush ? 'none' : 'block';
                                    // Update required status on contract checkbox
                                    document.getElementById('withContract').required = !isRush;
                                    // Auto-check if hiding (rush project doesn't need confirmation)
                                    if (isRush) {
                                        document.getElementById('withContract').checked = true;
                                    }
                                }
                            });
                        }

                        approveForm.addEventListener('submit', async (e) => {
                            e.preventDefault();

                            const quotationId = document.getElementById('approveBtn').dataset.quot;
                            const contractSubject = document.getElementById('contractSubject').value.trim();
                            const projectStartDate = document.getElementById('projectStartDate').value;
                            const projectEndDate = document.getElementById('projectEndDate').value;
                            const withContract = document.getElementById('withContract').checked;
                            const isRushProject = document.getElementById('isRushProject').checked;

                            // ✅ UPDATED: Contract checkbox is REQUIRED only if NOT a rush project
                            if (!isRushProject && !withContract) {
                                Swal.fire('Validation Error',
                                    'You must check "With Contract" to approve this quotation.', 'warning');
                                return;
                            }

                            // ✅ UPDATED: Contract subject is REQUIRED only if NOT a rush project
                            if (!isRushProject && !contractSubject) {
                                Swal.fire('Validation Error', 'Contract Subject is required.', 'warning');
                                return;
                            }

                            // ✅ UPDATED: Date validation only applies if NOT a rush project
                            if (!isRushProject) {
                                // ✅ VALIDATION: Start date is required (for non-rush)
                                if (!projectStartDate) {
                                    Swal.fire('Validation Error', 'Project Start Date is required.', 'warning');
                                    return;
                                }

                                // ✅ VALIDATION: End date is required (for non-rush)
                                if (!projectEndDate) {
                                    Swal.fire('Validation Error', 'Project End Date is required.', 'warning');
                                    return;
                                }

                                // ✅ UPDATED: Start date CAN be in the past (allow backtracking)
                                // Removed: const today check against projectStartDate

                                // ✅ VALIDATION: Start date must be before end date
                                if (projectStartDate > projectEndDate) {
                                    Swal.fire('Validation Error', 'Project start date must be before end date.',
                                        'warning');
                                    return;
                                }
                            }

                            // Approve with status ID 2
                            await this.updateStatus(quotationId, 2, {
                                contract_subject: contractSubject,
                                project_start_date: isRushProject ? null : projectStartDate,
                                project_end_date: isRushProject ? null : projectEndDate,
                                is_rush_project: isRushProject ? 1 : 0,
                                with_contract: withContract ? 1 : 0
                            });

                            // Close modal on success
                            const modal = bootstrap.Modal.getInstance(document.getElementById('approveModal'));
                            if (modal) modal.hide();
                        });
                    }

                    async updateStatus(quotationId, statusId, contractData = {}) {
                        try {
                            const payload = {
                                status_id: statusId,
                                ...contractData // Include contract data if provided (for approve action)
                            };

                            const endpoint = this.quotationType === 'additional' ?
                                `/additional-quotations/${quotationId}/status` :
                                `/quotations/${quotationId}/status`;

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

                new QuotationStatusHandler("{{ $quotationType ?? 'regular' }}");
            </script>

            <script>
                window.quotationMaterialHandler = {
                    loadMaterials: async function() {
                        const quotationId = "{{ $quotationId }}";
                        const quotationType = "{{ $quotationType }}";
                        const qStatus = "{{ strtolower($quotation->status->status_name ?? '') }}";
                        const readonly = {{ empty($readonly) ? 'false' : 'true' }};

                        try {
                            // Dynamically determine endpoint based on quotation type
                            const endpoint = quotationType === 'additional' ?
                                `/additional-quotation/${quotationId}/materials` :
                                `/quotation/${quotationId}/materials`;

                            const res = await fetch(endpoint);
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
                                    data-pivot="${mat.pivot_id}" data-quot="${quotationId}"
                                    value="${mat.quantity}" min="1"
                                    style="width: 80px; display:inline-block;">
                                <span>${escapeHtml(mat.unit)}</span>
                            </td>
                            <td>${priceField}</td>
                            <td class="line-total text-end">₱${formatNumberWithCommas(mat.line_total)}</td>
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

                            // ✅ Update grand total using centralized updater (preserves markup)
                            if (data.grand_total !== undefined) {
                                console.log('📊 Updating grand total to:', data.grand_total);
                                updateGrandTotalDisplay(data.grand_total);
                            } else {
                                console.log('⚠️ No grand_total in response, computing from page');
                                computeAndUpdateGrandTotal();
                            }

                            // ✅ NEW: Also update the Total Material Cost
                            updateTotalMaterialCost();

                            // Ensure price inputs are bound (formatting) before attaching handlers
                            bindPriceInputs("{{ $quotationType ?? 'regular' }}");

                            // ✅ Rebind handlers
                            try {
                                new QuantityUpdater(".update-quantity", "{{ $quotationType ?? 'regular' }}");
                                new DeleteMaterialFromQuotation(".delete-material", "{{ $quotationType ?? 'regular' }}");

                                // ✅ Restore FeeUpdater state tracking WITHOUT re-initializing
                                // This preserves event listeners and input state values
                                if (window.feeUpdater && typeof window.feeUpdater.restoreStateTracking === 'function') {
                                    window.feeUpdater.restoreStateTracking();
                                }

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
                    const quotationType = "{{ $quotationType ?? 'regular' }}";

                    Swal.fire({
                        title: 'Create a revision?',
                        text: "Do you want to create a revision for this quotation?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, create it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        const endpoint = quotationType === 'additional' ?
                            `/additional-quotations/${id}/create-revision` :
                            `/quotations/${id}/create-revision`;

                        fetch(endpoint, {
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
                                        const redirectUrl = quotationType === 'additional' ?
                                            `/additional-quotations/${data.quotation_id || id}/edit` :
                                            `/quotations/${data.quotation_id}`;
                                        window.location.href = redirectUrl;
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
                                const quotationId = "{{ $quotationId }}";
                                const quotationType = "{{ $quotationType ?? 'regular' }}";

                                // Call the generate token endpoint
                                const endpoint = quotationType === 'additional' ?
                                    `/additional-quotations/${quotationId}/generate-token` :
                                    `/quotations/${quotationId}/generate-token`;

                                const response = await fetch(endpoint, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json'
                                    }
                                });

                                const data = await response.json();

                                if (data.success) {
                                    const link = data.public_link;
                                    await navigator.clipboard.writeText(link);
                                    Swal.fire({
                                        title: 'Link Generated & Copied!',
                                        text: link,
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Failed',
                                        text: data.message || 'Could not generate link',
                                        icon: 'error'
                                    });
                                }
                            } catch (err) {
                                console.error(err);
                                Swal.fire('Error', 'Could not generate the link: ' + err.message, 'error');
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
                    try {
                        bindPriceInputs("{{ $quotationType ?? 'regular' }}");
                    } catch (e) {
                        console.error('bindPriceInputs error', e);
                    }
                });
            </script>

            <!-- View Additional Quotations Modal -->
            <div class="modal fade" id="additionalQuotationsModal" tabindex="-1"
                aria-labelledby="additionalQuotationsLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="additionalQuotationsLabel">Additional Quotations</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
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
                    const viewAdditionalQtnBtn = document.getElementById('viewAdditionalQtnBtn');
                    const modalEl = document.getElementById('additionalQuotationsModal');

                    if (!viewAdditionalQtnBtn || !modalEl) return;

                    const bsModal = new bootstrap.Modal(modalEl);

                    viewAdditionalQtnBtn.addEventListener('click', async function() {
                        const parentId = this.getAttribute('data-parent-id');
                        const container = document.getElementById('additionalQuotationsList');

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
                            container.innerHTML = '';

                            if (data.quotations.length === 0) {
                                container.innerHTML =
                                    '<div class="alert alert-info">No additional quotations yet.</div>';
                            } else {
                                data.quotations.forEach((quotation, index) => {
                                    const div = document.createElement('div');
                                    div.classList.add('card', 'mb-3', 'border-primary', 'border-start',
                                        'border-5');

                                    const statusBadgeClass = {
                                            'draft': 'bg-secondary',
                                            'pending': 'bg-warning text-dark',
                                            'approved': 'bg-success',
                                            'rejected': 'bg-danger',
                                            'completed': 'bg-success',
                                            'ongoing': 'bg-info'
                                        } [quotation.status?.status_name?.toLowerCase()] ||
                                        'bg-secondary';

                                    const approvalStatus = quotation.customer_approved ?
                                        '<span><i class="fa-solid fa-circle text-success me-2" style="font-size: 0.5rem;"></i>Approved</span>' :
                                        '<span><i class="fa-solid fa-circle text-warning me-2" style="font-size: 0.5rem;"></i>Pending</span>';

                                    div.innerHTML = `
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">${quotation.subject || 'Untitled'}</h6>
                                        <small class="text-muted">ID: ${quotation.id}</small>
                                    </div>
                                    <div>
                                        <span class="badge ${statusBadgeClass}">
                                            ${quotation.status?.status_name || 'Unknown'}
                                        </span>
                                        ${approvalStatus}
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="mb-2"><strong>Description:</strong> <br> 
                                        ${quotation.description ? quotation.description : '<em class="text-muted">No description</em>'}
                                    </p>
                                    <p class="mb-2"><strong>Created:</strong> ${new Date(quotation.created_at).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })}</p>
                                    <p class="mb-2"><strong>Materials:</strong> ${quotation.materials?.length || 0}</p>
                                    <div class="d-flex gap-2">
                                        <a href="/additional-quotations/${quotation.id}/edit" class="btn btn-sm btn-primary">
                                            <i class="fa-solid fa-eye me-1"></i> View/Edit
                                        </a>
                                    </div>
                                </div>
                            `;
                                    container.appendChild(div);
                                });
                            }

                            // Show modal
                            bsModal.show();
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
            </script>

        @endsection
