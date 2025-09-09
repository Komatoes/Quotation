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

                <button class="btn btn-primary mb-3" data-bs-toggle="offcanvas" data-bs-target="#addMaterialOffcanvas">
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
                        @foreach($materials as $mat)
                        <tr>
                            <td>{{ $mat->name }}</td>
                            <td>{{ $mat->pivot->quantity }} {{ $mat->unit }}</td>
                            <td>₱{{ number_format($mat->unit_cost, 2) }}</td>
                            <td class="line-total">₱{{ number_format($mat->unit_cost * $mat->pivot->quantity, 2) }}</td>
                            <td class="text-center">
                                <!-- Edit Button -->
                                <a href="#" 
                                   class="text-primary me-2 edit-material" 
                                   data-id="{{ $mat->pivot->id }}" 
                                   data-quot="{{ $quotation->id }}" 
                                   data-qty="{{ $mat->pivot->quantity }}">
                                    <i class="ti ti-edit"></i>
                                </a>

                                <!-- Delete Button -->
                                <a href="#" 
                                   class="text-danger delete-material" 
                                   data-id="{{ $mat->pivot->id }}" 
                                   data-quot="{{ $quotation->id }}">
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
                                <input type="number" class="form-control text-end fee-input" 
                                    id="laborFee"
                                    value="{{ $quotation->labor_fee }}"
                                    step="0.01"
                                    data-field="labor_fee">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Delivery/Hauling Fee:</td>
                            <td colspan="2">
                                <input type="number" class="form-control text-end fee-input" 
                                    id="deliveryFee"
                                    value="{{ $quotation->delivery_fee }}"
                                    step="0.01"
                                    data-field="delivery_fee">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                            <td colspan="2" class="fw-bold text-danger" id="grandTotal">
                                ₱{{ number_format($materials->sum(fn($m) => $m->unit_cost * $m->pivot->quantity) + $quotation->labor_fee + $quotation->delivery_fee, 2) }}
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

    </div>
</div>
@endsection
