<!-- New Material template (used for swapping into the Add Material modal) -->
<!-- New Material template (used for swapping into the Add Material modal) -->
<template id="tmpl-new-material">
    <div class="modal-content">
        <form class="add-new-record pt-0 row g-2" data-swap-new-material method="POST" action="{{ url('/materials/store') }}">
            @csrf
            <div class="modal-header">
                <button type="button" class="btn btn-outline-secondary back-to-add-list">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back
                </button>
                <h5 class="modal-title">Add Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="col-sm-12 form-control-validation">
                    <label class="form-label">Material Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Cement" required />
                </div>

                <div class="col-sm-12 form-control-validation mt-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Optional description"></textarea>
                </div>

                <div class="col-sm-6 form-control-validation mt-3">
                    <label class="form-label">Unit</label>
                    <input type="text" name="unit" class="form-control" placeholder="pcs / kg / liters" required />
                </div>

                <div class="col-sm-12 form-control-validation mt-3">
                    <label class="form-label">Unit Price</label>
                    <input type="number" name="unit_price" class="form-control" placeholder="250.00" step="0.01" required />
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary data-submit me-sm-4 me-1">Save</button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</template>

<script>
/**
 * Initialize a swapped-in New Material form inside a modal container
 * @param {HTMLFormElement} formEl - the form element inside the modal
 * @param {HTMLElement} modalEl - the modal root element (the addMatModal)
 * @param {string|number} quotationId - current quotation id
 */
window.initNewMaterialForm = function(formEl, modalEl, quotationId) {
    if (!formEl) return;

    // Prevent duplicate binding
    if (formEl.__newMaterialInit) return;
    formEl.__newMaterialInit = true;

    console.log('[SWAP-TRACE] initNewMaterialForm called', {
        time: Date.now(),
        modalId: modalEl?.id,
        backdropCount: document.querySelectorAll('.modal-backdrop').length,
        shownModals: document.querySelectorAll('.modal.show').length,
        bodyHasModalOpen: document.body.classList.contains('modal-open')
    });

    formEl.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(formEl);

        try {
            const res = await fetch('/materials/store', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: formData
            });

            const data = await res.json();
            if (!res.ok || !data.success) {
                const msg = data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : 'Failed to add material.');
                Swal.fire({ title: 'Error', text: msg, icon: 'error' });
                return;
            }

            // Attach new material to quotation
            const attachRes = await fetch('/quotation-materials/add-selected', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ quot_id: quotationId, selected: [data.material.id], quantity: { [data.material.id]: 1 } })
            });

            const resp = await attachRes.json();
            if (!attachRes.ok || !resp.success) {
                const msg = resp.message || (resp.errors ? Object.values(resp.errors).flat().join('\n') : 'Failed to attach material.');
                Swal.fire({ title: 'Error', text: msg, icon: 'error' });
                return;
            }

            // Hide modal (the parent addMatModal)
            try {
                const inst = bootstrap.Modal.getInstance(modalEl);
                if (inst) inst.hide();
            } catch (err) {
                console.error('Error hiding modal after save', err);
            }

            setTimeout(() => {
                Swal.fire({ title: 'Success!', text: 'Material added and attached to quotation.', icon: 'success', timer: 1500, showConfirmButton: false });
                if (window.quotationMaterialHandler && typeof window.quotationMaterialHandler.loadMaterials === 'function') {
                    window.quotationMaterialHandler.loadMaterials();
                }
                if (resp.grand_total !== undefined) {
                    const grandTotalEl = document.getElementById('grandTotal');
                    if (grandTotalEl) grandTotalEl.textContent = '₱' + parseFloat(resp.grand_total).toFixed(2);
                }
            }, 600);

        } catch (err) {
            console.error(err);
            Swal.fire({ title: 'Error', text: 'Something went wrong!', icon: 'error' });
        }
    });

    // Back button handler (if present)
    const backBtn = formEl.closest('.modal-content')?.querySelector('.back-to-add-list');
    if (backBtn) {
        backBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Restore original addMatModal content if saved
            const modal = modalEl;
            if (modal && modal.__originalContent) {
                modal.querySelector('.modal-content').innerHTML = modal.__originalContent;

                // Dispose any lingering instance before re-initializing
                try {
                    if (window.addMaterialQuotation && typeof window.addMaterialQuotation.dispose === 'function') {
                        window.addMaterialQuotation.dispose();
                    }
                } catch (err) {
                    console.error('Error disposing existing AddMaterial instance before re-init', err);
                }

                // Force re-initialize AddMaterialtoQuotation to rebind handlers to restored DOM
                try {
                    window.addMaterialQuotation = null;
                    window.addMaterialQuotation = new AddMaterialtoQuotation();
                } catch (err) {
                    console.error('Failed to re-init AddMaterialtoQuotation after restoring content', err);
                }
                console.log('[SWAP-TRACE] Back clicked - restored original addMatModal content', {
                    time: Date.now(),
                    backdropCountAfterRestore: document.querySelectorAll('.modal-backdrop').length,
                    shownModalsAfterRestore: document.querySelectorAll('.modal.show').length,
                    bodyHasModalOpenAfterRestore: document.body.classList.contains('modal-open')
                });
                // Re-init swap opener in case listeners were lost
                try { if (typeof window.initAddMaterialSwap === 'function') window.initAddMaterialSwap(); } catch(e){}
            }
        });
    }

    // Ensure the programmatic swap opener is re-initialized after restoring modal content
    try {
        if (typeof window.initAddMaterialSwap === 'function') {
            window.initAddMaterialSwap();
        }
    } catch (e) {
        /* noop */
    }
    // Move focus to the first input for convenience
    try {
        setTimeout(() => {
            const nameInput = formEl.querySelector('[name="name"]');
            if (nameInput) nameInput.focus();
        }, 80);
    } catch (e) {
        /* noop */
    }
};
</script>
