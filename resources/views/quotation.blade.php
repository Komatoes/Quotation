    <script>
        // Ensure Add Selected Materials button in modal triggers the handler
        document.addEventListener('DOMContentLoaded', function() {
            const addSelectedBtn = document.getElementById('addSelectedMaterialsBtn');
            if (addSelectedBtn) {
                addSelectedBtn.onclick = function() {
                    addMaterialQuotation.add('addMaterialForm');
                };
            }
        });
    </script>
    @extends('layouts.app')
    @include('include.head')
    @section('content')
        <div class="content-wrapper">
            <!-- Content -->
            <div class="container-xxl flex-grow-1 container-p-y">

                <!-- Header -->
                <div class="card mb-6">
                    <div class="card-body text-center bg-light rounded shadow-sm">
                        <h1 class="h3 mb-0 text-dark">Creating Quotation...</h1>
                    </div>
                </div>

                <!-- Quotation Info -->
                <div class="card mb-6">
                    <div class="card-body">
                        <h3 class="mb-3">{{ $quotation->subject }}</h3>

                        <p><strong>Customer:</strong> {{ $client->first_name }} {{ $client->last_name }}</p>
                        <p><strong>Contact:</strong> {{ $client->contact_no }}</p>
                        <p><strong>Address:</strong> {{ $client->address }}</p>

                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMatModal">
                            Add Material
                        </button>

                    </div>
                </div>

                <!-- Materials Table -->
                <div class="card">
                    <div class="card-datatable table-responsive">
                        <table class="table table-bordered">
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
                                            <input type="number" class="form-control update-quantity"
                                                data-pivot="{{ $mat->pivot->id }}" data-quot="{{ $quotation->id }}"
                                                value="{{ $mat->pivot->quantity }}" min="1"
                                                style="width: 80px; display:inline-block;">
                                            <span>{{ $mat->unit }}</span>
                                        </td>


                                        <td>₱{{ number_format($mat->unit_price, 2) }}</td>
                                        <td class="line-total">
                                            ₱{{ number_format($mat->unit_price * $mat->pivot->quantity, 2) }}
                                        </td>
                                        <td class="text-center">
                                            <!-- Delete Button -->
                                            <a href="#" class="text-danger delete-material"
                                                data-id="{{ $mat->pivot->id }}" data-quot="{{ $quotation->id }}">
                                                <i class="ti ti-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Labor Fee:</td>
                                    <td colspan="2">
                                        <input type="number" class="form-control text-end fee-input" id="laborFee"
                                            value="{{ $quotation->labor_fee }}" step="0.01" data-field="labor_fee">
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Delivery/Hauling Fee:</td>
                                    <td colspan="2">
                                        <input type="number" class="form-control text-end fee-input" id="deliveryFee"
                                            value="{{ $quotation->delivery_fee }}" step="0.01"
                                            data-field="delivery_fee">
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

                <!-- Action Buttons -->
                <div class="mt-3">
                    <button class="btn btn-success" id="approveBtn" data-quot="{{ $quotation->id }}">Approve</button>
                    <button class="btn btn-primary" id="saveDraftBtn" data-quot="{{ $quotation->id }}">Save as
                        Draft</button>
                    <button class="btn btn-danger" id="rejectBtn" data-quot="{{ $quotation->id }}">Reject</button>
                </div>

                <!-- Add Material to Quotation Modal -->
                <div class="modal fade" id="addMatModal" tabindex="-1" aria-labelledby="addMatModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form id="addMaterialForm" method="POST" action="{{ url('/quotation-materials/store') }}">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addMatModalLabel">Add Material to Quotation</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                                    <!-- Search bar & button -->
                                    <div class="d-flex mb-3">
                                        <input type="text" id="materialSearch" class="form-control me-2"
                                            placeholder="Search materials...">
                                        <button type="button" id="openNewMaterialModalBtn" class="btn btn-success"
                                            data-bs-toggle="modal" data-bs-target="#newMaterialModal">
                                            + Add Material
                                        </button>

                                    </div>

                                    <!-- Materials Table -->
                                    <table class="table table-bordered" id="materialsTable">
                                        <thead>
                                            <tr>
                                                <th>Material Name</th>
                                                <th>Unit</th>
                                                <th>Unit Cost</th>
                                                <th>Quantity</th>
                                                <th>Select</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Will be filled by JS -->
                                        </tbody>
                                    </table>

                                    <!-- Hidden quotation id -->
                                    <input type="hidden" name="quot_id" value="{{ $quotation->id }}">
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary"
                                        onclick="addMaterialQuotation.add('addMaterialForm')">Add Selected
                                        Materials</button>
                                    <button type="button" class="btn btn-outline-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- New Material Modal -->
        <div class="modal fade" id="newMaterialModal" tabindex="-1" aria-labelledby="newMaterialModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="newMaterialForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="newMaterialModalLabel">Add New Material</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="materialName" class="form-label">Material Name</label>
                                <input type="text" class="form-control" id="materialName" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="materialUnit" class="form-label">Unit</label>
                                <input type="text" class="form-control" id="materialUnit" name="unit">
                            </div>
                            <div class="mb-3">
                                <label for="materialPrice" class="form-label">Unit Price</label>
                                <input type="number" class="form-control" id="materialPrice" name="unit_price"
                                    step="0.01">
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
           <script>
   document.addEventListener('DOMContentLoaded', function() {
       const newMaterialForm = document.getElementById('newMaterialForm');
       if (newMaterialForm) {
           newMaterialForm.addEventListener('submit', async function(e) {
               e.preventDefault();
               const formData = new FormData(this);
               try {
                   const res = await fetch('/materials/store', {  // Adjust to your route
                       method: 'POST',
                       headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                       body: formData
                   });
                   const data = await res.json();
                   if (data.success) {
                       Swal.fire('Success!', 'Material added.', 'success');
                       const modal = bootstrap.Modal.getInstance(newMaterialForm.closest('.modal'));
                       modal.hide();
                       // Refresh parent modal's table if open
                       if (window.modalMaterialHandler) {
                           window.modalMaterialHandler.loadMaterials();
                       }
                       // Reset form
                       newMaterialForm.reset();
                   } else {
                       Swal.fire('Error', data.message || 'Failed to add material.', 'error');
                   }
               } catch (error) {
                   console.error('Error:', error);
                   Swal.fire('Error', 'Something went wrong!', 'error');
               }
           });
       }
   });
   </script>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const newMaterialModal = document.getElementById("newMaterialModal");

                if (newMaterialModal) {
                    newMaterialModal.addEventListener("hidden.bs.modal", function() {
                        if (document.querySelector("#addMatModal.show")) {
                            document.body.classList.add("modal-open");
                        }
                    });
                }
            });
        </script>
        <script>
            document.addEventListener('show.bs.modal', function(event) {
                // If another modal is already open, move the new one above it
                if (document.querySelectorAll('.modal.show').length) {
                    const zIndex = 1050 + (10 * document.querySelectorAll('.modal.show').length);
                    event.target.style.zIndex = zIndex;
                    setTimeout(() => {
                        const modalBackdrop = document.querySelector('.modal-backdrop:last-of-type');
                        if (modalBackdrop) modalBackdrop.style.zIndex = zIndex - 1;
                    });
                }
            });
        </script>
        <script>
            document.addEventListener('hidden.bs.modal', function(event) {
                // If no modals are still open, remove all backdrops
                if (document.querySelectorAll('.modal.show').length === 0) {
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = ''; // restore scroll
                    document.body.style.paddingRight = '';
                }
            });
        </script>

        <script>
            class MaterialHandler {
                constructor(tableId, fetchUrl) {
                    this.tableId = tableId;
                    this.fetchUrl = fetchUrl;
                }

                loadMaterials() {
                    fetch(this.fetchUrl)
                        .then(res => res.json())
                        .then(materials => {
                            const table = document.getElementById(this.tableId);
                            if (!table) return;

                            const tbody = table.querySelector("tbody");
                            tbody.innerHTML = ""; // Clear existing rows

                            materials.forEach(material => {
                                const row = `
                        <tr>
                            <td>${material.name}</td>
                            <td>${material.unit}</td>
                            <td>₱${parseFloat(material.unit_cost).toFixed(2)}</td>
                            <td>
                                <input type="number" 
                                       name="quantity[${material.id}]" 
                                       class="form-control" 
                                       value="1" 
                                       min="1">
                            </td>
                            <td class="text-center">
                                <input type="checkbox" name="selected[]" value="${material.id}">
                            </td>
                        </tr>
                    `;
                                tbody.insertAdjacentHTML("beforeend", row);
                            });
                        })
                        .catch(error => console.error("Error loading materials:", error));
                }
            }

            // 🔹 Initialize when modal opens
            document.getElementById('addMatModal').addEventListener('shown.bs.modal', () => {
                window.modalMaterialHandler = new MaterialHandler("materialsTable", "/materials/list");
                window.modalMaterialHandler.loadMaterials();
            });
        </script>

        <script>
            class AddMaterialtoQuotation {
                async add(id) {
                    const form = document.getElementById(id);
                    if (!form) return;
                    const formData = new FormData(form);

                    try {
                        const res = await fetch("/quotation-materials/add-selected", {
                            method: "POST",
                            credentials: "same-origin",
                            headers: {
                                "X-CSRF-TOKEN": '{{ csrf_token() }}',
                                "Accept": "application/json"
                            },
                            body: formData
                        });

                        const data = await res.json();

                        if (!res.ok || !data.success) {
                            const msg = data.message || (data.errors ? Object.values(data.errors).flat().join('\n') :
                                'Failed to add materials');
                            Swal.fire({
                                title: msg,
                                icon: 'error'
                            });
                            return;
                        }

                        // ✅ Rebuild main materials table tbody (unchanged)
                        const tbody = document.querySelector('.card-datatable table.table tbody');
                        if (tbody && Array.isArray(data.materials)) {
                            tbody.innerHTML = '';
                            data.materials.forEach(m => {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `
                           <td>${m.name}</td>
                           <td>${m.quantity} ${m.unit ?? ''}</td>
                           <td>₱${parseFloat(m.unit_price).toFixed(2)}</td>
                           <td class="line-total">₱${parseFloat(m.line_total).toFixed(2)}</td>
                           <td class="text-center">
                               <a href="#" class="text-danger delete-material" 
                                  data-id="${m.pivot_id}" 
                                  data-quot="${data.quotation_id ?? '{{ $quotation->id }}'}">
                                   <i class="ti ti-trash"></i>
                               </a>
                           </td>
                       `;
                                tbody.appendChild(tr);
                            });
                        }

                        // ✅ Update fees & grand total if provided (unchanged)
                        if (data.labor_fee !== undefined) {
                            const laborInput = document.getElementById('laborFee');
                            if (laborInput) laborInput.value = parseFloat(data.labor_fee).toFixed(2);
                        }
                        if (data.delivery_fee !== undefined) {
                            const deliveryInput = document.getElementById('deliveryFee');
                            if (deliveryInput) deliveryInput.value = parseFloat(data.delivery_fee).toFixed(2);
                        }
                        if (data.grand_total !== undefined) {
                            const grandTotalEl = document.getElementById('grandTotal');
                            if (grandTotalEl) grandTotalEl.textContent = '₱' + parseFloat(data.grand_total).toFixed(2);
                        }

                        // ✅ Properly close MODAL (not offcanvas!)
                        const modalEl = document.getElementById('addMatModal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) {
                            modal.hide(); // This triggers Bootstrap's hidden event for cleanup
                        }

                        // ✅ Fallback cleanup (if needed, but Bootstrap should handle)
                        setTimeout(() => {
                            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                            if (!document.querySelector('.modal.show')) {
                                document.body.classList.remove('modal-open');
                                document.body.style.overflow = '';
                                document.body.style.paddingRight = '';
                            }
                        }, 300); // Delay to allow Bootstrap animation

                        Swal.fire({
                            title: data.message || 'Materials updated',
                            icon: 'success',
                            timer: 1000,
                            showConfirmButton: false,
                            timerProgressBar: true,
                            position: 'center'
                        });

                    } catch (error) {
                        console.error("Error adding materials:", error);
                        Swal.fire("Something went wrong!", "", "error");
                    }
                }
            }

            const addMaterialQuotation = new AddMaterialtoQuotation();
        </script>


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

                            // Remove row from DOM
                            if (rowEl) rowEl.remove();

                            // Update grand total if server sends it back
                            if (data.grand_total !== undefined) {
                                document.getElementById("grandTotal").textContent =
                                    "₱" + parseFloat(data.grand_total).toFixed(2);
                            }
                        } else {
                            Swal.fire("Error", data.message || "Failed to delete", "error");
                        }
                    } catch (error) {
                        console.error("Error deleting material:", error);
                        Swal.fire("Something went wrong!", "", "error");
                    }
                }
            }

            const deleteMaterialHandler = new DeleteMaterialFromQuotation(".delete-material");
        </script>

        <script>
            class QuantityUpdater {
                constructor(selector) {
                    this.selector = selector;
                    this.init();
                }

                init() {
                    document.querySelectorAll(this.selector).forEach(input => {
                        input.addEventListener("change", (e) => this.update(e));
                    });
                }

                update(e) {
                    const input = e.target;
                    const newQty = input.value;
                    const pivotId = input.dataset.pivot;
                    const quotId = input.dataset.quot;

                    fetch(`/quotation-materials/update-quantity`, {
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
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                // ✅ update line total in UI
                                input.closest("tr").querySelector(".line-total").textContent =
                                    `₱${data.line_total.toFixed(2)}`;
                            } else {
                                Swal.fire("Update failed", data.message || "", "error");
                            }
                        })
                        .catch(error => {
                            console.error("Error:", error);
                            Swal.fire("Something went wrong!", "", "error");
                        });
                }
            }

            new QuantityUpdater(".update-quantity");
        </script>
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

                            if (button.id === "saveDraftBtn") statusId = 1;
                            if (button.id === "approveBtn") statusId = 2;
                            if (button.id === "rejectBtn") statusId = 3;

                            if (statusId) {
                                this.updateStatus(quotationId, statusId);
                            }
                        });
                    });
                }

                async updateStatus(quotationId, statusId) {
                    try {
                        const res = await fetch(`/quotations/${quotationId}/status`, {
                            method: "PUT",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": this.csrfToken
                            },
                            body: JSON.stringify({
                                status_id: statusId
                            })
                        });

                        const data = await res.json();
                        if (data.success) {
                            Swal.fire("Success", data.message, "success");
                        } else {
                            Swal.fire("Error", data.message, "error");
                        }
                    } catch (error) {
                        console.error("Error updating quotation status:", error);
                        Swal.fire("Error", "Something went wrong!", "error");
                    }
                }
            }

            document.addEventListener("DOMContentLoaded", () => {
                new QuotationStatusHandler();
            });
        </script>
        <script>
            class FeeUpdater {
                constructor(selector) {
                    this.selector = selector;
                    this.csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                    this.init();
                }

                init() {
                    document.querySelectorAll(this.selector).forEach(input => {
                        input.addEventListener("change", (e) => this.updateFee(e));
                    });
                }

                async updateFee(e) {
                    const input = e.target;
                    const value = input.value;
                    const field = input.dataset.field; // "labor_fee" or "delivery_fee"
                    const quotationId = "{{ $quotation->id }}";

                    try {
                        const res = await fetch(`/quotations/${quotationId}/update-fee`, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": this.csrfToken
                            },
                            body: JSON.stringify({
                                field: field,
                                value: value
                            })
                        });

                        const data = await res.json();
                        if (data.success) {
                            document.getElementById("grandTotal").textContent =
                                "₱" + parseFloat(data.grand_total).toFixed(2);

                            Swal.fire({
                                title: data.message,
                                icon: "success",
                                timer: 800,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire("Error", data.message || "Update failed", "error");
                        }
                    } catch (error) {
                        console.error("Error updating fee:", error);
                        Swal.fire("Error", "Something went wrong!", "error");
                    }
                }
            }

            document.addEventListener("DOMContentLoaded", () => {
                new FeeUpdater(".fee-input");
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Handle show for nested modals (z-index stacking)
                document.addEventListener('show.bs.modal', function(event) {
                    const modals = document.querySelectorAll('.modal.show');
                    if (modals.length > 0) {
                        const zIndex = 1050 + (10 * modals.length);
                        event.target.style.zIndex = zIndex;
                        setTimeout(() => {
                            const backdrop = document.querySelector('.modal-backdrop:last-of-type');
                            if (backdrop) backdrop.style.zIndex = zIndex - 1;
                        }, 0);
                    }
                });

                // Handle hidden for cleanup
                document.addEventListener('hidden.bs.modal', function(event) {
                    const modals = document.querySelectorAll('.modal.show');
                    if (modals.length === 0) {
                        // No modals open: full cleanup
                        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                        document.body.classList.remove('modal-open');
                        document.body.style.overflow = '';
                        document.body.style.paddingRight = '';
                    } else {
                        // Still modals open: ensure body is modal-open
                        document.body.classList.add('modal-open');
                    }
                });

                // Specific fix for newMaterialModal hidden event
                const newMaterialModal = document.getElementById('newMaterialModal');
                if (newMaterialModal) {
                    newMaterialModal.addEventListener('hidden.bs.modal', function() {
                        const addMatModal = document.getElementById('addMatModal');
                        if (addMatModal && bootstrap.Modal.getInstance(addMatModal)) {
                            document.body.classList.add('modal-open');
                        }
                    });
                }

                // Force-reset on reopen attempt (safety net)
                const addMatModal = document.getElementById('addMatModal');
                if (addMatModal) {
                    addMatModal.addEventListener('show.bs.modal', function() {
                        // Clean any lingering backdrops before show
                        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                        document.body.classList.add('modal-open');
                    });
                }
            });
        </script>s
    @endsection
