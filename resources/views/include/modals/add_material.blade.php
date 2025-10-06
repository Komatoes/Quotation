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
     * Material Loader
     */
    class MaterialHandler {
        constructor(tableId, fetchUrl) {
            this.tableId = tableId;
            this.fetchUrl = fetchUrl;
        }

        loadMaterials() {
            fetch(this.fetchUrl)
                .then(res => res.json())
                .then(materials => {
                    const table = document.getElementById(this.tableId);
                    if (!table) return;
                    const tbody = table.querySelector("tbody");
                    tbody.innerHTML = "";

                    materials.forEach(material => {
                        const row = `
                            <tr>
                                <td>${material.name}</td>
                                <td>${material.unit}</td>
                                <td>₱${parseFloat(material.unit_cost).toFixed(2)}</td>
                                <td><input type="number" name="quantity[${material.id}]" class="form-control" value="1" min="1"></td>
                                <td class="text-center"><input type="checkbox" name="selected[]" value="${material.id}"></td>
                            </tr>`;
                        tbody.insertAdjacentHTML("beforeend", row);
                    });
                })
                .catch(err => console.error("Error loading materials:", err));
        }
    }

    // Load materials when Add Material modal is opened
    const addMatModal = document.getElementById('addMatModal');
    addMatModal.addEventListener('shown.bs.modal', () => {
        window.modalMaterialHandler = new MaterialHandler("materialsTable", "/materials/list");
        window.modalMaterialHandler.loadMaterials();
    });

    /**
     * AJAX submit for attaching materials
     */
    class AddMaterialtoQuotation {
        async add(id) {
            const form = document.getElementById(id);
            const formData = new FormData(form);

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
                    // ✅ Close modal
                    const modalInstance = bootstrap.Modal.getInstance(addMatModal);
                    if (modalInstance) modalInstance.hide();

                    // ✅ Cleanup leftover backdrop (Bootstrap sometimes leaves it)
                    document.querySelectorAll(".modal-backdrop").forEach(el => el.remove());
                    document.body.classList.remove("modal-open");

                    // ✅ Refresh the quotation materials table dynamically
                    Swal.fire("Material added successfully!", "", "success").then(() => {
                        if (window.quotationMaterialHandler) {
                            window.quotationMaterialHandler.loadMaterials();
                        }
                    });

                } else {
                    Swal.fire("Failed to add material", data.message || "", "error");
                }
            } catch (err) {
                console.error("Error adding materials:", err);
                Swal.fire("An error occurred", "", "error");
            }
        }
    }

    // Make class globally accessible
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
                const newMatModal = new bootstrap.Modal(newMaterialModalEl);
                newMatModal.show();

                // cleanup handler so it fires once
                addMatModal.removeEventListener("hidden.bs.modal", handler);
            });
        });
    }

    /**
     * Backdrop cleanup when New Material modal closes
     */
    newMaterialModalEl.addEventListener("hidden.bs.modal", function() {
        document.querySelectorAll(".modal-backdrop").forEach(el => el.remove());
        document.body.classList.remove("modal-open");
    });
});
</script>
