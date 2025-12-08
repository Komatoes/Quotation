@php
    // If a cookie exists for public comment session token, expose it to server rendering
    $publicSessionToken = request()->cookie('publicCommentSessionToken') ?? null;
@endphp

<script>
    // Expose current user ID and auth status to JS for ownership checks
    window.CURRENT_USER_ID = @json(optional(auth()->user())->id);
    window.IS_AUTHENTICATED = @json(auth()->check());
    
    // Helper to read/write cookies from JS
    function getCookie(name) {
        const v = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
        return v ? v.pop() : null;
    }
    function setCookie(name, value, days) {
        let expires = '';
        if (days) {
            const date = new Date(); date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = '; expires=' + date.toUTCString();
        }
        document.cookie = name + '=' + (value || '')  + expires + '; path=/';
    }

    // Generate or retrieve a session token for public customers
    // This allows unauthenticated users to edit/delete their own comments
    if (!window.SESSION_TOKEN) {
        // 1) Try localStorage
        window.SESSION_TOKEN = localStorage.getItem('publicCommentSessionToken');
        // 2) Fallback to cookie if localStorage empty (helps across page loads/devices where cookie persisted)
        if (!window.SESSION_TOKEN) {
            window.SESSION_TOKEN = getCookie('publicCommentSessionToken');
        }
        if (!window.SESSION_TOKEN) {
            // Generate a new one if not present
            window.SESSION_TOKEN = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('publicCommentSessionToken', window.SESSION_TOKEN);
            // Also set a long-lived cookie so server-side rendering can see it on next requests
            setCookie('publicCommentSessionToken', window.SESSION_TOKEN, 3650); // ~10 years
        }
    }
