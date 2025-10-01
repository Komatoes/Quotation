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

                    <!-- Add Material Button -->
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
                                        ₱{{ number_format($mat->unit_price * $mat->pivot->quantity, 2) }}</td>
                                    <td class="text-center">
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
                                        value="{{ $quotation->delivery_fee }}" step="0.01" data-field="delivery_fee">
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
                <button class="btn btn-primary" id="saveDraftBtn" data-quot="{{ $quotation->id }}">Save as Draft</button>
                <button class="btn btn-danger" id="rejectBtn" data-quot="{{ $quotation->id }}">Reject</button>
            </div>

            <!-- Include Modals -->
            @include('include.modals.add_material')
            @include('include.modals.new_material')


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
                    <i class="ti ti-trash"></i>
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

    <!-- Update Quantity -->
    <script>
        class QuantityUpdater {
            constructor(selector) {
                this.selector = selector;
                this.init();
            }
            init() {
                document.querySelectorAll(this.selector).forEach(input => input.addEventListener("change", (e) => this
                    .update(e)));
            }
            update(e) {
                const input = e.target,
                    newQty = input.value,
                    pivotId = input.dataset.pivot,
                    quotId = input.dataset.quot;
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
                            input.closest("tr").querySelector(".line-total").textContent =
                                `₱${data.line_total.toFixed(2)}`;
                        } else {
                            Swal.fire("Update failed", data.message || "", "error");
                        }
                    }).catch(error => {
                        console.error(error);
                        Swal.fire("Something went wrong!", "", "error");
                    });
            }
        }
        new QuantityUpdater(".update-quantity");
    </script>

    <!-- Update Fees -->
    <script>
        class FeeUpdater {
            constructor(selector) {
                this.selector = selector;
                this.csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                this.init();
            }
            init() {
                document.querySelectorAll(this.selector).forEach(input => input.addEventListener("change", (e) => this
                    .updateFee(e)));
            }
            async updateFee(e) {
                const input = e.target,
                    value = input.value,
                    field = input.dataset.field,
                    quotationId = "{{ $quotation->id }}";
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
                        document.getElementById("grandTotal").textContent = "₱" + parseFloat(data.grand_total).toFixed(
                            2);
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
                    console.error(error);
                    Swal.fire("Error", "Something went wrong!", "error");
                }
            }
        }
        new FeeUpdater(".fee-input");
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
                        if (button.id === "saveDraftBtn") statusId = 1;
                        if (button.id === "approveBtn") statusId = 2;
                        if (button.id === "rejectBtn") statusId = 3;
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
                            "X-CSRF-TOKEN": this.csrfToken
                        },
                        body: JSON.stringify({
                            status_id: statusId
                        })
                    });
                    const data = await res.json();
                    if (data.success) Swal.fire("Success", data.message, "success");
                    else Swal.fire("Error", data.message, "error");
                } catch (error) {
                    console.error(error);
                    Swal.fire("Error", "Something went wrong!", "error");
                }
            }
        }
        new QuotationStatusHandler();
    </script>
@endsection
