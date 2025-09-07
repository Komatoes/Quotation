<!-- Offcanvas: Crete Quotation-->
<div class="offcanvas offcanvas-end" id="add-new-Quotation">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title">Add Quotation</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
            aria-label="Close"></button>
    </div>
    <div class="offcanvas-body flex-grow-1">
        <form class="add-new-record pt-0 row g-2" id="form-add-Quotation" onsubmit="return false">
            <div class="col-sm-12 form-control-validation">
                <label class="form-label" for="materialName">Quotation Name</label>
                <input type="text" id="materialName" class="form-control" name="name"
                    placeholder="Cement" required />
            </div>

            <div class="col-sm-12 form-control-validation">
                <label class="form-label" for="materialDescription">Description</label>
                <textarea id="materialDescription" name="description" class="form-control" rows="2"
                    placeholder="Optional description"></textarea>
            </div>

            <div class="col-sm-6 form-control-validation">
                <label class="form-label" for="materialQuantity">Quantity</label>
                <input type="number" id="materialQuantity" name="quantity" class="form-control"
                    placeholder="0" min="0" />
            </div>

            <div class="col-sm-6 form-control-validation">
                <label class="form-label" for="materialUnit">Unit</label>
                <input type="text" id="materialUnit" name="unit" class="form-control"
                    placeholder="pcs / kg / liters" />
            </div>

            <div class="col-sm-12 form-control-validation">
                <label class="form-label" for="materialPrice">Unit Price</label>
                <input type="number" id="materialPrice" name="unit_price" class="form-control"
                    placeholder="250.00" step="0.01" />
            </div>

            <div class="col-sm-12">
                <button type="submit" class="btn btn-primary data-submit me-sm-4 me-1"
                    onclick="addMaterial.add('form-add-Quotation')">Save</button>
                <button type="reset" class="btn btn-outline-secondary"
                    data-bs-dismiss="offcanvas">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    class AddMaterial {
        add(id) {
            const form = document.getElementById(id);
            const formData = new FormData(form);

            fetch("/add-Quotation", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    title: data.message,
                    icon: "success",
                    draggable: true
                });

                // optional: reset form and close offcanvas
                form.reset();
                const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('add-new-Quotation'));
                offcanvas.hide();
            })
            .catch(error => {
                console.error("Error:", error);
                Swal.fire("Something went wrong!", "", "error");
            });
        }
    }

    const addMaterial = new AddMaterial();
</script>
<!--/ Offcanvas: Add Quotation -->
