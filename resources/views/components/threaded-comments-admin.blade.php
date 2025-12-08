<script>
    // Admin threaded comments component: auth required
    window.CURRENT_USER_ID = @json(optional(auth()->user())->id);
    window.IS_AUTHENTICATED = @json(auth()->check());
</script>

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
                                    {{-- Admin view: ONLY owner can edit/delete their own comment --}}
                                    @if(Auth::check() && Auth::id() == $comment->user_id)
                                        <button class="btn btn-sm btn-link text-primary edit-comment" data-comment-id="{{ $comment->id }}">Edit</button>
                                        <button class="btn btn-sm btn-link text-danger delete-comment" data-comment-id="{{ $comment->id }}">Delete</button>
                                    @endif
                                    <button class="btn btn-sm btn-link text-secondary reply-toggle" data-comment-id="{{ $comment->id }}">Reply</button>
                                </div>
                            </div>
                            <p class="mb-2 comment-text">{{ $comment->comment }}</p>

                            <div class="edit-comment-form mt-2" id="edit-comment-form-{{ $comment->id }}" style="display:none;">
                                <textarea class="form-control mb-2 edit-comment-text" data-comment-id="{{ $comment->id }}">{{ $comment->comment }}</textarea>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-primary save-edit-comment" data-comment-id="{{ $comment->id }}">Save</button>
                                    <button class="btn btn-sm btn-secondary cancel-edit-comment" data-comment-id="{{ $comment->id }}">Cancel</button>
                                </div>
                            </div>

                            <div class="reply-form-container mt-2" id="reply-form-{{ $comment->id }}" style="display:none;">
                                <textarea class="form-control mb-2 reply-textarea" rows="2" placeholder="Write a reply..."></textarea>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-primary submit-reply" data-comment-id="{{ $comment->id }}">Reply</button>
                                    <button class="btn btn-sm btn-secondary cancel-reply" data-comment-id="{{ $comment->id }}">Cancel</button>
                                </div>
                            </div>

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
                                                            @if(Auth::check() && Auth::id() == $reply->user_id)
                                                                <button class="btn btn-sm btn-link text-primary edit-reply" data-reply-id="{{ $reply->id }}">Edit</button>
                                                                <button class="btn btn-sm btn-link text-danger delete-reply" data-reply-id="{{ $reply->id }}">Delete</button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <p class="mb-1 reply-text">{{ $reply->comment }}</p>

                                                    <div class="edit-reply-form mt-2" id="edit-reply-form-{{ $reply->id }}" style="display:none;">
                                                        <textarea class="form-control mb-2 edit-reply-text" data-reply-id="{{ $reply->id }}">{{ $reply->comment }}</textarea>
                                                        <div class="d-flex gap-2">
                                                            <button class="btn btn-sm btn-primary save-edit-reply" data-reply-id="{{ $reply->id }}">Save</button>
                                                            <button class="btn btn-sm btn-secondary cancel-edit-reply" data-reply-id="{{ $reply->id }}">Cancel</button>
                                                        </div>
                                                    </div>

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
                                                                                    @if(Auth::check() && Auth::id() == $nreply->user_id)
                                                                                        <button class="btn btn-sm btn-link text-primary edit-nested-reply" data-nested-reply-id="{{ $nreply->id }}">Edit</button>
                                                                                        <button class="btn btn-sm btn-link text-danger delete-nested-reply" data-nested-reply-id="{{ $nreply->id }}">Delete</button>
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

        <div class="mt-4 pt-3 border-top d-flex gap-3">
            <div class="flex-shrink-0">
                @php
                    $mainInitial = 'U';
                    if (Auth::check()) {
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

@push('scripts')
<script>
// Full admin comment handlers: load, render, post, edit, delete, replies
(function(){
    const quotationId = "{{ $quotationId ?? '' }}";
    const quotationType = "{{ $quotationType ?? 'quotation' }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const baseEndpoint = quotationType === 'additional' ? `/additional-quotations/${quotationId}/comments` : `/quotation/${quotationId}/comments`;
    const commentEndpoint = baseEndpoint;
    const commentsListEndpoint = baseEndpoint;

    function qs(s,ctx=document){return ctx.querySelector(s);} 
    function qsa(s,ctx=document){return Array.from((ctx||document).querySelectorAll(s));}
    function escapeHtml(unsafe){ return String(unsafe||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\"/g,'&quot;').replace(/'/g,'&#039;'); }

    // Build comment HTML similar to public builder but for admin
    function buildAdminCommentHtml(c){
        let editDeleteButtons = '';
        if (window.IS_AUTHENTICATED && window.CURRENT_USER_ID === c.user_id) {
            editDeleteButtons = `<button class="btn btn-sm btn-link text-primary edit-comment" data-comment-id="${c.id}">Edit</button><button class="btn btn-sm btn-link text-danger delete-comment" data-comment-id="${c.id}">Delete</button>`;
        }

        const repliesHtml = (c.replies||[]).map(r => buildAdminReplyHtml(r)).join('');

        return `
        <li class="mb-3" data-comment-id="${c.id}">
            <div class="d-flex mb-2">
                <div class="flex-shrink-0"><div class="avatar ${c.sender_type==='customer'?'avatar-primary':'avatar-success'}"><span class="avatar-initial rounded-circle">${escapeHtml((c.user_name||'U').charAt(0).toUpperCase())}</span></div></div>
                <div class="flex-grow-1 ms-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div><span class="fw-semibold">${escapeHtml(c.user_name|| (c.sender_type==='customer'?'Customer':'Admin'))}</span><small class="text-muted ms-2">${new Date(c.created_at).toLocaleString()}</small></div>
                        <div class="comment-actions">
                            ${editDeleteButtons}
                            <button class="btn btn-sm btn-link text-secondary reply-toggle" data-comment-id="${c.id}">Reply</button>
                        </div>
                    </div>
                    <p class="mb-2 comment-text">${escapeHtml(c.comment)}</p>

                    <div class="edit-comment-form mt-2" id="edit-comment-form-${c.id}" style="display:none;">
                        <textarea class="form-control mb-2 edit-comment-text" data-comment-id="${c.id}">${escapeHtml(c.comment)}</textarea>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-primary save-edit-comment" data-comment-id="${c.id}">Save</button>
                            <button class="btn btn-sm btn-secondary cancel-edit-comment" data-comment-id="${c.id}">Cancel</button>
                        </div>
                    </div>

                    <div class="reply-form-container mt-2" id="reply-form-${c.id}" style="display:none;">
                        <textarea class="form-control mb-2 reply-textarea" rows="2" placeholder="Write a reply..."></textarea>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-primary submit-reply" data-comment-id="${c.id}">Reply</button>
                            <button class="btn btn-sm btn-secondary cancel-reply" data-comment-id="${c.id}">Cancel</button>
                        </div>
                    </div>

                    <div class="replies-container mt-3">${repliesHtml}</div>
                </div>
            </div>
        </li>
        `;
    }

    function buildAdminReplyHtml(r){
        let editDeleteButtons = '';
        if (window.IS_AUTHENTICATED && window.CURRENT_USER_ID === r.user_id) {
            editDeleteButtons = `<button class="btn btn-sm btn-link text-primary edit-reply" data-reply-id="${r.id}">Edit</button><button class="btn btn-sm btn-link text-danger delete-reply" data-reply-id="${r.id}">Delete</button>`;
        }

        const nestedHtml = (r.nestedReplies||[]).map(nr => buildAdminReplyHtml(nr)).join('');

        return `
        <div class="reply-item mb-3" data-reply-id="${r.id}">
            <div class="d-flex">
                <div class="flex-shrink-0"><div class="avatar avatar-sm ${r.sender_type==='customer'?'avatar-primary':'avatar-success'}"><span class="avatar-initial rounded-circle">${escapeHtml((r.user_name||'U').charAt(0).toUpperCase())}</span></div></div>
                <div class="flex-grow-1 ms-2">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div><span class="fw-semibold">${escapeHtml(r.user_name|| (r.sender_type==='customer'?'Customer':'Admin'))}</span><small class="text-muted ms-2">${new Date(r.created_at).toLocaleString()}</small></div>
                        <div class="reply-actions">${editDeleteButtons}</div>
                    </div>
                    <p class="mb-1 reply-text">${escapeHtml(r.comment)}</p>
                    <div class="edit-reply-form mt-2" id="edit-reply-form-${r.id}" style="display:none;">
                        <textarea class="form-control mb-2 edit-reply-text" data-reply-id="${r.id}">${escapeHtml(r.comment)}</textarea>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-primary save-edit-reply" data-reply-id="${r.id}">Save</button>
                            <button class="btn btn-sm btn-secondary cancel-edit-reply" data-reply-id="${r.id}">Cancel</button>
                        </div>
                    </div>
                    <div class="nested-replies mt-2">${nestedHtml}</div>
                </div>
            </div>
        </div>
        `;
    }

    // Load comments from server and render (used after create/edit/delete)
    async function loadCommentsFromServer(){
        try{
            const res = await fetch(commentsListEndpoint, { method:'GET', headers: {'Accept':'application/json'} });
            const comments = await res.json();
            const list = qs('#comments-list');
            list.innerHTML = '';
            if(Array.isArray(comments)){
                comments.forEach(c => list.insertAdjacentHTML('beforeend', buildAdminCommentHtml(c)));
            }
        }catch(err){ console.error('Failed to load comments', err); }
    }

    // Wire delegated handlers
    document.addEventListener('click', function(e){
        // Reply toggle
        const rt = e.target.closest('.reply-toggle'); if(rt){ const id = rt.dataset.commentId; const form = qs(`#reply-form-${id}`); if(form) form.style.display = form.style.display === 'none' ? 'block' : 'none'; return; }

        // Cancel reply
        const cr = e.target.closest('.cancel-reply'); if(cr){ const id = cr.dataset.commentId; const form = qs(`#reply-form-${id}`); if(form){ form.style.display='none'; form.querySelector('.reply-textarea').value=''; } return; }

        // Submit main comment
        if(e.target.closest('#submit-main-comment')){
            (async ()=>{
                const btn = qs('#submit-main-comment'); const text = (qs('#main-comment-input').value||'').trim(); if(!text) return Swal.fire('Error','Please write a comment','warning');
                btn.disabled=true; btn.innerHTML='Posting...';
                try{
                    const res = await fetch(commentEndpoint, { method:'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body: JSON.stringify({ comment: text }) });
                    const data = await res.json(); if(data.success){ qs('#main-comment-input').value=''; await loadCommentsFromServer(); Swal.fire({toast:true,position:'top-end',icon:'success',title:'Comment posted',showConfirmButton:false,timer:1000}); }
                    else Swal.fire('Error',data.message||'Failed to post comment','error');
                }catch(err){ console.error(err); Swal.fire('Error','Something went wrong','error'); }
                btn.disabled=false; btn.innerHTML='<i class="fa-solid fa-paper-plane me-1"></i> Post Comment';
            })();
            return;
        }

        // Submit reply
        const sr = e.target.closest('.submit-reply'); if(sr){ (async ()=>{
            const commentId = sr.dataset.commentId; const textarea = qs(`#reply-form-${commentId} .reply-textarea`); const text = (textarea.value||'').trim(); if(!text) return Swal.fire('Error','Please write a reply','warning');
            sr.disabled=true; sr.innerHTML='Replying...';
            try{
                const replyEndpoint = `/comments/${commentId}/replies`;
                const res = await fetch(replyEndpoint, { method:'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body: JSON.stringify({ comment: text }) });
                const data = await res.json(); if(data.success){ textarea.value=''; qs(`#reply-form-${commentId}`).style.display='none'; await loadCommentsFromServer(); Swal.fire({toast:true,position:'top-end',icon:'success',title:'Reply posted',showConfirmButton:false,timer:1000}); }
                else Swal.fire('Error',data.message||'Failed to post reply','error');
            }catch(err){ console.error(err); Swal.fire('Error','Something went wrong','error'); }
            sr.disabled=false; sr.innerHTML='Reply';
        })(); return; }

        // Edit comment toggle
        const ec = e.target.closest('.edit-comment'); if(ec){ const id = ec.dataset.commentId; const form = qs(`#edit-comment-form-${id}`); if(form) form.style.display = form.style.display === 'none' ? 'block' : 'none'; return; }
        const cancelEdit = e.target.closest('.cancel-edit-comment'); if(cancelEdit){ const id = cancelEdit.dataset.commentId; const form = qs(`#edit-comment-form-${id}`); if(form) form.style.display='none'; return; }

        // Save edit comment
        const sec = e.target.closest('.save-edit-comment'); if(sec){ (async ()=>{
            const id = sec.dataset.commentId; const textarea = qs(`#edit-comment-form-${id} .edit-comment-text`); const newText = (textarea.value||'').trim(); if(!newText) return Swal.fire('Error','Comment cannot be empty','warning');
            sec.disabled=true; sec.innerHTML='Saving...';
            try{
                const res = await fetch(`/comments/${id}`, { method:'PUT', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body: JSON.stringify({ comment: newText }) });
                const data = await res.json(); if(data.success){ await loadCommentsFromServer(); Swal.fire({toast:true,position:'top-end',icon:'success',title:'Updated',showConfirmButton:false,timer:1200}); }
                else Swal.fire('Error',data.message||'Failed to update','error');
            }catch(err){ console.error(err); Swal.fire('Error','Something went wrong','error'); }
            sec.disabled=false; sec.innerHTML='Save';
        })(); return; }

        // Delete comment
        const dc = e.target.closest('.delete-comment'); if(dc){ const id = dc.dataset.commentId; Swal.fire({title:'Delete comment?',text:'This cannot be undone',icon:'warning',showCancelButton:true,confirmButtonColor:'#d33',cancelButtonColor:'#6c757d',confirmButtonText:'Yes, delete it'}).then(async (res)=>{ if(!res.isConfirmed) return; try{ const resp = await fetch(`/comments/${id}`, { method:'DELETE', headers: {'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'} }); const data = await resp.json(); if(data.success){ await loadCommentsFromServer(); Swal.fire({toast:true,position:'top-end',icon:'success',title:'Deleted',showConfirmButton:false,timer:1000}); } else Swal.fire('Error',data.message||'Failed to delete','error'); }catch(err){ console.error(err); Swal.fire('Error','Something went wrong','error'); } }); return; }

        // Edit reply toggle
        const er = e.target.closest('.edit-reply'); if(er){ const id = er.dataset.replyId; const form = qs(`#edit-reply-form-${id}`); if(form) form.style.display = form.style.display === 'none' ? 'block' : 'none'; return; }
        const cancelEditReply = e.target.closest('.cancel-edit-reply'); if(cancelEditReply){ const id = cancelEditReply.dataset.replyId; const form = qs(`#edit-reply-form-${id}`); if(form) form.style.display='none'; return; }

        // Save edit reply
        const ser = e.target.closest('.save-edit-reply'); if(ser){ (async ()=>{
            const id = ser.dataset.replyId; const textarea = qs(`#edit-reply-form-${id} .edit-reply-text`); const newText = (textarea.value||'').trim(); if(!newText) return Swal.fire('Error','Reply cannot be empty','warning');
            ser.disabled=true; ser.innerHTML='Saving...';
            try{
                const res = await fetch(`/replies/${id}`, { method:'PUT', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body: JSON.stringify({ comment: newText }) });
                const data = await res.json(); if(data.success){ await loadCommentsFromServer(); Swal.fire({toast:true,position:'top-end',icon:'success',title:'Updated',showConfirmButton:false,timer:1200}); } else Swal.fire('Error',data.message||'Failed to update','error');
            }catch(err){ console.error(err); Swal.fire('Error','Something went wrong','error'); }
            ser.disabled=false; ser.innerHTML='Save';
        })(); return; }

        // Delete reply
        const dr = e.target.closest('.delete-reply, .delete-nested-reply'); if(dr){ const id = dr.dataset.replyId || dr.dataset.nestedReplyId; Swal.fire({title:'Delete reply?',text:'This cannot be undone',icon:'warning',showCancelButton:true,confirmButtonColor:'#d33',cancelButtonColor:'#6c757d',confirmButtonText:'Yes, delete it'}).then(async (res)=>{ if(!res.isConfirmed) return; try{ const resp = await fetch(`/replies/${id}`, { method:'DELETE', headers: {'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'} }); const data = await resp.json(); if(data.success){ await loadCommentsFromServer(); Swal.fire({toast:true,position:'top-end',icon:'success',title:'Deleted',showConfirmButton:false,timer:1000}); } else Swal.fire('Error',data.message||'Failed to delete','error'); }catch(err){ console.error(err); Swal.fire('Error','Something went wrong','error'); } }); return; }
    });

    // Initial load
    document.addEventListener('DOMContentLoaded', function(){ loadCommentsFromServer(); });

})();
</script>
@endpush
