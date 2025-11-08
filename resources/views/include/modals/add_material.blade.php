<!-- Add Material to Quotation Modal -->
<div class="modal fade" id="addMatModal" tabindex="-1" aria-labelledby="addMatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="addMaterialForm" method="POST" action="{{ url('/quotation-materials/store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addMatModalLabel">Add Material to Quotation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                    <div class="d-flex mb-3">
                        <input type="text" id="materialSearch" class="form-control me-2"
                            placeholder="Search materials...">
                        <button type="button" id="openNewMaterialModalBtn" class="btn btn-success"
                            data-bs-toggle="modal" data-bs-target="#newMaterialModal">
                            + Add Material
                        </button>

                    </div>

                    <table class="table table-bordered" id="materialsTable">
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

                    <input type="hidden" name="quot_id" value="{{ $quotation->id }}">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary"
                        onclick="addMaterialQuotation.add('addMaterialForm')">Add Selected Materials</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        /**
         * Material Loader + Search
         */
        class MaterialHandler {
            constructor(tableId, fetchUrl, searchInputId) {
                this.tableId = tableId;
                this.fetchUrl = fetchUrl;
                this.searchInput = document.getElementById(searchInputId);
                this.allMaterials = [];

                // Bind search event
                if (this.searchInput) {
                    this.searchInput.addEventListener("input", () => {
                        this.filterMaterials(this.searchInput.value.trim().toLowerCase());
                    });
                }
            }

            // Load materials from the backend
            loadMaterials() {
                fetch(this.fetchUrl)
                    .then(res => res.json())
                    .then(materials => {
                        this.allMaterials = materials;
                        this.renderTable(materials);
                    })
                    .catch(err => console.error("Error loading materials:", err));
            }

            // Render materials to table
            renderTable(materials) {
                const table = document.getElementById(this.tableId);
                if (!table) return;
                const tbody = table.querySelector("tbody");
                tbody.innerHTML = "";

                if (materials.length === 0) {
                    tbody.innerHTML =
                        `<tr><td colspan="5" class="text-center text-muted">No materials found</td></tr>`;
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

            // Filter materials (client-side)
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

        // Load materials when Add Material modal is opened
        const addMatModal = document.getElementById('addMatModal');
        // Debug listeners to trace modal lifecycle
        addMatModal.addEventListener('show.bs.modal', () => console.debug('[addMatModal] show.bs.modal'));
        addMatModal.addEventListener('shown.bs.modal', () => console.debug('[addMatModal] shown.bs.modal'));
        addMatModal.addEventListener('hide.bs.modal', () => console.debug('[addMatModal] hide.bs.modal'));
        addMatModal.addEventListener('hidden.bs.modal', () => console.debug('[addMatModal] hidden.bs.modal'));

        addMatModal.addEventListener('shown.bs.modal', () => {
            // always create a fresh handler for the list to avoid stale references
            console.debug('[addMatModal] initializing material handler and loading materials');
            window.modalMaterialHandler = new MaterialHandler("materialsTable", "/materials/list",
                "materialSearch");
            window.modalMaterialHandler.loadMaterials();
        });

        // Debug: observe if the modal element is ever removed from the DOM
        try {
            const modalObserver = new MutationObserver((mutations) => {
                for (const m of mutations) {
                    if (m.removedNodes && m.removedNodes.length) {
                        m.removedNodes.forEach(node => {
                            if (node === addMatModal) {
                                console.error('[addMatModal] was REMOVED from DOM by', m);
                                console.trace();
                            }
                        });
                    }
                }
            });
            modalObserver.observe(document.body, {
                childList: true,
                subtree: true
            });
            // store for potential later disconnect
            window._addMatModalObserver = modalObserver;
        } catch (e) {
            console.warn('Modal observer failed', e);
        }


        /**
         * AJAX submit for attaching materials
         */
        class AddMaterialtoQuotation {
            async add(id) {
                const form = document.getElementById(id);
                const formData = new FormData(form);

                console.debug('[addMaterialQuotation] submit called for form', id);
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
                    console.debug('[addMaterialQuotation] response', data);
                    if (data.success) {
                        // Only use Bootstrap's modal API. Wait for the modal to finish hiding
                        // before showing the Swal and reloading materials to avoid lifecycle races.
                        const modalInstance = bootstrap.Modal.getInstance(addMatModal);
                        const afterHide = () => {
                                // Show a non-blocking toast after modal has fully hidden.
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Material added!',
                                    showConfirmButton: false,
                                    timer: 1200
                                });

                                // Reload materials on the main quotation interface
                                if (window.quotationMaterialHandler) {
                                    window.quotationMaterialHandler.loadMaterials();
                                }

                                // Remove listener (defensive) — we prefer to attach with { once: true } where possible
                                addMatModal.removeEventListener('hidden.bs.modal', afterHide);
                            };



                        if (modalInstance) {
                            console.debug(
                                '[addMaterialQuotation] attaching hidden.bs.modal listener and hiding modal'
                            );
                            // Attach the listener once to avoid duplicates
                            addMatModal.addEventListener('hidden.bs.modal', afterHide, { once: true });
                            modalInstance.hide();
                        } else {
                            console.debug(
                                '[addMaterialQuotation] no modal instance, running afterHide immediately'
                            );
                            // If no instance found, just run the afterHide actions
                            afterHide();
                        }
                    } else {
                        Swal.fire("Failed to add material", data.message || "", "error");
                    }
                } catch (err) {
                    console.error("Error adding materials:", err);
                    Swal.fire("An error occurred", "", "error");
                }
            }
        }

        window.addMaterialQuotation = new AddMaterialtoQuotation();

        /**
         * Modal switching (Add Material -> New Material)
         */
        const openNewMaterialBtn = document.getElementById("openNewMaterialModalBtn");
        const newMaterialModalEl = document.getElementById("newMaterialModal");

        if (openNewMaterialBtn) {
            openNewMaterialBtn.addEventListener("click", function() {
                const addMatInstance = bootstrap.Modal.getInstance(addMatModal);
                addMatInstance.hide();

                addMatModal.addEventListener("hidden.bs.modal", function handler() {
                    // Show new material modal after add modal fully hidden.
                    // Use getOrCreateInstance to avoid duplicate instances and allow Bootstrap
                    // to correctly manage backdrop. Use a tiny timeout so other hidden handlers
                    // (global cleanup) run first and we don't race removing/adding backdrop.
                    const newMatModal = bootstrap.Modal.getOrCreateInstance(newMaterialModalEl);
                    setTimeout(() => newMatModal.show(), 20);
                    addMatModal.removeEventListener("hidden.bs.modal", handler);
                }, { once: true });
            });
        }

        /**
         * Backdrop cleanup when New Material modal closes
         */
        newMaterialModalEl.addEventListener("hidden.bs.modal", function() {
            // No manual backdrop manipulation here; let Bootstrap manage backdrop.
            console.debug('[newMaterialModal] hidden.bs.modal');
        });
    });
</script>
