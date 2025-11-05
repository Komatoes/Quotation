@extends('layouts.app')

@section('content')
<div class="container">
    <h2>{{ $quotation->subject }}</h2>
    <p>{{ $quotation->description }}</p>

    <hr>

    {{-- ✅ Comments Section --}}
    <h4>Comments</h4>
    <div id="comments-list">
        @foreach($quotation->comments as $comment)
            <div class="mb-2">
                <strong>{{ $comment->user_type == 'client' ? 'You' : 'Admin' }}:</strong>
                <span>{{ $comment->message }}</span>
                <small class="text-muted d-block">{{ $comment->created_at->diffForHumans() }}</small>
            </div>
        @endforeach
    </div>

    {{-- ✅ Add Comment Form --}}
    <div class="mt-3">
        <textarea id="comment-input" class="form-control" rows="3" placeholder="Write a comment..."></textarea>
        <button id="submit-comment" class="btn btn-primary mt-2">Send</button>
    </div>

    <hr>

    {{-- ✅ Approve Quotation --}}
    @if(!$quotation->customer_approved)
        <button id="approve-btn" class="btn btn-success">Approve Quotation</button>
    @else
        <span class="badge bg-success">You approved this quotation ✅</span>
    @endif
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
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({ message: message })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('comments-list').innerHTML += `
                    <div class="mb-2">
                        <strong>You:</strong> ${data.comment.message}
                        <small class="text-muted d-block">Just now</small>
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
