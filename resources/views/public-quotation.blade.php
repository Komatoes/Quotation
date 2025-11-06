@extends('layouts.public')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-2">{{ $quotation->subject }}</h4>
                    <p class="mb-1">{{ $quotation->description }}</p>
                </div>
                @if(!$quotation->customer_approved)
                    <button id="approve-btn" class="btn btn-success">
                        <i class="ti ti-check me-1"></i> Approve Quotation
                    </button>
                @else
                    <div class="badge bg-success p-2">
                        <i class="ti ti-check-circle me-1"></i> You approved this quotation
                    </div>
                @endif
            </div>
            
            <div class="row">
                <div class="col-xl-4 col-md-6 col-sm-12">
                    <div class="mb-3">
                        <label class="form-label">Contact Information</label>
                        <p class="mb-1"><strong>Name:</strong> {{ $quotation->client->name }}</p>
                        <p class="mb-1"><strong>Phone:</strong> {{ $quotation->client->contact_no }}</p>
                        <p class="mb-1"><strong>Address:</strong> {{ $quotation->client->address }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Materials Table -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Materials & Services</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Material/Service</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($materials as $material)
                        <tr>
                            <td>{{ $material->name }}</td>
                            <td>{{ $material->pivot->quantity }} {{ $material->unit }}</td>
                            <td>₱{{ number_format($material->unit_price, 2) }}</td>
                            <td>₱{{ number_format($material->unit_price * $material->pivot->quantity, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Labor Fee:</td>
                        <td>₱{{ number_format($quotation->labor_fee, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Delivery Fee:</td>
                        <td>₱{{ number_format($quotation->delivery_fee, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                        <td class="fw-bold text-primary fs-5">
                            ₱{{ number_format($materials->sum(fn($m) => $m->unit_price * $m->pivot->quantity) + $quotation->labor_fee + $quotation->delivery_fee, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Comments & Feedback</h5>
        </div>
        <div class="card-body">
            <div id="comments-list" class="mb-4">
                @foreach($quotation->comments as $comment)
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <div class="avatar @if($comment->sender_type === 'customer') avatar-primary @else avatar-success @endif">
                                <span class="avatar-initial rounded-circle">
                                    @if($comment->sender_type === 'customer')
                                        C
                                    @else
                                        A
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="mb-1">
                                <span class="fw-semibold">{{ $comment->sender_type === 'customer' ? 'You' : 'Admin' }}</span>
                                <small class="text-muted"> • {{ $comment->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1">{{ $comment->comment }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Add Comment Form -->
            <div class="mt-3">
                <textarea id="comment-input" class="form-control mb-3" rows="3" placeholder="Write your comment or feedback..."></textarea>
                <button id="submit-comment" class="btn btn-primary">
                    <i class="ti ti-send me-1"></i> Send Comment
                </button>
            </div>
        </div>
    </div>
</div>
</div>

{{-- ✅ JS Section --}}
<script>
    const publicToken = "{{ $quotation->public_token }}";
    const commentUrl = "{{ route('quotation.comment.submit', $quotation->public_token) }}";
    const approveUrl = "{{ route('quotation.customer.approve', $quotation->public_token) }}";

    // ✅ Submit Comment via AJAX
    document.getElementById('submit-comment').addEventListener('click', function () {
        const message = document.getElementById('comment-input').value.trim();

        if (message === "") {
            Swal.fire('Error', 'Comment cannot be empty', 'error');
            return;
        }

        fetch(commentUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ comment: message })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('comments-list').innerHTML += `
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-primary">
                                <span class="avatar-initial rounded-circle">C</span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="mb-1">
                                <span class="fw-semibold">You</span>
                                <small class="text-muted"> • Just now</small>
                            </div>
                            <p class="mb-1">${message}</p>
                        </div>
                    </div>`;
                document.getElementById('comment-input').value = "";
            }
        })
        .catch(() => {
            Swal.fire('Error', 'Something went wrong!', 'error');
        });
    });

    // ✅ Approve Quotation
    document.getElementById('approve-btn')?.addEventListener('click', function () {
        fetch(approveUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Approved!', 'You have approved this quotation.', 'success')
                .then(() => location.reload());
            }
        })
        .catch(() => {
            Swal.fire('Error', 'Could not approve quotation', 'error');
        });
    });

</script>
@endsection
