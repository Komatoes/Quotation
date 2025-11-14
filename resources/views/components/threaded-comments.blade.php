{{-- Threaded Comments Section with Edit, Delete, and Reply Features --}}
<!-- Comments & Feedback Section -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Comments & Feedback</h5>
    </div>
    <div class="card-body">
        <!-- Existing Comments (Threaded) -->
        <div id="comments-list" class="mb-4">
            @forelse($comments as $comment)
                <div class="comment-thread mb-4 border-start border-2 border-primary ps-3" data-comment-id="{{ $comment->id }}">
                    <!-- Main Comment -->
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <div class="avatar {{ $comment->sender_type === 'customer' ? 'avatar-primary' : 'avatar-success' }}">
                                <span class="avatar-initial rounded-circle">
                                    {{ isset($comment->user_name) ? strtoupper($comment->user_name[0]) : ($comment->sender_type === 'customer' ? 'C' : 'A') }}
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div>
                                    <span class="fw-semibold">
                                        {{ $comment->user_name ?? ($comment->sender_type === 'customer' ? 'Customer' : 'Admin') }}
                                    </span>
                                    <small class="text-muted ms-2">{{ $comment->created_at->diffForHumans() }}</small>
                                </div>
                                <div class="comment-actions">
                                    @if(Auth::check() && Auth::id() == $comment->user_id)
                                        <!-- Authenticated user editing their own comment -->
                                        <button class="btn btn-sm btn-link text-primary edit-comment" data-comment-id="{{ $comment->id }}" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button class="btn btn-sm btn-link text-danger delete-comment" data-comment-id="{{ $comment->id }}" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @elseif(!Auth::check() && $comment->user_id === null && isset($publicToken))
                                        <!-- Public customer editing their own comment (has no user_id, but publicToken matches) -->
                                        <button class="btn btn-sm btn-link text-primary edit-comment" data-comment-id="{{ $comment->id }}" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button class="btn btn-sm btn-link text-danger delete-comment" data-comment-id="{{ $comment->id }}" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <p class="mb-2 comment-text">{{ $comment->comment }}</p>
                            <button class="btn btn-sm btn-link text-secondary reply-toggle" data-comment-id="{{ $comment->id }}">
                                <i class="fa-solid fa-reply me-1"></i> Reply
                            </button>
                        </div>
                    </div>

                    <!-- Reply Form (Hidden by default) -->
                    <div class="reply-form-container ms-5 mb-3" id="reply-form-{{ $comment->id }}" style="display: none;">
                        <textarea class="form-control form-control-sm mb-2 reply-textarea" rows="2" placeholder="Write a reply..."></textarea>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-primary submit-reply" data-comment-id="{{ $comment->id }}">
                                <i class="fa-solid fa-paper-plane me-1"></i> Reply
                            </button>
                            <button class="btn btn-sm btn-secondary cancel-reply" data-comment-id="{{ $comment->id }}">Cancel</button>
                        </div>
                    </div>

                    <!-- Replies (Nested) -->
                    @if($comment->replies && $comment->replies->count() > 0)
                        <div class="replies-container ms-5">
                            @foreach($comment->replies as $reply)
                                <div class="reply-item mb-3 pb-3 border-bottom" data-reply-id="{{ $reply->id }}">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <div class="avatar avatar-sm {{ $reply->sender_type === 'customer' ? 'avatar-primary' : 'avatar-success' }}">
                                                <span class="avatar-initial rounded-circle">
                                                    {{ isset($reply->user_name) ? strtoupper($reply->user_name[0]) : ($reply->sender_type === 'customer' ? 'C' : 'A') }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <div>
                                                    <span class="fw-semibold" style="font-size: 0.9rem;">
                                                        {{ $reply->user_name ?? ($reply->sender_type === 'customer' ? 'Customer' : 'Admin') }}
                                                    </span>
                                                    <small class="text-muted ms-2">{{ $reply->created_at->diffForHumans() }}</small>
                                                </div>
                                                <div class="reply-actions">
                                                    @if(Auth::check() && Auth::id() == $reply->user_id)
                                                        <!-- Authenticated user editing their own reply -->
                                                        <button class="btn btn-sm btn-link text-primary edit-reply" data-reply-id="{{ $reply->id }}" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-link text-danger delete-reply" data-reply-id="{{ $reply->id }}" title="Delete">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    @elseif(!Auth::check() && $reply->user_id === null && isset($publicToken))
                                                        <!-- Public customer editing their own reply (has no user_id, but publicToken matches) -->
                                                        <button class="btn btn-sm btn-link text-primary edit-reply" data-reply-id="{{ $reply->id }}" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-link text-danger delete-reply" data-reply-id="{{ $reply->id }}" title="Delete">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                            <p class="mb-2 reply-text" style="font-size: 0.95rem;">{{ $reply->comment }}</p>
                                            
                                            <!-- Reply to reply button -->
                                            <button class="btn btn-sm btn-link text-secondary nested-reply-toggle" data-reply-id="{{ $reply->id }}" style="font-size: 0.85rem;">
                                                <i class="fa-solid fa-reply me-1"></i> Reply
                                            </button>
                                            
                                            <!-- Nested reply form (hidden by default) -->
                                            <div class="nested-reply-form-container ms-3 mt-2" id="nested-reply-form-{{ $reply->id }}" style="display: none;">
                                                <textarea class="form-control form-control-sm mb-2 nested-reply-textarea" rows="2" placeholder="Write a reply to this reply..."></textarea>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-primary submit-nested-reply" data-reply-id="{{ $reply->id }}">
                                                        <i class="fa-solid fa-paper-plane me-1"></i> Reply
                                                    </button>
                                                    <button class="btn btn-sm btn-secondary cancel-nested-reply" data-reply-id="{{ $reply->id }}">Cancel</button>
                                                </div>
                                            </div>
                                            
                                            <!-- Nested replies (replies to this reply) -->
                                            @if($reply->nestedReplies && $reply->nestedReplies->count() > 0)
                                                <div class="nested-replies-container ms-4 mt-2">
                                                    @foreach($reply->nestedReplies as $nestedReply)
                                                        <div class="nested-reply-item mb-2 pb-2 border-start ps-2" data-nested-reply-id="{{ $nestedReply->id }}" style="border-color: #dee2e6;">
                                                            <div class="d-flex">
                                                                <div class="flex-shrink-0">
                                                                    <div class="avatar avatar-xs {{ $nestedReply->sender_type === 'customer' ? 'avatar-primary' : 'avatar-success' }}" style="width: 28px; height: 28px;">
                                                                        <span class="avatar-initial rounded-circle" style="font-size: 0.7rem;">
                                                                            {{ isset($nestedReply->user_name) ? strtoupper($nestedReply->user_name[0]) : ($nestedReply->sender_type === 'customer' ? 'C' : 'A') }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="flex-grow-1 ms-2">
                                                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                                                        <div>
                                                                            <small class="fw-semibold">
                                                                                {{ $nestedReply->user_name ?? ($nestedReply->sender_type === 'customer' ? 'Customer' : 'Admin') }}
                                                                            </small>
                                                                            <small class="text-muted ms-2">{{ $nestedReply->created_at->diffForHumans() }}</small>
                                                                        </div>
                                                                        <div class="nested-reply-actions" style="font-size: 0.85rem;">
                                                                            @if(Auth::check() && Auth::id() == $nestedReply->user_id)
                                                                                <button class="btn btn-sm btn-link text-primary edit-nested-reply" data-nested-reply-id="{{ $nestedReply->id }}" title="Edit">
                                                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                                                </button>
                                                                                <button class="btn btn-sm btn-link text-danger delete-nested-reply" data-nested-reply-id="{{ $nestedReply->id }}" title="Delete">
                                                                                    <i class="fa-solid fa-trash"></i>
                                                                                </button>
                                                                            @elseif(!Auth::check() && $nestedReply->user_id === null && isset($publicToken))
                                                                                <button class="btn btn-sm btn-link text-primary edit-nested-reply" data-nested-reply-id="{{ $nestedReply->id }}" title="Edit">
                                                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                                                </button>
                                                                                <button class="btn btn-sm btn-link text-danger delete-nested-reply" data-nested-reply-id="{{ $nestedReply->id }}" title="Delete">
                                                                                    <i class="fa-solid fa-trash"></i>
                                                                                </button>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                    <p class="mb-0" style="font-size: 0.85rem;">{{ $nestedReply->comment }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-muted">No comments yet. Be the first to comment!</p>
            @endforelse
        </div>

        <!-- Add Comment Form -->
        <div class="mt-4 pt-3 border-top">
            <div class="d-flex gap-3">
                <div class="flex-shrink-0">
                    <div class="avatar avatar-primary">
                        <span class="avatar-initial rounded-circle">
                            {{ Auth::check() && Auth::user()->name ? strtoupper(Auth::user()->name[0]) : 'U' }}
                        </span>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <textarea id="main-comment-input" class="form-control mb-2" rows="3" placeholder="Share your thoughts..."></textarea>
                    <button id="submit-main-comment" class="btn btn-primary">
                        <i class="fa-solid fa-paper-plane me-1"></i> Post Comment
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Comment Modal -->
<div class="modal fade" id="editCommentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Comment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <textarea id="edit-comment-textarea" class="form-control" rows="4"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-comment-edit">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Reply Modal -->
<div class="modal fade" id="editReplyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Reply</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <textarea id="edit-reply-textarea" class="form-control" rows="4"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-reply-edit">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Threaded Comments Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quotationId = "{{ $quotationId ?? '' }}";
        const publicToken = "{{ $publicToken ?? '' }}";
        const currentUserId = "{{ Auth::id() ?? '' }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let editingCommentId = null;
        let editingReplyId = null;

        // Toggle reply form
        document.addEventListener('click', function(e) {
            if (e.target.closest('.reply-toggle')) {
                const commentId = e.target.closest('.reply-toggle').dataset.commentId;
                const replyForm = document.getElementById(`reply-form-${commentId}`);
                if (replyForm) {
                    replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
                }
            }
        });

        // Cancel reply
        document.addEventListener('click', function(e) {
            if (e.target.closest('.cancel-reply')) {
                const commentId = e.target.closest('.cancel-reply').dataset.commentId;
                const replyForm = document.getElementById(`reply-form-${commentId}`);
                if (replyForm) {
                    replyForm.style.display = 'none';
                    replyForm.querySelector('.reply-textarea').value = '';
                }
            }
        });

        // Submit main comment
        document.getElementById('submit-main-comment')?.addEventListener('click', async function() {
            const commentText = document.getElementById('main-comment-input').value.trim();
            if (!commentText) {
                Swal.fire('Error', 'Please write a comment', 'warning');
                return;
            }

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Posting...';

            try {
                const res = await fetch(`/quotation/${quotationId}/comments`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        comment: commentText,
                        sender_type: '{{ Auth::check() ? (Auth::user()->hasRole('customer') ? 'customer' : 'admin') : 'customer' }}'
                    })
                });

                const data = await res.json();
                if (data.success) {
                    // Add new comment to DOM without reloading
                    const newComment = data.comment;
                    const commentsList = document.getElementById('comments-list');
                    
                    // Create new comment HTML - use response data for user_name
                    const userInitial = (newComment.user_name && newComment.user_name.length > 0) 
                        ? newComment.user_name[0].toUpperCase() 
                        : 'U';
                    const newCommentHtml = `
                        <div class="comment-thread mb-4 border-start border-2 border-primary ps-3" data-comment-id="${newComment.id}">
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <div class="avatar ${newComment.sender_type === 'customer' ? 'avatar-primary' : 'avatar-success'}">
                                        <span class="avatar-initial rounded-circle">${userInitial}</span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div>
                                            <span class="fw-semibold">${newComment.user_name}</span>
                                            <small class="text-muted ms-2">just now</small>
                                        </div>
                                        <div class="comment-actions">
                                            <button class="btn btn-sm btn-link text-primary edit-comment" data-comment-id="${newComment.id}" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button class="btn btn-sm btn-link text-danger delete-comment" data-comment-id="${newComment.id}" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <p class="mb-2 comment-text">${newComment.comment}</p>
                                    <button class="btn btn-sm btn-link text-secondary reply-toggle" data-comment-id="${newComment.id}">
                                        <i class="fa-solid fa-reply me-1"></i> Reply
                                    </button>
                                </div>
                            </div>
                            <div class="reply-form-container ms-5 mb-3" id="reply-form-${newComment.id}" style="display: none;">
                                <textarea class="form-control form-control-sm mb-2 reply-textarea" rows="2" placeholder="Write a reply..."></textarea>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-primary submit-reply" data-comment-id="${newComment.id}">
                                        <i class="fa-solid fa-paper-plane me-1"></i> Reply
                                    </button>
                                    <button class="btn btn-sm btn-secondary cancel-reply" data-comment-id="${newComment.id}">Cancel</button>
                                </div>
                            </div>
                            <div class="replies-container ms-5"></div>
                        </div>
                    `;
                    
                    commentsList.insertAdjacentHTML('beforeend', newCommentHtml);
                    document.getElementById('main-comment-input').value = '';
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Comment posted!',
                        showConfirmButton: false,
                        timer: 1200
                    });
                } else {
                    Swal.fire('Error', data.message || 'Failed to post comment', 'error');
                }
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'Something went wrong', 'error');
            }

            this.disabled = false;
            this.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Post Comment';
        });

        // Edit comment
        document.addEventListener('click', function(e) {
            if (e.target.closest('.edit-comment')) {
                const commentId = e.target.closest('.edit-comment').dataset.commentId;
                const commentText = document.querySelector(`[data-comment-id="${commentId}"] .comment-text`).textContent;
                
                editingCommentId = commentId;
                document.getElementById('edit-comment-textarea').value = commentText;
                
                const modal = new bootstrap.Modal(document.getElementById('editCommentModal'));
                modal.show();
            }
        });

        // Save comment edit
        document.getElementById('save-comment-edit')?.addEventListener('click', async function() {
            const newText = document.getElementById('edit-comment-textarea').value.trim();
            if (!newText) {
                Swal.fire('Error', 'Comment cannot be empty', 'warning');
                return;
            }

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

            try {
                const res = await fetch(`/comments/${editingCommentId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ comment: newText })
                });

                const data = await res.json();
                if (data.success) {
                    document.querySelector(`[data-comment-id="${editingCommentId}"] .comment-text`).textContent = newText;
                    bootstrap.Modal.getInstance(document.getElementById('editCommentModal')).hide();
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Comment updated!',
                        showConfirmButton: false,
                        timer: 1200
                    });
                } else {
                    Swal.fire('Error', data.message || 'Failed to update', 'error');
                }
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'Something went wrong', 'error');
            }

            this.disabled = false;
            this.innerHTML = 'Save Changes';
        });

        // Delete comment
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-comment')) {
                const commentId = e.target.closest('.delete-comment').dataset.commentId;
                
                Swal.fire({
                    title: 'Delete comment?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const res = await fetch(`/comments/${commentId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                }
                            });

                            const data = await res.json();
                            if (data.success) {
                                document.querySelector(`[data-comment-id="${commentId}"]`).remove();
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Comment deleted!',
                                    showConfirmButton: false,
                                    timer: 1200
                                });
                            } else {
                                Swal.fire('Error', data.message || 'Failed to delete', 'error');
                            }
                        } catch (error) {
                            console.error(error);
                            Swal.fire('Error', 'Something went wrong', 'error');
                        }
                    }
                });
            }
        });

        // Submit reply
        document.addEventListener('click', function(e) {
            if (e.target.closest('.submit-reply')) {
                const commentId = e.target.closest('.submit-reply').dataset.commentId;
                const replyText = document.querySelector(`#reply-form-${commentId} .reply-textarea`).value.trim();
                
                if (!replyText) {
                    Swal.fire('Error', 'Please write a reply', 'warning');
                    return;
                }

                const btn = e.target.closest('.submit-reply');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Replying...';

                fetch(`/comments/${commentId}/replies`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ comment: replyText })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Add reply to DOM without reloading
                        const newReply = data.reply;
                        const userInitial = (newReply.user_name && newReply.user_name.length > 0) 
                            ? newReply.user_name[0].toUpperCase() 
                            : 'U';
                        const repliesContainer = document.querySelector(`[data-comment-id="${commentId}"] .replies-container`) || 
                                                document.createElement('div');
                        
                        if (!repliesContainer.parentElement) {
                            const replyContainer = document.createElement('div');
                            replyContainer.className = 'replies-container ms-5';
                            document.querySelector(`[data-comment-id="${commentId}"]`).appendChild(replyContainer);
                        }
                        
                        const newReplyHtml = `
                            <div class="reply-item mb-3 pb-3 border-bottom" data-reply-id="${newReply.id}">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <div class="avatar avatar-sm ${newReply.sender_type === 'customer' ? 'avatar-primary' : 'avatar-success'}">
                                            <span class="avatar-initial rounded-circle">${userInitial}</span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-2">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <div>
                                                <span class="fw-semibold" style="font-size: 0.9rem;">${newReply.user_name}</span>
                                                <small class="text-muted ms-2">just now</small>
                                            </div>
                                            <div class="reply-actions">
                                                <button class="btn btn-sm btn-link text-primary edit-reply" data-reply-id="${newReply.id}" title="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <button class="btn btn-sm btn-link text-danger delete-reply" data-reply-id="${newReply.id}" title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <p class="mb-0 reply-text" style="font-size: 0.95rem;">${newReply.comment}</p>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        repliesContainer.insertAdjacentHTML('beforeend', newReplyHtml);
                        document.querySelector(`#reply-form-${commentId}`).style.display = 'none';
                        document.querySelector(`#reply-form-${commentId} .reply-textarea`).value = '';
                        
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Reply posted!',
                            showConfirmButton: false,
                            timer: 1200
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Failed to post reply', 'error');
                    }
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire('Error', 'Something went wrong', 'error');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Reply';
                });
            }
        });

        // Edit reply
        document.addEventListener('click', function(e) {
            if (e.target.closest('.edit-reply')) {
                const replyId = e.target.closest('.edit-reply').dataset.replyId;
                const replyText = document.querySelector(`[data-reply-id="${replyId}"] .reply-text`).textContent;
                
                editingReplyId = replyId;
                document.getElementById('edit-reply-textarea').value = replyText;
                
                const modal = new bootstrap.Modal(document.getElementById('editReplyModal'));
                modal.show();
            }
        });

        // Save reply edit
        document.getElementById('save-reply-edit')?.addEventListener('click', async function() {
            const newText = document.getElementById('edit-reply-textarea').value.trim();
            if (!newText) {
                Swal.fire('Error', 'Reply cannot be empty', 'warning');
                return;
            }

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

            try {
                const res = await fetch(`/replies/${editingReplyId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ comment: newText })
                });

                const data = await res.json();
                if (data.success) {
                    // Check if this is a nested reply
                    const isNested = document.getElementById('edit-reply-textarea').dataset.isNestedReply === 'true';
                    if (isNested) {
                        document.querySelector(`[data-nested-reply-id="${editingReplyId}"] p`).textContent = newText;
                    } else {
                        document.querySelector(`[data-reply-id="${editingReplyId}"] .reply-text`).textContent = newText;
                    }
                    
                    bootstrap.Modal.getInstance(document.getElementById('editReplyModal')).hide();
                    document.getElementById('edit-reply-textarea').dataset.isNestedReply = 'false';
                    
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Reply updated!',
                        showConfirmButton: false,
                        timer: 1200
                    });
                } else {
                    Swal.fire('Error', data.message || 'Failed to update', 'error');
                }
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'Something went wrong', 'error');
            }

            this.disabled = false;
            this.innerHTML = 'Save Changes';
        });

        // Delete reply
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-reply')) {
                const replyId = e.target.closest('.delete-reply').dataset.replyId;
                
                Swal.fire({
                    title: 'Delete reply?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const res = await fetch(`/replies/${replyId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                }
                            });

                            const data = await res.json();
                            if (data.success) {
                                document.querySelector(`[data-reply-id="${replyId}"]`).remove();
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Reply deleted!',
                                    showConfirmButton: false,
                                    timer: 1200
                                });
                            } else {
                                Swal.fire('Error', data.message || 'Failed to delete', 'error');
                            }
                        } catch (error) {
                            console.error(error);
                            Swal.fire('Error', 'Something went wrong', 'error');
                        }
                    }
                });
            }
        });

        // Toggle nested reply form
        document.addEventListener('click', function(e) {
            if (e.target.closest('.nested-reply-toggle')) {
                const replyId = e.target.closest('.nested-reply-toggle').dataset.replyId;
                const nestedReplyForm = document.getElementById(`nested-reply-form-${replyId}`);
                if (nestedReplyForm) {
                    nestedReplyForm.style.display = nestedReplyForm.style.display === 'none' ? 'block' : 'none';
                }
            }
        });

        // Cancel nested reply
        document.addEventListener('click', function(e) {
            if (e.target.closest('.cancel-nested-reply')) {
                const replyId = e.target.closest('.cancel-nested-reply').dataset.replyId;
                const nestedReplyForm = document.getElementById(`nested-reply-form-${replyId}`);
                if (nestedReplyForm) {
                    nestedReplyForm.style.display = 'none';
                    nestedReplyForm.querySelector('.nested-reply-textarea').value = '';
                }
            }
        });

        // Submit nested reply
        document.addEventListener('click', function(e) {
            if (e.target.closest('.submit-nested-reply')) {
                const replyId = e.target.closest('.submit-nested-reply').dataset.replyId;
                const nestedReplyText = document.querySelector(`#nested-reply-form-${replyId} .nested-reply-textarea`).value.trim();
                
                if (!nestedReplyText) {
                    Swal.fire('Error', 'Please write a reply', 'warning');
                    return;
                }

                const btn = e.target.closest('.submit-nested-reply');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Replying...';

                fetch(`/replies/${replyId}/nested-replies`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ comment: nestedReplyText })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const newNestedReply = data.reply;
                        const userInitial = (newNestedReply.user_name && newNestedReply.user_name.length > 0) 
                            ? newNestedReply.user_name[0].toUpperCase() 
                            : 'U';
                        
                        // Find or create nested-replies container
                        let nestedRepliesContainer = document.querySelector(`[data-reply-id="${replyId}"] .nested-replies-container`);
                        if (!nestedRepliesContainer) {
                            nestedRepliesContainer = document.createElement('div');
                            nestedRepliesContainer.className = 'nested-replies-container ms-4 mt-2';
                            document.querySelector(`[data-reply-id="${replyId}"]`).appendChild(nestedRepliesContainer);
                        }

                        const newNestedReplyHtml = `
                            <div class="nested-reply-item mb-2 pb-2 border-start ps-2" data-nested-reply-id="${newNestedReply.id}" style="border-color: #dee2e6;">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <div class="avatar avatar-xs ${newNestedReply.sender_type === 'customer' ? 'avatar-primary' : 'avatar-success'}" style="width: 28px; height: 28px;">
                                            <span class="avatar-initial rounded-circle" style="font-size: 0.7rem;">${userInitial}</span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-2">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <div>
                                                <small class="fw-semibold">${newNestedReply.user_name}</small>
                                                <small class="text-muted ms-2">just now</small>
                                            </div>
                                            <div class="nested-reply-actions" style="font-size: 0.85rem;">
                                                <button class="btn btn-sm btn-link text-primary edit-nested-reply" data-nested-reply-id="${newNestedReply.id}" title="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <button class="btn btn-sm btn-link text-danger delete-nested-reply" data-nested-reply-id="${newNestedReply.id}" title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <p class="mb-0" style="font-size: 0.85rem;">${newNestedReply.comment}</p>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        nestedRepliesContainer.insertAdjacentHTML('beforeend', newNestedReplyHtml);
                        document.querySelector(`#nested-reply-form-${replyId}`).style.display = 'none';
                        document.querySelector(`#nested-reply-form-${replyId} .nested-reply-textarea`).value = '';
                        
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Nested reply posted!',
                            showConfirmButton: false,
                            timer: 1200
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Failed to post nested reply', 'error');
                    }
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire('Error', 'Something went wrong', 'error');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Reply';
                });
            }
        });

        // Delete nested reply
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-nested-reply')) {
                const nestedReplyId = e.target.closest('.delete-nested-reply').dataset.nestedReplyId;
                
                Swal.fire({
                    title: 'Delete reply?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const res = await fetch(`/replies/${nestedReplyId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                }
                            });

                            const data = await res.json();
                            if (data.success) {
                                document.querySelector(`[data-nested-reply-id="${nestedReplyId}"]`).remove();
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Nested reply deleted!',
                                    showConfirmButton: false,
                                    timer: 1200
                                });
                            } else {
                                Swal.fire('Error', data.message || 'Failed to delete', 'error');
                            }
                        } catch (error) {
                            console.error(error);
                            Swal.fire('Error', 'Something went wrong', 'error');
                        }
                    }
                });
            }
        });

        // Edit nested reply
        document.addEventListener('click', function(e) {
            if (e.target.closest('.edit-nested-reply')) {
                const nestedReplyId = e.target.closest('.edit-nested-reply').dataset.nestedReplyId;
                const nestedReplyText = document.querySelector(`[data-nested-reply-id="${nestedReplyId}"] p`).textContent;
                
                editingReplyId = nestedReplyId;
                document.getElementById('edit-reply-textarea').value = nestedReplyText;
                document.getElementById('edit-reply-textarea').dataset.isNestedReply = 'true';
                
                const modal = new bootstrap.Modal(document.getElementById('editReplyModal'));
                modal.show();
            }
        });
    });
</script>
