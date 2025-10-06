<!-- 🧱 New Material Modal -->
<div class="modal fade" id="newMaterialModal" tabindex="-1" aria-labelledby="newMaterialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="newMaterialForm" class="add-new-record pt-0 row g-2" method="POST" action="{{ url('/materials/store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="newMaterialModalLabel">Add Material</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="col-sm-12 form-control-validation">
                        <label class="form-label" for="materialName">Material Name</label>
                        <input type="text" id="materialName" class="form-control" name="name" placeholder="Cement" required />
                    </div>

                    <div class="col-sm-12 form-control-validation mt-3">
                        <label class="form-label" for="materialDescription">Description</label>
                        <textarea id="materialDescription" name="description" class="form-control" rows="2" placeholder="Optional description"></textarea>
                    </div>

                    <div class="col-sm-6 form-control-validation mt-3">
                        <label class="form-label" for="materialUnit">Unit</label>
                        <input type="text" id="materialUnit" name="unit" class="form-control" placeholder="pcs / kg / liters" required />
                    </div>

                    <div class="col-sm-12 form-control-validation mt-3">
                        <label class="form-label" for="materialPrice">Unit Price</label>
                        <input type="number" id="materialPrice" name="unit_price" class="form-control" placeholder="250.00" step="0.01" required />
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary data-submit me-sm-4 me-1">Save</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const newMaterialForm = document.getElementById('newMaterialForm');
    const newMaterialModalEl = document.getElementById('newMaterialModal');
    const quotationId = "{{ $quotation->id }}"; // current quotation ID

    newMaterialForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(newMaterialForm);

        try {
            // Step 1️⃣: Create new material
            const res = await fetch("/materials/store", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": '{{ csrf_token() }}',
                    "Accept": "application/json"
                },
                body: formData
            });

            const data = await res.json();

            if (!res.ok || !data.success) {
                const msg = data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : "Failed to add material.");
                Swal.fire({ title: "Error", text: msg, icon: "error" });
                return;
            }

            // Step 2️⃣: Attach the new material to the quotation
            const attachRes = await fetch(`/quotation-materials/add-selected`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": '{{ csrf_token() }}',
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    quot_id: quotationId,
                    selected: [data.material.id],
                    quantity: { [data.material.id]: 1 }
                })
            });

            const resp = await attachRes.json();

            if (!attachRes.ok || !resp.success) {
                const msg = resp.message || (resp.errors ? Object.values(resp.errors).flat().join('\n') : "Failed to attach material.");
                Swal.fire({ title: "Error", text: msg, icon: "error" });
                return;
            }

            // Step 3️⃣: Hide modal
            const modalInstance = bootstrap.Modal.getInstance(newMaterialModalEl);
            if (modalInstance) modalInstance.hide();

            // Step 4️⃣: Success notification
            Swal.fire({
                title: "Success!",
                text: "Material added and attached to quotation.",
                icon: "success",
                timer: 1500,
                showConfirmButton: false
            });

            // Step 5️⃣: Dynamically reload materials in the quotation table
            if (window.quotationMaterialHandler && typeof window.quotationMaterialHandler.loadMaterials === 'function') {
                await window.quotationMaterialHandler.loadMaterials();
            }

            // Step 6️⃣: Update grand total dynamically
            if (resp.grand_total !== undefined) {
                const grandTotalEl = document.getElementById("grandTotal");
                if (grandTotalEl) {
                    grandTotalEl.textContent = "₱" + parseFloat(resp.grand_total).toFixed(2);
                }
            }

            // Step 7️⃣: Reset the form
            newMaterialForm.reset();

        } catch (err) {
            console.error("Error:", err);
            Swal.fire({ title: "Error", text: "Something went wrong!", icon: "error" });
        }
    });

    // ✅ Clean up modal backdrop properly
    newMaterialModalEl.addEventListener("hidden.bs.modal", function () {
        document.querySelectorAll(".modal-backdrop").forEach(el => el.remove());
        document.body.classList.remove("modal-open");
    });
});
</script>
