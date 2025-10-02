<!-- New Material Modal -->
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
                        <input type="text" id="materialUnit" name="unit" class="form-control" placeholder="pcs / kg / liters" />
                    </div>

                    <div class="col-sm-12 form-control-validation mt-3">
                        <label class="form-label" for="materialPrice">Unit Price</label>
                        <input type="number" id="materialPrice" name="unit_price" class="form-control" placeholder="250.00" step="0.01" />
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

    newMaterialForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(newMaterialForm);

        try {
            // Step 1: Add the new material
            const res = await fetch("/materials/store", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": '{{ csrf_token() }}',
                    "Accept": "application/json"
                },
                body: formData
            });

            const data = await res.json();
            if (!data.success) {
                Swal.fire("Error", data.message || "Failed to add material.", "error");
                return;
            }

            // Step 2: Hide the modal
            bootstrap.Modal.getInstance(newMaterialModalEl)?.hide();
            Swal.fire("Success!", "Material added and attached to quotation.", "success");

            // Step 3: Reload the materials list in the Add Material modal
            if (window.modalMaterialHandler) {
                await window.modalMaterialHandler.loadMaterials();
            }

            // Step 4: Attach the new material to the current quotation
            const quotationId = "{{ $quotation->id }}"; // current quotation
            await fetch(`/quotation-materials/add-selected`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": '{{ csrf_token() }}',
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    quot_id: quotationId,
                    selected: [data.material.id],        // attach new material
                    quantity: { [data.material.id]: 1 }  // default quantity = 1
                })
            }).then(res => res.json())
              .then(resp => {
                  if (resp.success) {
                      // Update grand total dynamically
                      const grandTotalEl = document.getElementById("grandTotal");
                      if (grandTotalEl && resp.grand_total !== undefined) {
                          grandTotalEl.textContent = "₱" + parseFloat(resp.grand_total).toFixed(2);
                      }

                      // ✅ Reload quotation materials table dynamically
                      if (window.quotationMaterialHandler) {
                          window.quotationMaterialHandler.loadMaterials();
                      } else {
                          // fallback: reload the page if no JS handler
                          // window.location.reload();
                      }
                  } else {
                      Swal.fire("Error", resp.message || "Failed to attach material.", "error");
                  }
              });

            // Step 5: Reset form
            newMaterialForm.reset();

        } catch (err) {
            console.error(err);
            Swal.fire("Error", "Something went wrong!", "error");
        }
    });

    // Clean up backdrop properly
    newMaterialModalEl.addEventListener("hidden.bs.modal", function () {
        document.querySelectorAll(".modal-backdrop").forEach(el => el.remove());
        document.body.classList.remove("modal-open");
    });
});
</script>

  