{{-- resources/views/materials.blade.php --}}
{{-- Do NOT extend layout here since it's included inside dashboard --}}
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Materials</h5>
            <!-- Add Material Button -->
            <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#add-new-material">
                <i class="ti ti-plus me-1"></i> Add Material
            </button>
        </div>
        <div class="card-datatable table-responsive pt-0">
            <table class="table" id="materials-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Unit</th>
                        <th>Unit Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- JS will populate this -->
                </tbody>
            </table>
        </div>
    </div>
</div>


<!-- Offcanvas: Add Material -->
<div class="offcanvas offcanvas-end" id="add-new-material">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title">Add Material</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body flex-grow-1">
        <form class="add-new-record pt-0 row g-2" id="form-add-material">
            <div class="col-sm-12 form-control-validation">
                <label class="form-label" for="materialName">Material Name</label>
                <input type="text" id="materialName" class="form-control" name="name" placeholder="Cement"
                    required />
            </div>

            <div class="col-sm-12 form-control-validation">
                <label class="form-label" for="materialDescription">Description</label>
                <textarea id="materialDescription" name="description" class="form-control" rows="2"
                    placeholder="Optional description"></textarea>
            </div>

            <div class="col-sm-6 form-control-validation">
                <label class="form-label" for="materialUnit">Unit</label>
                <input type="text" id="materialUnit" name="unit" class="form-control"
                    placeholder="pcs / kg / liters" />
            </div>

            <div class="col-sm-12 form-control-validation">
                <label class="form-label" for="materialPrice">Unit Price</label>
                <input type="number" id="materialPrice" name="unit_price" class="form-control" placeholder="250.00"
                    step="0.01" />
            </div>

            <div class="col-sm-12">
                <button type="submit" class="btn btn-primary data-submit me-sm-4 me-1">Save</button>
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
            </div>
        </form>
    </div>
</div>


<script>
    // Handles table loading
    class MaterialHandler {
        constructor() {
            this.loadMaterials();
        }

        loadMaterials() {
            fetch('/materials/list')
                .then(res => res.json())
                .then(materials => {
                    const table = document.getElementById("materials-table");
                    const tbody = table.getElementsByTagName("tbody")[0];
                    tbody.innerHTML = ""; // Clear existing rows

                    materials.forEach(material => {
                        const row = `
                            <tr>
                                <td>${material.name}</td>
                                <td>${material.description || ''}</td>
                                <td>${material.unit}</td>
                                <td>${material.unit_price}</td>
                                <td>
                                <button class="btn btn-sm btn-warning edit-btn" data-id="${material.id}">Edit</button>
                                </td>
                            </tr>
                        `;
                        tbody.insertAdjacentHTML("beforeend", row);
                    });
                })
                .catch(error => console.error("Error loading materials:", error));
        }
    }

    // Initialize and make it global
    window.materialHandler = new MaterialHandler();
</script>

<script>
    class AddMaterial {
        constructor() {
            this.form = document.getElementById("form-add-material");
            // Ensure we don't double-run when EditMaterial sets custom onsubmit
            this.form.addEventListener("submit", (e) => this.onSubmit(e));
            // Reset editing flag when offcanvas closes
            const off = document.getElementById('add-new-material');
            off.addEventListener('hidden.bs.offcanvas', () => {
                this.resetForm();
            });
        }

        onSubmit(e) {
            e.preventDefault();
            // If editing, skip create (EditMaterial will handle)
            if (this.form.dataset.editing === "true") return;

            const formData = new FormData(this.form);
            fetch('/add-material', {
                    method: 'POST',
                    headers: {
                        "X-CSRF-TOKEN": '{{ csrf_token() }}'
                    },
                    body: formData,
                    credentials: "same-origin"
                })
                .then(res => {
                    return res.json().then(json => ({
                        ok: res.ok,
                        json
                    }));
                })
                .then(({
                    ok,
                    json
                }) => {
                    if (!ok) {
                        const msg = json.message || (json.errors ? Object.values(json.errors).flat().join(
                            '\n') : 'Failed to add material');
                        Swal.fire({
                            title: msg,
                            icon: 'error'
                        });
                        return;
                    }

                    Swal.fire({
                        title: json.message || 'Material added',
                        icon: 'success'
                    });
                    const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById(
                        'add-new-material'));
                    if (offcanvas) offcanvas.hide();

                    if (window.materialHandler) window.materialHandler.loadMaterials();
                    this.resetForm();
                })
                .catch(err => {
                    console.error("Error adding material:", err);
                    Swal.fire("Something went wrong!", "", "error");
                });
        }

        resetForm() {
            this.form.reset();
            delete this.form.dataset.editing;
            // restore default submit behavior
            this.form.onsubmit = null;
        }
    }

    // Initialize add handler once
    if (!window.addMaterial) {
        window.addMaterial = new AddMaterial();
    }
</script>

<script>
    class EditMaterial {
        constructor() {
            document.addEventListener("click", (e) => {
                if (e.target.classList.contains("edit-btn")) {
                    const id = e.target.dataset.id;
                    this.openEditForm(id);
                }
            });

            // When offcanvas closes, ensure editing flag cleared
            document.getElementById('add-new-material').addEventListener('hidden.bs.offcanvas', () => {
                const form = document.getElementById("form-add-material");
                if (form) {
                    delete form.dataset.editing;
                    form.onsubmit = null;
                }
            });
        }

        openEditForm(id) {
            // Fetch material data and populate form
            fetch(`/materials/list`)
                .then(res => res.json())
                .then(materials => {
                    const material = materials.find(m => m.id == id);
                    if (!material) return;

                    // Fill form
                    document.getElementById("materialName").value = material.name;
                    document.getElementById("materialDescription").value = material.description || '';
                    document.getElementById("materialUnit").value = material.unit;
                    document.getElementById("materialPrice").value = material.unit_price;

                    // Mark form as editing so AddMaterial handler will skip create
                    const form = document.getElementById("form-add-material");
                    form.dataset.editing = "true";

                    // Change submit behavior to update
                    form.onsubmit = (e) => {
                        e.preventDefault();
                        this.update(id, new FormData(form));
                    };

                    // Open offcanvas
                    const offcanvas = new bootstrap.Offcanvas(document.getElementById('add-new-material'));
                    offcanvas.show();
                });
        }

        update(id, formData) {
            fetch(`/materials/update/${id}`, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": '{{ csrf_token() }}'
                    },
                    body: formData,
                    credentials: "same-origin"
                })
                .then(res => res.json().then(json => ({
                    ok: res.ok,
                    json
                })))
                .then(({
                    ok,
                    json
                }) => {
                    if (!ok) {
                        const msg = json.message || (json.errors ? Object.values(json.errors).flat().join(
                            '\n') : 'Update failed');
                        Swal.fire({
                            title: msg,
                            icon: 'error'
                        });
                        return;
                    }

                    Swal.fire({
                        title: json.message || 'Updated',
                        icon: "success"
                    });

                    const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById(
                        'add-new-material'));
                    if (offcanvas) offcanvas.hide();

                    // clear editing flag and restore form
                    const form = document.getElementById("form-add-material");
                    if (form) {
                        delete form.dataset.editing;
                        form.onsubmit = null;
                    }

                    if (window.materialHandler) {
                        window.materialHandler.loadMaterials();
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    Swal.fire("Something went wrong!", "", "error");
                });
        }
    }

    // Initialize once
    if (!window.editMaterial) {
        window.editMaterial = new EditMaterial();
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
