<!-- New Material Modal -->
<div class="modal fade" id="newMaterialModal" tabindex="-1" aria-labelledby="newMaterialModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="newMaterialForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="newMaterialModalLabel">Add New Material</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="materialName" class="form-label">Material Name</label>
                        <input type="text" class="form-control" id="materialName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="materialUnit" class="form-label">Unit</label>
                        <input type="text" class="form-control" id="materialUnit" name="unit">
                    </div>
                    <div class="mb-3">
                        <label for="materialPrice" class="form-label">Unit Price</label>
                        <input type="number" class="form-control" id="materialPrice" name="unit_price" step="0.01">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const newMaterialForm = document.getElementById('newMaterialForm');
    const newMaterialModalEl = document.getElementById('newMaterialModal');

    // Submit handler
    newMaterialForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        try {
            const res = await fetch('/materials/store', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire('Success!', 'Material added.', 'success');
                bootstrap.Modal.getInstance(newMaterialModalEl).hide();

                if (window.modalMaterialHandler) window.modalMaterialHandler.loadMaterials();
                newMaterialForm.reset();
            } else {
                Swal.fire('Error', data.message || 'Failed to add material.', 'error');
            }
        } catch (error) {
            console.error(error);
            Swal.fire('Error', 'Something went wrong!', 'error');
        }
    });

    // Ensure backdrop cleanup only (no reopen)
    newMaterialModalEl.addEventListener('hidden.bs.modal', function () {
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
    });
});
</script>


