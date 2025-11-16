<!-- Add Material to Quotation Modal -->
<div class="modal fade" id="addMatModal" tabindex="-1" aria-labelledby="addMatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form id="addMaterialForm" method="POST" action="{{ url('/quotation-materials/store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addMatModalLabel">Add Material to Quotation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="add-material-controls d-flex flex-wrap gap-2 align-items-center mb-3">
                        <input type="text" id="materialSearch" class="form-control me-2" placeholder="Search materials...">
                        <button type="button" id="openNewMaterialModalBtn" class="btn btn-success"
                            data-bs-toggle="modal" data-bs-target="#newMaterialModal">
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

                    <input type="hidden" name="quot_id" value="{{ $quotation->id }}">
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
                const row = `
                <tr>
                    <td>${material.name}</td>
                    <td>${material.unit}</td>
                    <td>₱${parseFloat(material.unit_price).toFixed(2)}</td>
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
            this.listenersBound = true;

            // Load materials when modal is shown
            this.modalEl.addEventListener('shown.bs.modal', () => {
                this.onModalShown();
            });

            // Submit button handler
            this.submitBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.addMaterials();
            });
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
                const res = await fetch("/quotation-materials/add-selected", {
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
        }
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