</script>

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
                                        {{-- Public view: server-side render edit/delete only when the request cookie matches the comment's session token --}}
                                        @if($publicSessionToken && $publicSessionToken === $comment->session_token)
                                            <button class="btn btn-sm btn-link text-primary edit-comment" data-comment-id="{{ $comment->id }}">Edit</button>
                                            <button class="btn btn-sm btn-link text-danger delete-comment" data-comment-id="{{ $comment->id }}">Delete</button>
                                        @endif
                                    @else
                                        {{-- Admin view: ONLY owner can edit/delete their own comment --}}
                                        @if(Auth::check() && Auth::id() == $comment->user_id)
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
                                                                {{-- Public view: server-side render edit/delete if cookie matches reply session_token --}}
                                                                @if($publicSessionToken && $publicSessionToken === $reply->session_token)
                                                                    <button class="btn btn-sm btn-link text-primary edit-reply" data-reply-id="{{ $reply->id }}">Edit</button>
                                                                    <button class="btn btn-sm btn-link text-danger delete-reply" data-reply-id="{{ $reply->id }}">Delete</button>
                                                                @endif
                                                            @else
                                                                {{-- Admin view: ONLY owner can edit/delete their own reply --}}
                                                                @if(Auth::check() && Auth::id() == $reply->user_id)
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
                                                                                        {{-- Public view: server-side render edit/delete if cookie matches nested reply session_token --}}
                                                                                        @if($publicSessionToken && $publicSessionToken === $nreply->session_token)
                                                                                            <button class="btn btn-sm btn-link text-primary edit-nested-reply" data-nested-reply-id="{{ $nreply->id }}">Edit</button>
                                                                                            <button class="btn btn-sm btn-link text-danger delete-nested-reply" data-nested-reply-id="{{ $nreply->id }}">Delete</button>
                                                                                        @endif
                                                                                    @else
                                                                                        {{-- Admin view: ONLY owner can edit/delete their own nested reply --}}
                                                                                        @if(Auth::check() && Auth::id() == $nreply->user_id)
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
    const commentsListEndpoint = "{{ $commentsEndpoint ?? '' }}" || (publicToken ? `/quotation/public/${publicToken}/comments` : `/quotations/${quotationId}/comments`);

    // Helpers
    function qs(selector, ctx=document){ return ctx.querySelector(selector); }
    function qsa(selector, ctx=document){ return Array.from((ctx||document).querySelectorAll(selector)); }

    // Load comments from server (especially important for public view to get session_token)
    async function loadCommentsFromServer() {
        if (!publicToken) return; // Only for public view
        try {
            const res = await fetch(commentsListEndpoint, {
                method: 'GET',
                headers: {
                    'X-Session-Token': window.SESSION_TOKEN,
                    'Accept': 'application/json'
                }
            });
            const comments = await res.json();
            if (Array.isArray(comments) && comments.length > 0) {
                // Clear existing comments
                const commentsList = qs('#comments-list');
                commentsList.innerHTML = '';
                // Re-render with session tokens included
                comments.forEach(c => {
                    commentsList.insertAdjacentHTML('beforeend', buildCommentHtml(c));
                });
            }
        } catch(err) {
            console.error('Error loading comments:', err);
        }
    }

    // Load comments on page load (for public view)
    document.addEventListener('DOMContentLoaded', function() {
        if (publicToken) {
            loadCommentsFromServer();
        }
    });

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
                headers: {
                    'Content-Type':'application/json',
                    'X-CSRF-TOKEN':csrfToken,
                    'X-Session-Token': window.SESSION_TOKEN,
                    'Accept':'application/json'
                },
                body: JSON.stringify({ comment: text })
            });
            const data = await res.json();
                if(data.success && data.comment){
                // Update session token if server returned one (for new users)
                if(data.session_token) {
                    window.SESSION_TOKEN = data.session_token;
                    localStorage.setItem('publicCommentSessionToken', window.SESSION_TOKEN);
                    // also set a long-lived cookie so server-side rendering can detect ownership on subsequent loads
                    if (typeof setCookie === 'function') setCookie('publicCommentSessionToken', window.SESSION_TOKEN, 3650);
                }
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
            const headers = {
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':csrfToken,
                'Accept':'application/json'
            };
            // Add session token if in public view
            if(publicToken) {
                headers['X-Session-Token'] = window.SESSION_TOKEN;
            }
            const res = await fetch(replyEndpoint, {
                method:'POST', headers: headers, body: JSON.stringify({ comment: text })
            });
            const data = await res.json();
                if(data.success && data.reply){
                // Update session token if server returned one (for new users)
                if(data.session_token) {
                    window.SESSION_TOKEN = data.session_token;
                    localStorage.setItem('publicCommentSessionToken', window.SESSION_TOKEN);
                    // also set cookie for persistence across reloads so server-side rendering can use it
                    if (typeof setCookie === 'function') setCookie('publicCommentSessionToken', window.SESSION_TOKEN, 3650);
                }
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
            const headers = {
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':csrfToken,
                'Accept':'application/json'
            };
            // Add session token if in public view
            if(publicToken) {
                headers['X-Session-Token'] = window.SESSION_TOKEN;
            }
            const res = await fetch(updateEndpoint, { method:'PUT', headers: headers, body: JSON.stringify({ comment: newText }) });
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
                const headers = {
                    'X-CSRF-TOKEN':csrfToken,
                    'Accept':'application/json'
                };
                // Add session token if in public view
                if(publicToken) {
                    headers['X-Session-Token'] = window.SESSION_TOKEN;
                }
                const resp = await fetch(deleteEndpoint, { method:'DELETE', headers: headers });
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
            const headers = {
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':csrfToken,
                'Accept':'application/json'
            };
            // Add session token if in public view
            if(publicToken) {
                headers['X-Session-Token'] = window.SESSION_TOKEN;
            }
            const res = await fetch(updateEndpoint, { method:'PUT', headers: headers, body: JSON.stringify({ comment: newText }) });
            const data = await res.json(); if(data.success){ const isNested = qs(`[data-nested-reply-id="${id}"]`); if(isNested) qs(`[data-nested-reply-id="${id}"] p`).textContent = newText; else qs(`[data-reply-id="${id}"] .reply-text`).textContent = newText; qs(`#edit-reply-form-${id}`).style.display='none'; Swal.fire({toast:true,position:'top-end',icon:'success',title:'Updated',showConfirmButton:false,timer:1200}); } else Swal.fire('Error',data.message||'Failed to update','error');
        }catch(err){ console.error(err); Swal.fire('Error','Something went wrong','error'); }
        t.disabled=false; t.innerHTML='Save';
    });

    // Delete reply
    document.addEventListener('click', function(e){ const t = e.target.closest('.delete-reply, .delete-nested-reply'); if(!t) return; const id = t.dataset.replyId || t.dataset.nestedReplyId; Swal.fire({title:'Delete reply?',text:'This cannot be undone',icon:'warning',showCancelButton:true,confirmButtonColor:'#d33',cancelButtonColor:'#6c757d',confirmButtonText:'Yes, delete it'}).then(async (res)=>{ if(!res.isConfirmed) return; try{ const deleteEndpoint = publicToken ? `/quotation/public/${publicToken}/replies/${id}` : `/replies/${id}`; const headers = {'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}; if(publicToken) { headers['X-Session-Token'] = window.SESSION_TOKEN; } const resp = await fetch(deleteEndpoint, { method:'DELETE', headers: headers }); const data = await resp.json(); if(data.success){ qs(`[data-reply-id="${id}"]`)?.remove(); qs(`[data-nested-reply-id="${id}"]`)?.remove(); Swal.fire({toast:true,position:'top-end',icon:'success',title:'Deleted',showConfirmButton:false,timer:1200}); } else Swal.fire('Error',data.message||'Failed to delete','error'); }catch(err){ console.error(err); Swal.fire('Error','Something went wrong','error'); } }); });

    // Builders for HTML snippets (simple)
    function buildCommentHtml(c){
        // Permission check: show edit/delete buttons only for owner
        let editDeleteButtons = '';
        
        // For authenticated users (admin/staff): show buttons if they own the comment
        if (window.IS_AUTHENTICATED && window.CURRENT_USER_ID === c.user_id) {
            editDeleteButtons = `<button class="btn btn-sm btn-link text-primary edit-comment" data-comment-id="${c.id}">Edit</button>
                            <button class="btn btn-sm btn-link text-danger delete-comment" data-comment-id="${c.id}">Delete</button>`;
        }
        // For public view: show buttons if session token matches
        else if (publicToken && window.SESSION_TOKEN && c.session_token && window.SESSION_TOKEN === c.session_token) {
            editDeleteButtons = `<button class="btn btn-sm btn-link text-primary edit-comment" data-comment-id="${c.id}">Edit</button>
                            <button class="btn btn-sm btn-link text-danger delete-comment" data-comment-id="${c.id}">Delete</button>`;
        }
        
        return `\n        <div class="comment-thread mb-4 border-start border-2 border-primary ps-3" data-comment-id="${c.id}">\n            <div class="d-flex mb-2">\n                <div class="flex-shrink-0"><div class="avatar ${c.sender_type==='customer'?'avatar-primary':'avatar-success'}"><span class="avatar-initial rounded-circle">${(c.user_name||'U').charAt(0).toUpperCase()}</span></div></div>\n                <div class="flex-grow-1 ms-3">\n                    <div class="d-flex justify-content-between align-items-start mb-1">\n                        <div><span class="fw-semibold">${c.user_name}</span><small class="text-muted ms-2">just now</small></div>\n                        <div class="comment-actions">\n                            ${editDeleteButtons}\n                            <button class="btn btn-sm btn-link text-secondary reply-toggle" data-comment-id="${c.id}">Reply</button>\n                        </div>\n                    </div>\n                    <p class="mb-2 comment-text">${escapeHtml(c.comment)}</p>\n\n                    <!-- Inline edit form -->\n                    <div class="edit-comment-form mt-2" id="edit-comment-form-${c.id}" style="display:none;">\n                        <textarea class="form-control mb-2 edit-comment-text" data-comment-id="${c.id}">${escapeHtml(c.comment)}</textarea>\n                        <div class="d-flex gap-2">\n                            <button class="btn btn-sm btn-primary save-edit-comment" data-comment-id="${c.id}">Save</button>\n                            <button class="btn btn-sm btn-secondary cancel-edit-comment" data-comment-id="${c.id}">Cancel</button>\n                        </div>\n                    </div>\n\n                    <div class="reply-form-container mt-2" id="reply-form-${c.id}" style="display:none;">\n                        <textarea class="form-control mb-2 reply-textarea" rows="2"></textarea>\n                        <div class="d-flex gap-2">\n                            <button class="btn btn-sm btn-primary submit-reply" data-comment-id="${c.id}">Reply</button>\n                            <button class="btn btn-sm btn-secondary cancel-reply" data-comment-id="${c.id}">Cancel</button>\n                        </div>\n                    </div>\n                    <div class="replies-container mt-3"></div>\n                </div>\n            </div>\n        </div>\n        `;
    }

    function buildReplyHtml(r){
        // Permission check: show edit/delete buttons only for owner
        let editDeleteButtons = '';
        
        // For authenticated users (admin/staff): show buttons if they own the reply
        if (window.IS_AUTHENTICATED && window.CURRENT_USER_ID === r.user_id) {
            editDeleteButtons = `<button class="btn btn-sm btn-link text-primary edit-reply" data-reply-id="${r.id}">Edit</button><button class="btn btn-sm btn-link text-danger delete-reply" data-reply-id="${r.id}">Delete</button>`;
        }
        // For public view: show buttons if session token matches
        else if (publicToken && window.SESSION_TOKEN && r.session_token && window.SESSION_TOKEN === r.session_token) {
            editDeleteButtons = `<button class="btn btn-sm btn-link text-primary edit-reply" data-reply-id="${r.id}">Edit</button><button class="btn btn-sm btn-link text-danger delete-reply" data-reply-id="${r.id}">Delete</button>`;
        }
        
        return `\n        <div class="reply-item mb-3" data-reply-id="${r.id}">\n            <div class="d-flex">\n                <div class="flex-shrink-0"><div class="avatar avatar-sm ${r.sender_type==='customer'?'avatar-primary':'avatar-success'}"><span class="avatar-initial rounded-circle">${(r.user_name||'U').charAt(0).toUpperCase()}</span></div></div>\n                <div class="flex-grow-1 ms-2">\n                    <div class="d-flex justify-content-between align-items-start mb-1">\n                        <div><span class="fw-semibold">${r.user_name}</span><small class="text-muted ms-2">just now</small></div>\n                        <div class="reply-actions">${editDeleteButtons}</div>\n                    </div>\n                    <p class="mb-1 reply-text">${escapeHtml(r.comment)}</p>\n                    <div class="edit-reply-form mt-2" id="edit-reply-form-${r.id}" style="display:none;">\n                        <textarea class="form-control mb-2 edit-reply-text" data-reply-id="${r.id}">${escapeHtml(r.comment)}</textarea>\n                        <div class="d-flex gap-2">\n                            <button class="btn btn-sm btn-primary save-edit-reply" data-reply-id="${r.id}">Save</button>\n                            <button class="btn btn-sm btn-secondary cancel-edit-reply" data-reply-id="${r.id}">Cancel</button>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n        `;
    }

    function escapeHtml(unsafe){ return String(unsafe).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }

    // Load comments from server on page load (for public view to ensure session tokens are loaded)
    async function loadPublicComments() {
        if (!publicToken) return; // Only for public view
        try {
            const res = await fetch(commentsListEndpoint, {
                method: 'GET',
                headers: {
                    'X-Session-Token': window.SESSION_TOKEN,
                    'Accept': 'application/json'
                }
            });
            const comments = await res.json();
            if (Array.isArray(comments) && comments.length > 0) {
                // Clear existing comments (server-side rendered ones)
                const commentsList = qs('#comments-list');
                commentsList.innerHTML = '';
                // Re-render with session tokens included (from API response)
                comments.forEach(c => {
                    commentsList.insertAdjacentHTML('beforeend', buildCommentHtml(c));
                });
                console.log('[Debug] Loaded ' + comments.length + ' comments from API with session tokens');
            }
        } catch(err) {
            console.error('[Debug] Error loading comments:', err);
        }
    }

    // Trigger load on DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadPublicComments);
    } else {
        loadPublicComments();
    }
})();
</script>
