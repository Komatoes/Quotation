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
                                    <td>{{ $mat->pivot->quantity }} {{ $mat->unit }}</td>
                                    <td>₱{{ number_format($mat->unit_price, 2) }}</td>
                                    <td class="line-total">₱{{ number_format($mat->unit_cost * $mat->pivot->quantity, 2) }}
                                    </td>
                                    <td class="text-center">
                                        <!-- Edit Button -->
                                        <a href="#" class="text-primary me-2 edit-material"
                                            data-id="{{ $mat->pivot->id }}" data-quot="{{ $quotation->id }}"
                                            data-qty="{{ $mat->pivot->quantity }}">
                                            <i class="ti ti-edit"></i>
                                        </a>

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

            <!-- Add Material to Quotation Modal -->
            <div class="modal fade" id="addMatModal" tabindex="-1" aria-labelledby="addMatModalLabel" aria-hidden="true">
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
                                    <button type="button" id="openNewMaterialModalBtn" class="btn btn-success">
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
                                    onclick="addMaterialQuotation.add('addMaterialForm')">Add Selected Materials</button>
                                <button type="button" class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                            <td>₱${parseFloat(material.unit_price).toFixed(2)}</td>
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
            add(id) {
                const form = document.getElementById(id);
                const formData = new FormData(form);

                fetch("/add-materialquotation", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: data.message,
                                icon: "success"
                            });
                        } else {
                            Swal.fire("Failed to create quotation", "", "error");
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        Swal.fire("Something went wrong!", "", "error");
                    });
            }
        }
        const addMaterialQuotation = new AddMaterialtoQuotation();
    </script>
@endsection
