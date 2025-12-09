<!-- Add Material to Quotation Modal -->
<div class="modal fade" id="addMatModal" tabindex="-1" aria-labelledby="addMatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form id="addMaterialForm" method="POST" action="{{ isset($additionalQuotation) ? url('/additional-quotation-materials/add-selected') : url('/quotation-materials/add-selected') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addMatModalLabel">Add Material to Quotation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="add-material-controls d-flex flex-wrap gap-2 align-items-center mb-3">
                        <input type="text" id="materialSearch" class="form-control me-2" placeholder="Search materials...">
                        <button type="button" id="openNewMaterialModalBtn" class="btn btn-success">
                            + Add Material
                        </button>
                    </div>

                    <div class="materials-table-wrapper">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="materialsTable">
                        <thead>
                            <tr>
                                <th>Material Name</th>
                                <th>Unit</th>
                                <th>Unit Cost</th>
                                <th>Quantity</th>
                                <th>Select</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Filled dynamically via JS -->
                        </tbody>
                            </table>
                        </div>
                    </div>

                    <input type="hidden" name="quot_id" value="{{ isset($additionalQuotation) ? $additionalQuotation->id : ($quotation->id ?? $parentQuotation->id ?? '') }}">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="addSelectedMaterialsBtn">Add Selected Materials</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    /**
     * Material Loader + Search Handler
     */
    class MaterialHandler {
        constructor(tableId, fetchUrl, searchInputId) {
            this.tableId = tableId;
            this.fetchUrl = fetchUrl;
            this.searchInput = document.getElementById(searchInputId);
            this.allMaterials = [];
            this.isInitialized = false;

            // Bind search event once
            if (this.searchInput && !this.isInitialized) {
                this.isInitialized = true;
                this.searchInput.addEventListener("input", (e) => {
                    this.filterMaterials(e.target.value.trim().toLowerCase());
                });
            }
        }

        loadMaterials() {
            return fetch(this.fetchUrl)
                .then(res => res.json())
                .then(materials => {
                    this.allMaterials = materials;
                    this.renderTable(materials);
                    return materials;
                })
                .catch(err => {
                    console.error("Error loading materials:", err);
                    return [];
                });
        }

        renderTable(materials) {
            const table = document.getElementById(this.tableId);
            if (!table) return;
            const tbody = table.querySelector("tbody");
            tbody.innerHTML = "";

            if (materials.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No materials found</td></tr>`;
                return;
            }

            materials.forEach(material => {
                const formattedPrice = parseFloat(material.unit_price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const row = `
                <tr>
                    <td>${material.name}</td>
                    <td>${material.unit}</td>
                    <td>₱${formattedPrice}</td>
                    <td><input type="number" name="quantity[${material.id}]" class="form-control" value="1" min="1"></td>
                    <td class="text-center"><input type="checkbox" name="selected[]" value="${material.id}"></td>
                </tr>`;
                tbody.insertAdjacentHTML("beforeend", row);
            });
        }

        filterMaterials(searchTerm) {
            if (!searchTerm) {
                this.renderTable(this.allMaterials);
                return;
            }

            const filtered = this.allMaterials.filter(material =>
                material.name.toLowerCase().includes(searchTerm) ||
                material.unit.toLowerCase().includes(searchTerm)
            );

            this.renderTable(filtered);
        }
    }

    /**
     * Add Material to Quotation Handler
     */
    class AddMaterialtoQuotation {
        constructor() {
            this.modalEl = document.getElementById('addMatModal');
            this.formEl = document.getElementById('addMaterialForm');
            this.submitBtn = document.getElementById('addSelectedMaterialsBtn');
            this.materialHandler = null;
            this.listenersBound = false;
            this._lastShownAt = 0; // guard against duplicate shown events
            
            // Initialize listeners only once
            this.initializeListeners();
        }

        initializeListeners() {
            // Prevent adding listeners multiple times
            if (this.listenersBound) return;

            // If essential DOM nodes are not present yet (happens when modal
            // content was swapped/restored), retry a few times instead of
            // throwing. This avoids `Cannot read properties of null` errors.
            if (!this.modalEl || !this.submitBtn) {
                this._initRetries = (this._initRetries || 0) + 1;
                if (this._initRetries > 10) {
                    console.error('AddMaterialtoQuotation: required elements missing after retries', {
                        modalEl: this.modalEl,
                        submitBtn: this.submitBtn
                    });
                    return;
                }
                // Try again shortly — small backoff to allow DOM restore to complete
                setTimeout(() => this.initializeListeners(), 120);
                return;
            }

            // Now bind handlers
            this.listenersBound = true;

            // Store bound handlers so they can be removed in dispose()
            this._onShownBound = () => this.onModalShown();
            this._onSubmitBound = (e) => { e.preventDefault(); this.addMaterials(); };

            // Load materials when modal is shown
            this.modalEl.addEventListener('shown.bs.modal', this._onShownBound);

            // Submit button handler
            this.submitBtn.addEventListener('click', this._onSubmitBound);
        }

        /**
         * Remove all listeners added by this instance. Call before discarding instance
         * to avoid duplicate handlers causing duplicate show/hidden events.
         */
        dispose() {
            try {
                if (this._onShownBound && this.modalEl) {
                    this.modalEl.removeEventListener('shown.bs.modal', this._onShownBound);
                }
                if (this._onSubmitBound && this.submitBtn) {
                    this.submitBtn.removeEventListener('click', this._onSubmitBound);
                }
            } catch (err) {
                console.error('Error disposing AddMaterialtoQuotation handlers', err);
            }
            console.log('[SWAP-TRACE] AddMaterialtoQuotation.dispose() called', { time: Date.now() });
            this.listenersBound = false;
            this._onShownBound = null;
            this._onSubmitBound = null;
        }

        onModalShown() {
            // Prevent duplicate invocation if shown event fires multiple times quickly
            const now = Date.now();
            if (now - this._lastShownAt < 800) {
                // ignore duplicate shown event
                return;
            }
            this._lastShownAt = now;

            // Create new material handler each time modal is shown
            if (!this.materialHandler) {
                this.materialHandler = new MaterialHandler("materialsTable", "/materials/list", "materialSearch");
            }
            this.materialHandler.loadMaterials();
        }

        async addMaterials() {
            if (!this.formEl) return;

            const formData = new FormData(this.formEl);
            this.submitBtn.disabled = true;
            this.submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding...';

            try {
                // Get the form action to determine the endpoint
                const endpoint = this.formEl.action || "/quotation-materials/add-selected";
                const res = await fetch(endpoint, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": '{{ csrf_token() }}',
                        "Accept": "application/json"
                    },
                    body: formData
                });

                const data = await res.json();

                if (data.success) {
                    // Hide the modal immediately; centralized handler will reconcile backdrops
                    try {
                        const modalInstance = bootstrap.Modal.getInstance(this.modalEl);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                    } catch (err) {
                        console.error("Error hiding modal:", err);
                    }

                    // Show success message (delayed to not conflict with modal hide animation)
                    setTimeout(() => {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Material added!',
                            showConfirmButton: false,
                            timer: 1200
                        });

                        // Reload quotation materials
                        if (window.quotationMaterialHandler && typeof window.quotationMaterialHandler.loadMaterials === 'function') {
                            window.quotationMaterialHandler.loadMaterials();
                        }
                    }, 650);
                } else {
                    Swal.fire("Failed to add material", data.message || "", "error");
                }
            } catch (err) {
                console.error("Error adding materials:", err);
                Swal.fire("An error occurred", err.message || "", "error");
            } finally {
                this.submitBtn.disabled = false;
                this.submitBtn.innerHTML = 'Add Selected Materials';
            }
        }
    }

    // Initialize when DOM is ready - but only once globally
    if (!window.addMaterialQuotationInitialized) {
        window.addMaterialQuotationInitialized = true;
        
        const initAddMaterialModal = () => {
            // Check if element exists and instance hasn't been created yet
            if (document.getElementById('addMatModal') && !window.addMaterialQuotation) {
                window.addMaterialQuotation = new AddMaterialtoQuotation();
            }
        };

        // Initialize immediately if DOM is already loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAddMaterialModal);
        } else {
            initAddMaterialModal();
        }

        // Re-attach listener if instance was disposed
        const modalEl = document.getElementById('addMatModal');
        if (modalEl) {
            modalEl.addEventListener('show.bs.modal', function() {
                // If instance doesn't exist, recreate it
                if (!window.addMaterialQuotation) {
                    window.addMaterialQuotation = new AddMaterialtoQuotation();
                }
            });
            // NOTE: intentionally do NOT run per-modal hidden cleanup here.
            // Centralized cleanup in `public/assets/js/modal-handler.js` handles
            // safe removal of extra backdrops and the `modal-open` class.
        }
    }

    // Programmatic opener for nested New Material modal to avoid Bootstrap's
    // automatic behaviors that can hide the parent modal in some environments.
    // Idempotent initializer for the programmatic "+ Add Material" opener.
    window.initAddMaterialSwap = function() {
        const btn = document.getElementById('openNewMaterialModalBtn');
        const modalEl = document.getElementById('addMatModal');
    const quotationId = "{{ $additionalQuotation->id ?? $quotation->id ?? $parentQuotation->id ?? '' }}";
        if (!btn || !modalEl) return;

        // Remove any previous handler to avoid duplicate bindings
        try {
            if (btn.__openNewMaterialHandler) {
                btn.removeEventListener('click', btn.__openNewMaterialHandler);
                btn.__openNewMaterialHandler = null;
            }
        } catch (err) {
            // ignore
        }

        const handler = function(e) {
            e.preventDefault();

            console.log('[SWAP-TRACE] +Add Material clicked', {
                time: Date.now(),
                modalBackdropCount: document.querySelectorAll('.modal-backdrop').length,
                shownModals: document.querySelectorAll('.modal.show').length,
                bodyHasModalOpen: document.body.classList.contains('modal-open')
            });

            const tmpl = document.getElementById('tmpl-new-material');
            if (!tmpl) {
                console.error('Template #tmpl-new-material not found when attempting to open New Material form');
                return;
            }

            // Dispose existing AddMaterial instance (remove its listeners) before swapping
            if (window.addMaterialQuotation && typeof window.addMaterialQuotation.dispose === 'function') {
                try {
                    window.addMaterialQuotation.dispose();
                } catch (err) {
                    console.error('Error disposing previous AddMaterial instance', err);
                }
                window.addMaterialQuotation = null;
            }

            // Save original content so we can restore later
            if (!modalEl.__originalContent) {
                modalEl.__originalContent = modalEl.querySelector('.modal-content').innerHTML;
            }

            // Clone template and swap into modal
            const clone = tmpl.content.firstElementChild.cloneNode(true);
            const contentHolder = modalEl.querySelector('.modal-content');
            contentHolder.innerHTML = '';
            contentHolder.appendChild(clone);

            console.log('[SWAP-TRACE] Swapped new material template into addMatModal', {
                time: Date.now(),
                backdropCountAfterSwap: document.querySelectorAll('.modal-backdrop').length,
                shownModalsAfterSwap: document.querySelectorAll('.modal.show').length,
                bodyHasModalOpenAfterSwap: document.body.classList.contains('modal-open'),
                contentHTMLSize: contentHolder.innerHTML.length
            });

            // Initialize the new material form inside the swapped content
            const formEl = contentHolder.querySelector('form[data-swap-new-material]');
            if (window.initNewMaterialForm) {
                window.initNewMaterialForm(formEl, modalEl, quotationId);
            }
        };

        btn.addEventListener('click', handler);
        btn.__openNewMaterialHandler = handler;
    };

    // Initialize immediately
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.initAddMaterialSwap);
    } else {
        window.initAddMaterialSwap();
    }
</script>
<style>
/* Scoped styles for Add Material modal responsiveness */
.add-material-controls .form-control {
    min-width: 160px;
    flex: 1 1 200px;
}
.add-material-controls .btn {
    flex: 0 0 auto;
}
.materials-table-wrapper { /* ensure table gets its own scrolling area */
    width: 100%;
}
.modal-dialog-centered .modal-content {
    /* slightly adjust vertical centering for better visual balance */
    transform: none;
}
@media (max-width: 576px) {
    .add-material-controls {
        gap: .5rem;
    }
    .add-material-controls .form-control {
        flex-basis: 100%;
    }
}
</style>
