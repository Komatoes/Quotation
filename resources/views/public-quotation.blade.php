@extends('layouts.public')

@section('content')
    <div class="card mb-4">
        <div class="card-body text-center bg-light rounded shadow-sm">
            <h1 class="h3 mb-0 text-dark">Quotation Details</h1>
        </div>
    </div>

    <!-- Quotation Info -->
    <div class="card mb-4">
        <div class="card-body">
            <h3 class="mb-3">{{ $quotation->subject }}</h3>
            <p><strong>Customer:</strong> {{ $client->first_name }} {{ $client->last_name }}</p>
            <p><strong>Contact:</strong> {{ $client->contact_no }}</p>
            <p><strong>Address:</strong> {{ $client->address }}</p>
        </div>
    </div>

    <!-- Materials Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Material</th>
                        <th>Estimated Quantity</th>
                        <th>Price/Unit</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($materials as $mat)
                        <tr>
                            <td>{{ $mat->name }}</td>
                            <td>
                                {{ $mat->pivot->quantity }}
                                <span>{{ $mat->unit }}</span>
                            </td>
                            <td>₱{{ number_format($mat->unit_price, 2) }}</td>
                            <td>₱{{ number_format($mat->unit_price * $mat->pivot->quantity, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Labor Fee:</td>
                        <td>₱{{ number_format($quotation->labor_fee, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Delivery/Hauling Fee:</td>
                        <td>₱{{ number_format($quotation->delivery_fee, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                        <td class="fw-bold text-danger">
                            ₱{{ number_format($materials->sum(fn($m) => $m->unit_price * $m->pivot->quantity) + $quotation->labor_fee + $quotation->delivery_fee, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection