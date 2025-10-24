@extends('layouts.app')
@section('content')
    <div class="container-fluid py-3">

        <!-- Header -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body text-center bg-light">
                <h1 class="h3 mb-0 text-dark">{{ $quotation->name }}</h1>
            </div>
        </div>

        <!-- Project Info -->
        <div class="card mb-3">
            <div class="card-body">
                <h3 class="mb-3">{{ $quotation->subject }}</h3>
                <p><b>Client Name:</b> {{ $quotation->client->first_name }} {{ $quotation->client->last_name }}</p>
                <p><b>Date:</b> {{ $quotation->created_at->format('F d, Y') }}</p>
            </div>
        </div>

        <!-- Materials Table -->
        <div class="card mb-3">
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Material</th>
                            <th>Estimated Quantity</th>
                            <th>Price/Unit</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($quotation->materials as $material)
                            <tr>
                                <td>{{ $material->name }}</td>
                                <td>{{ $material->pivot->quantity ?? 0 }}</td>
                                <td>₱{{ number_format($material->unit_price, 2) }}</td>
                                <td>₱{{ number_format($material->unit_price * ($material->pivot->quantity ?? 0), 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @php
                    $totalMaterial = $quotation->materials->sum(fn($m) => $m->unit_price * ($m->pivot->quantity ?? 0));
                    $labourCost = $quotation->labour_cost ?? 0;
                @endphp

                <div class="text-end mt-3">
                    <p><b>Total Material Cost:</b> ₱{{ number_format($totalMaterial, 2) }}</p>
                    <p><b>Labour Cost:</b> ₱{{ number_format($labourCost, 2) }}</p>
                    <h4><b>Grand Total:</b> ₱{{ number_format($totalMaterial + $labourCost, 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="text-end mt-4">
            <button class="btn btn-warning" id="createRevisionBtn" data-id="{{ $quotation->id }}">
                <i class="bi bi-pencil-square"></i> Create Revision
            </button>
        </div>



    </div>
    </div>
    </div>

<script>
document.getElementById('createRevisionBtn').addEventListener('click', function() {
    const id = this.dataset.id;

    if (!confirm('Create a revision for this quotation?')) return;

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
            alert(data.message);
            // redirect to quotation edit page
            window.location.href = `/quotations/${data.quotation_id}`;
        } else {
            alert('Failed to create revision: ' + data.message);
        }
    })
    .catch(err => {
        alert('Error creating revision.');
        console.error(err);
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection
