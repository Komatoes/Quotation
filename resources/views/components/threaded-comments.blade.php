{{-- Threaded Comments Component — Plain, no modals — supports add, edit, delete, reply inline --}}
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Comments & Feedback</h5>
    </div>
    <div class="card-body">
        <ul id="comments-list" class="list-unstyled">
            @forelse($comments ?? collect() as $comment)
                <li class="mb-3" data-comment-id="{{ $comment->id }}">
                    <div class="d-flex mb-2">
                        <div class="flex-shrink-0"><div class="avatar {{ $comment->sender_type === 'customer' ? 'avatar-primary' : 'avatar-success' }}"><span class="avatar-initial rounded-circle">{{ isset($comment->user_name) ? strtoupper(substr($comment->user_name,0,1)) : 'U' }}</span></div></div>
                        <div class="flex-grow-1 ms-3">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div><span class="fw-semibold">{{ $comment->user_name ?? ($comment->sender_type === 'customer' ? 'Customer' : 'Admin') }}</span><small class="text-muted ms-2">{{ $comment->created_at->diffForHumans() }}</small></div>
                                <div class="comment-actions">
                                    @if(isset($publicToken))
                                        @if($comment->user_id === null)
                                            <button class="btn btn-sm btn-link text-primary edit-comment" data-comment-id="{{ $comment->id }}">Edit</button>
                                            <button class="btn btn-sm btn-link text-danger delete-comment" data-comment-id="{{ $comment->id }}">Delete</button>
                                        @endif
                                    @else
                                        {{-- Admin view: owner OR public comment --}}
                                        @if((Auth::check() && Auth::id() == $comment->user_id) || $comment->user_id === null)
                                            <button class="btn btn-sm btn-link text-primary edit-comment" data-comment-id="{{ $comment->id }}">Edit</button>
                                            <button class="btn btn-sm btn-link text-danger delete-comment" data-comment-id="{{ $comment->id }}">Delete</button>
                                        @endif
                                    @endif
                                    <button class="btn btn-sm btn-link text-secondary reply-toggle" data-comment-id="{{ $comment->id }}">Reply</button>
                                </div>
                            </div>
                            <p class="mb-2 comment-text">{{ $comment->comment }}</p>

                            {{-- Inline edit form (hidden) --}}
                            <div class="edit-comment-form mt-2" id="edit-comment-form-{{ $comment->id }}" style="display:none;">
                                <textarea class="form-control mb-2 edit-comment-text" data-comment-id="{{ $comment->id }}">{{ $comment->comment }}</textarea>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-primary save-edit-comment" data-comment-id="{{ $comment->id }}">Save</button>
                                    <button class="btn btn-sm btn-secondary cancel-edit-comment" data-comment-id="{{ $comment->id }}">Cancel</button>
                                </div>
                            </div>

                            {{-- Reply form (hidden) --}}
                            <div class="reply-form-container mt-2" id="reply-form-{{ $comment->id }}" style="display:none;">
                                <textarea class="form-control mb-2 reply-textarea" rows="2" placeholder="Write a reply..."></textarea>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-primary submit-reply" data-comment-id="{{ $comment->id }}">Reply</button>
                                    <button class="btn btn-sm btn-secondary cancel-reply" data-comment-id="{{ $comment->id }}">Cancel</button>
                                </div>
                            </div>

                            {{-- Replies (nested list-group) --}}
                            @if($comment->replies && $comment->replies->count())
                                <ul class="list-group mt-3 ms-4">
                                    @foreach($comment->replies as $reply)
                                        <li class="list-group-item border-0 p-0 mb-3" data-reply-id="{{ $reply->id }}">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-2">
                                                    <div class="avatar avatar-sm {{ $reply->sender_type === 'customer' ? 'avatar-primary' : 'avatar-success' }}">
                                                        <span class="avatar-initial rounded-circle">{{ isset($reply->user_name) ? strtoupper(substr($reply->user_name,0,1)) : 'U' }}</span>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <span class="fw-semibold">{{ $reply->user_name ?? ($reply->sender_type === 'customer' ? 'Customer' : 'Admin') }}</span>
                                                            <small class="text-muted ms-2">{{ $reply->created_at->diffForHumans() }}</small>
                                                        </div>
                                                        <div class="reply-actions">
                                                            @if(isset($publicToken))
                                                                {{-- Public view: ONLY public replies (user_id === null) --}}
                                                                @if($reply->user_id === null)
                                                                    <button class="btn btn-sm btn-link text-primary edit-reply" data-reply-id="{{ $reply->id }}">Edit</button>
                                                                    <button class="btn btn-sm btn-link text-danger delete-reply" data-reply-id="{{ $reply->id }}">Delete</button>
                                                                @endif
                                                            @else
                                                                {{-- Admin view: owner OR public reply --}}
                                                                @if((Auth::check() && Auth::id() == $reply->user_id) || $reply->user_id === null)
                                                                    <button class="btn btn-sm btn-link text-primary edit-reply" data-reply-id="{{ $reply->id }}">Edit</button>
                                                                    <button class="btn btn-sm btn-link text-danger delete-reply" data-reply-id="{{ $reply->id }}">Delete</button>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <p class="mb-1 reply-text">{{ $reply->comment }}</p>

                                                    {{-- Inline edit reply form --}}
                                                    <div class="edit-reply-form mt-2" id="edit-reply-form-{{ $reply->id }}" style="display:none;">
                                                        <textarea class="form-control mb-2 edit-reply-text" data-reply-id="{{ $reply->id }}">{{ $reply->comment }}</textarea>
                                                        <div class="d-flex gap-2">
                                                            <button class="btn btn-sm btn-primary save-edit-reply" data-reply-id="{{ $reply->id }}">Save</button>
                                                            <button class="btn btn-sm btn-secondary cancel-edit-reply" data-reply-id="{{ $reply->id }}">Cancel</button>
                                                        </div>
                                                    </div>

                                                    {{-- Nested replies (if any) --}}
                                                    @if($reply->nestedReplies && $reply->nestedReplies->count())
                                                        <ul class="list-group mt-2 ms-3">
                                                            @foreach($reply->nestedReplies as $nreply)
                                                                <li class="list-group-item border-0 p-0 mb-2" data-nested-reply-id="{{ $nreply->id }}">
                                                                    <div class="d-flex">
                                                                        <div class="flex-shrink-0 me-2">
                                                                            <div class="avatar avatar-xs {{ $nreply->sender_type === 'customer' ? 'avatar-primary' : 'avatar-success' }}">
                                                                                <span class="avatar-initial rounded-circle">{{ isset($nreply->user_name) ? strtoupper(substr($nreply->user_name,0,1)) : 'U' }}</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="flex-grow-1">
                                                                            <div class="d-flex justify-content-between align-items-start">
                                                                                <div>
                                                                                    <span class="fw-semibold">{{ $nreply->user_name ?? ($nreply->sender_type === 'customer' ? 'Customer' : 'Admin') }}</span>
                                                                                    <small class="text-muted ms-2">{{ $nreply->created_at->diffForHumans() }}</small>
                                                                                </div>
                                                                                <div class="nested-reply-actions">
                                                                                    @if(isset($publicToken))
                                                                                        {{-- Public view: ONLY public replies (user_id === null) --}}
                                                                                        @if($nreply->user_id === null)
                                                                                            <button class="btn btn-sm btn-link text-primary edit-nested-reply" data-nested-reply-id="{{ $nreply->id }}">Edit</button>
                                                                                            <button class="btn btn-sm btn-link text-danger delete-nested-reply" data-nested-reply-id="{{ $nreply->id }}">Delete</button>
                                                                                        @endif
                                                                                    @else
                                                                                        {{-- Admin view: owner OR public reply --}}
                                                                                        @if((Auth::check() && Auth::id() == $nreply->user_id) || $nreply->user_id === null)
                                                                                            <button class="btn btn-sm btn-link text-primary edit-nested-reply" data-nested-reply-id="{{ $nreply->id }}">Edit</button>
                                                                                            <button class="btn btn-sm btn-link text-danger delete-nested-reply" data-nested-reply-id="{{ $nreply->id }}">Delete</button>
                                                                                        @endif
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                            <p class="mb-0">{{ $nreply->comment }}</p>
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif

                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                        </div>
                    </div>
                </li>
            @empty
            @endforelse
        </ul>

        {{-- Add main comment form --}}
        <div class="mt-4 pt-3 border-top d-flex gap-3">
            <div class="flex-shrink-0">
                @php
                    $mainInitial = 'U';
                    // In public view, ALWAYS use client name, never use Auth
                    if (isset($publicToken) && ($comments ?? collect())->first()) {
                        $first = ($comments->first()->user_name ?? null) ?: 'C';
                        $mainInitial = strtoupper(substr($first, 0, 1));
                    } elseif (Auth::check()) {
                        $mainInitial = strtoupper(substr(Auth::user()->name, 0, 1));
                    }
                @endphp
                <div class="avatar avatar-primary"><span class="avatar-initial rounded-circle">{{ $mainInitial }}</span></div>
            </div>
            <div class="flex-grow-1">
                <textarea id="main-comment-input" class="form-control mb-2" rows="3" placeholder="Share your thoughts..."></textarea>
                <button id="submit-main-comment" class="btn btn-primary"><i class="fa-solid fa-paper-plane me-1"></i> Post Comment</button>
            </div>
        </div>
    </div>
</div>

<script>

(function(){
    const quotationId = "{{ $quotationId ?? '' }}";
    const publicToken = "{{ $publicToken ?? '' }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    // endpoints
    // Use passed-in endpoint if available, else fallback to dynamic
    const commentEndpoint = "{{ $commentEndpoint ?? '' }}" || (publicToken ? `/quotation/public/${publicToken}/comment` : `/quotation/${quotationId}/comments`);

    // Helpers
    function qs(selector, ctx=document){ return ctx.querySelector(selector); }
    function qsa(selector, ctx=document){ return Array.from((ctx||document).querySelectorAll(selector)); }

    // Toggle reply form
    document.addEventListener('click', function(e){
        const t = e.target.closest('.reply-toggle');
        if(!t) return;
        const id = t.dataset.commentId;
        const form = document.getElementById(`reply-form-${id}`);
        if(form) form.style.display = form.style.display === 'none' ? 'block' : 'none';
    });

    // Cancel reply
    document.addEventListener('click', function(e){
        const t = e.target.closest('.cancel-reply');
        if(!t) return;
        const id = t.dataset.commentId;
        const form = document.getElementById(`reply-form-${id}`);
        if(form){ form.style.display='none'; form.querySelector('.reply-textarea').value=''; }
    });

    // Submit main comment
    document.getElementById('submit-main-comment')?.addEventListener('click', async function(){
        const btn = this;
        const text = (qs('#main-comment-input').value||'').trim();
        if(!text) return Swal.fire('Error','Please write a comment','warning');
        btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Posting...';
        try{
            const res = await fetch(commentEndpoint, {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
                body: JSON.stringify({ comment: text })
            });
            const data = await res.json();
            if(data.success && data.comment){
                // append new comment at end
                const commentsList = document.getElementById('comments-list');
                commentsList.insertAdjacentHTML('beforeend', buildCommentHtml(data.comment));
                qs('#main-comment-input').value='';
                Swal.fire({toast:true,position:'top-end',icon:'success',title:'Comment posted!',showConfirmButton:false,timer:1200});
            } else {
                Swal.fire('Error',data.message||'Failed to post comment','error');
            }
        }catch(err){ console.error(err); Swal.fire('Error','Something went wrong','error'); }
        btn.disabled=false; btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Post Comment';
    });

    // Submit reply (delegated)
    document.addEventListener('click', async function(e){
        const t = e.target.closest('.submit-reply');
        if(!t) return;
        const commentId = t.dataset.commentId;
        const textarea = qs(`#reply-form-${commentId} .reply-textarea`);
        const text = (textarea.value||'').trim();
        if(!text) return Swal.fire('Error','Please write a reply','warning');
        const btn = t; btn.disabled=true; btn.innerHTML='<span class="spinner-border spinner-border-sm me-2"></span>Replying...';
        try{
            // Use public endpoint if in public view, else use admin endpoint
            const replyEndpoint = publicToken 
                ? `/quotation/public/${publicToken}/comments/${commentId}/reply`
                : `/comments/${commentId}/replies`;
            const res = await fetch(replyEndpoint,{
                method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body: JSON.stringify({ comment: text })
            });
            const data = await res.json();
            if(data.success && data.reply){
                const container = qs(`[data-comment-id="${commentId}"] .replies-container`);
                if(container) container.insertAdjacentHTML('beforeend', buildReplyHtml(data.reply));
                textarea.value=''; qs(`#reply-form-${commentId}`).style.display='none';
                Swal.fire({toast:true,position:'top-end',icon:'success',title:'Reply posted!',showConfirmButton:false,timer:1200});
            } else { Swal.fire('Error',data.message||'Failed to post reply','error'); }
        }catch(err){ console.error(err); Swal.fire('Error','Something went wrong','error'); }
        btn.disabled=false; btn.innerHTML='Reply';
    });

    // Edit comment - toggle inline edit form
    document.addEventListener('click', function(e){
        const t = e.target.closest('.edit-comment'); if(!t) return;
        const id = t.dataset.commentId; const form = qs(`#edit-comment-form-${id}`); if(!form) return; form.style.display = form.style.display === 'none' ? 'block' : 'none';
    });

    // Cancel edit comment
    document.addEventListener('click', function(e){
        const t = e.target.closest('.cancel-edit-comment'); if(!t) return;
        const id = t.dataset.commentId; const form = qs(`#edit-comment-form-${id}`); if(!form) return; form.style.display='none';
    });

    // Save edit comment
    document.addEventListener('click', async function(e){
        const t = e.target.closest('.save-edit-comment'); if(!t) return;
        const id = t.dataset.commentId; const textarea = qs(`#edit-comment-form-${id} .edit-comment-text`);
        const newText = (textarea.value||'').trim(); if(!newText) return Swal.fire('Error','Comment cannot be empty','warning');
        t.disabled=true; t.innerHTML='Saving...';
        try{
            // Use public endpoint if in public view, else use admin endpoint
            const updateEndpoint = publicToken 
                ? `/quotation/public/${publicToken}/comments/${id}`
                : `/comments/${id}`;
            const res = await fetch(updateEndpoint, { method:'PUT', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body: JSON.stringify({ comment: newText }) });
            const data = await res.json();
            if(data.success){ qs(`[data-comment-id="${id}"] .comment-text`).textContent = newText; qs(`#edit-comment-form-${id}`).style.display='none'; Swal.fire({toast:true,position:'top-end',icon:'success',title:'Updated',showConfirmButton:false,timer:1200}); }
            else Swal.fire('Error',data.message||'Failed to update','error');
        }catch(err){ console.error(err); Swal.fire('Error','Something went wrong','error'); }
        t.disabled=false; t.innerHTML='Save';
    });

    // Delete comment
    document.addEventListener('click', function(e){
        const t = e.target.closest('.delete-comment'); if(!t) return;
        const id = t.dataset.commentId;
        Swal.fire({title:'Delete comment?',text:'This cannot be undone',icon:'warning',showCancelButton:true,confirmButtonColor:'#d33',cancelButtonColor:'#6c757d',confirmButtonText:'Yes, delete it'}).then(async (res)=>{
            if(!res.isConfirmed) return;
            try{
                // Use public endpoint if in public view, else use admin endpoint
                const deleteEndpoint = publicToken 
                    ? `/quotation/public/${publicToken}/comments/${id}`
                    : `/comments/${id}`;
                const resp = await fetch(deleteEndpoint,{ method:'DELETE', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'} });
                const data = await resp.json(); if(data.success){ qs(`[data-comment-id="${id}"]`).remove(); Swal.fire({toast:true,position:'top-end',icon:'success',title:'Deleted',showConfirmButton:false,timer:1200}); } else Swal.fire('Error',data.message||'Failed to delete','error');
            }catch(err){ console.error(err); Swal.fire('Error','Something went wrong','error'); }
        });
    });

    // Edit reply - toggle
    document.addEventListener('click', function(e){ const t = e.target.closest('.edit-reply'); if(!t) return; const id = t.dataset.replyId; const form = qs(`#edit-reply-form-${id}`); if(!form) return; form.style.display = form.style.display === 'none' ? 'block' : 'none'; });
    // Cancel edit reply
    document.addEventListener('click', function(e){ const t = e.target.closest('.cancel-edit-reply'); if(!t) return; const id = t.dataset.replyId; const form = qs(`#edit-reply-form-${id}`); if(!form) return; form.style.display='none'; });

    // Save edit reply
    document.addEventListener('click', async function(e){
        const t = e.target.closest('.save-edit-reply'); if(!t) return;
        const id = t.dataset.replyId; const textarea = qs(`#edit-reply-form-${id} .edit-reply-text`);
        const newText = (textarea.value||'').trim(); if(!newText) return Swal.fire('Error','Reply cannot be empty','warning');
        t.disabled=true; t.innerHTML='Saving...';
        try{
            // Use public endpoint if in public view, else use admin endpoint
            const updateEndpoint = publicToken 
                ? `/quotation/public/${publicToken}/replies/${id}`
                : `/replies/${id}`;
            const res = await fetch(updateEndpoint, { method:'PUT', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body: JSON.stringify({ comment: newText }) });
            const data = await res.json(); if(data.success){ const isNested = qs(`[data-nested-reply-id="${id}"]`); if(isNested) qs(`[data-nested-reply-id="${id}"] p`).textContent = newText; else qs(`[data-reply-id="${id}"] .reply-text`).textContent = newText; qs(`#edit-reply-form-${id}`).style.display='none'; Swal.fire({toast:true,position:'top-end',icon:'success',title:'Updated',showConfirmButton:false,timer:1200}); } else Swal.fire('Error',data.message||'Failed to update','error');
        }catch(err){ console.error(err); Swal.fire('Error','Something went wrong','error'); }
        t.disabled=false; t.innerHTML='Save';
    });

    // Delete reply
    document.addEventListener('click', function(e){ const t = e.target.closest('.delete-reply, .delete-nested-reply'); if(!t) return; const id = t.dataset.replyId || t.dataset.nestedReplyId; Swal.fire({title:'Delete reply?',text:'This cannot be undone',icon:'warning',showCancelButton:true,confirmButtonColor:'#d33',cancelButtonColor:'#6c757d',confirmButtonText:'Yes, delete it'}).then(async (res)=>{ if(!res.isConfirmed) return; try{ const deleteEndpoint = publicToken ? `/quotation/public/${publicToken}/replies/${id}` : `/replies/${id}`; const resp = await fetch(deleteEndpoint,{ method:'DELETE', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'} }); const data = await resp.json(); if(data.success){ qs(`[data-reply-id="${id}"]`)?.remove(); qs(`[data-nested-reply-id="${id}"]`)?.remove(); Swal.fire({toast:true,position:'top-end',icon:'success',title:'Deleted',showConfirmButton:false,timer:1200}); } else Swal.fire('Error',data.message||'Failed to delete','error'); }catch(err){ console.error(err); Swal.fire('Error','Something went wrong','error'); } }); });

    // Builders for HTML snippets (simple)
    function buildCommentHtml(c){
        // Permission check: show edit/delete buttons conditionally
        // In public view: ONLY for public comments (user_id === null)
        // In admin view: for owner OR public comments
        let editDeleteButtons = '';
        if (publicToken) {
            // Public view: only for public comments (user_id === null)
            if (c.user_id === null) {
                editDeleteButtons = `<button class="btn btn-sm btn-link text-primary edit-comment" data-comment-id="${c.id}">Edit</button>\n                            <button class="btn btn-sm btn-link text-danger delete-comment" data-comment-id="${c.id}">Delete</button>`;
            }
        } else {
            // Admin view: show for owner OR public comments
            editDeleteButtons = `<button class="btn btn-sm btn-link text-primary edit-comment" data-comment-id="${c.id}">Edit</button>\n                            <button class="btn btn-sm btn-link text-danger delete-comment" data-comment-id="${c.id}">Delete</button>`;
        }
        
        return `\n        <div class="comment-thread mb-4 border-start border-2 border-primary ps-3" data-comment-id="${c.id}">\n            <div class="d-flex mb-2">\n                <div class="flex-shrink-0"><div class="avatar ${c.sender_type==='customer'?'avatar-primary':'avatar-success'}"><span class="avatar-initial rounded-circle">${(c.user_name||'U').charAt(0).toUpperCase()}</span></div></div>\n                <div class="flex-grow-1 ms-3">\n                    <div class="d-flex justify-content-between align-items-start mb-1">\n                        <div><span class="fw-semibold">${c.user_name}</span><small class="text-muted ms-2">just now</small></div>\n                        <div class="comment-actions">\n                            ${editDeleteButtons}\n                            <button class="btn btn-sm btn-link text-secondary reply-toggle" data-comment-id="${c.id}">Reply</button>\n                        </div>\n                    </div>\n                    <p class="mb-2 comment-text">${escapeHtml(c.comment)}</p>\n\n                    <!-- Inline edit form -->\n                    <div class="edit-comment-form mt-2" id="edit-comment-form-${c.id}" style="display:none;">\n                        <textarea class="form-control mb-2 edit-comment-text" data-comment-id="${c.id}">${escapeHtml(c.comment)}</textarea>\n                        <div class="d-flex gap-2">\n                            <button class="btn btn-sm btn-primary save-edit-comment" data-comment-id="${c.id}">Save</button>\n                            <button class="btn btn-sm btn-secondary cancel-edit-comment" data-comment-id="${c.id}">Cancel</button>\n                        </div>\n                    </div>\n\n                    <div class="reply-form-container mt-2" id="reply-form-${c.id}" style="display:none;">\n                        <textarea class="form-control mb-2 reply-textarea" rows="2"></textarea>\n                        <div class="d-flex gap-2">\n                            <button class="btn btn-sm btn-primary submit-reply" data-comment-id="${c.id}">Reply</button>\n                            <button class="btn btn-sm btn-secondary cancel-reply" data-comment-id="${c.id}">Cancel</button>\n                        </div>\n                    </div>\n                    <div class="replies-container mt-3"></div>\n                </div>\n            </div>\n        </div>\n        `;
    }

    function buildReplyHtml(r){
        // Permission check: show edit/delete buttons conditionally
        // In public view: ONLY for public replies (user_id === null)
        // In admin view: for owner OR public replies
        let editDeleteButtons = '';
        if (publicToken) {
            // Public view: only for public replies (user_id === null)
            if (r.user_id === null) {
                editDeleteButtons = `<button class="btn btn-sm btn-link text-primary edit-reply" data-reply-id="${r.id}">Edit</button><button class="btn btn-sm btn-link text-danger delete-reply" data-reply-id="${r.id}">Delete</button>`;
            }
        } else {
            // Admin view: show for owner OR public replies
            editDeleteButtons = `<button class="btn btn-sm btn-link text-primary edit-reply" data-reply-id="${r.id}">Edit</button><button class="btn btn-sm btn-link text-danger delete-reply" data-reply-id="${r.id}">Delete</button>`;
        }
        
        return `\n        <div class="reply-item mb-3" data-reply-id="${r.id}">\n            <div class="d-flex">\n                <div class="flex-shrink-0"><div class="avatar avatar-sm ${r.sender_type==='customer'?'avatar-primary':'avatar-success'}"><span class="avatar-initial rounded-circle">${(r.user_name||'U').charAt(0).toUpperCase()}</span></div></div>\n                <div class="flex-grow-1 ms-2">\n                    <div class="d-flex justify-content-between align-items-start mb-1">\n                        <div><span class="fw-semibold">${r.user_name}</span><small class="text-muted ms-2">just now</small></div>\n                        <div class="reply-actions">${editDeleteButtons}</div>\n                    </div>\n                    <p class="mb-1 reply-text">${escapeHtml(r.comment)}</p>\n                    <div class="edit-reply-form mt-2" id="edit-reply-form-${r.id}" style="display:none;">\n                        <textarea class="form-control mb-2 edit-reply-text" data-reply-id="${r.id}">${escapeHtml(r.comment)}</textarea>\n                        <div class="d-flex gap-2">\n                            <button class="btn btn-sm btn-primary save-edit-reply" data-reply-id="${r.id}">Save</button>\n                            <button class="btn btn-sm btn-secondary cancel-edit-reply" data-reply-id="${r.id}">Cancel</button>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n        `;
    }

    function escapeHtml(unsafe){ return String(unsafe).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }
})();
</script>
