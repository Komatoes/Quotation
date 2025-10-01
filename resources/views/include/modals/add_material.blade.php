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
                        <button type="button" id="openNewMaterialModalBtn" class="btn btn-success">
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
                            const row = `<tr>
                            <td>${material.name}</td>
                            <td>${material.unit}</td>
                            <td>₱${parseFloat(material.unit_cost).toFixed(2)}</td>
                            <td><input type="number" name="quantity[${material.id}]" class="form-control" value="1" min="1"></td>
                            <td class="text-center"><input type="checkbox" name="selected[]" value="${material.id}"></td>
                        </tr>`;
                            tbody.insertAdjacentHTML("beforeend", row);
                        });
                    }).catch(err => console.error("Error loading materials:", err));
            }
        }

        const addMatModal = document.getElementById('addMatModal');
        addMatModal.addEventListener('shown.bs.modal', () => {
            window.modalMaterialHandler = new MaterialHandler("materialsTable", "/materials/list");
            window.modalMaterialHandler.loadMaterials();
        });
    });
</script>
<script>
    class AddMaterialtoQuotation {
        add(id) {
            const form = document.getElementById(id);
            const formData = new FormData(form);

            fetch("/quotation-materials/add-selected", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": '{{ csrf_token() }}',
                        "Accept": "application/json"
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // hide the offcanvas
                        const offcanvasEl = document.getElementById("add-new-quotation");
                        const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                        if (offcanvas) offcanvas.hide();

                        Swal.fire("Material added successfully", "", "success").then(() => {
                            // optional: reload page or update table dynamically
                            window.location.reload();
                        });
                    } else {
                        Swal.fire("Failed to add material", "", "error");
                    }
                })
                .catch(err => {
                    console.error("Error adding materials:", err);
                    Swal.fire("An error occurred", "", "error");
                });

        }
    }
    const addMaterialQuotation = new AddMaterialtoQuotation();
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const openNewMaterialBtn = document.getElementById("openNewMaterialModalBtn");
    const addMatModalEl = document.getElementById("addMatModal");
    const newMaterialModalEl = document.getElementById("newMaterialModal");

    if (openNewMaterialBtn) {
        openNewMaterialBtn.addEventListener("click", function () {
            // Close Add Material modal first
            const addMatModal = bootstrap.Modal.getInstance(addMatModalEl) 
                || new bootstrap.Modal(addMatModalEl);
            addMatModal.hide();

            // When it’s fully hidden, open the New Material modal
            addMatModalEl.addEventListener("hidden.bs.modal", function handler() {
                const newMatModal = new bootstrap.Modal(newMaterialModalEl);
                newMatModal.show();

                // Remove this handler so it doesn’t fire every time
                addMatModalEl.removeEventListener("hidden.bs.modal", handler);
            });
        });
    }
});
</script>
