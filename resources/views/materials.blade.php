{{-- resources/views/materials.blade.php --}}
{{-- Do NOT extend layout here since it's included inside dashboard --}}
<div class="col-12">
    <div class="card">
        <!-- Card Header -->
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Materials</h5>
            <div class="d-flex align-items-center gap-2">
                <!-- 🔎 Search Bar (longer) -->
                <input type="text" id="search-material" class="form-control" style="width: 250px;" placeholder="Search materials...">

                <!-- ➕ Add Material Button -->
                <button class="btn btn-primary" id="btn-add-material">
                    <i class="ti ti-plus me-1"></i> Add Material
                </button>
            </div>
        </div>

        <!-- Table -->
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
                <tbody></tbody>
            </table>
        </div>

        <!-- Card Footer with Pagination -->
        <div class="card-footer d-flex justify-content-end">
            <nav>
                <ul class="pagination pagination-rounded mb-0" id="materials-pagination"></ul>
            </nav>
        </div>
    </div>
</div>
<!-- Offcanvas: Add/Edit Material -->
<div class="offcanvas offcanvas-end" id="add-new-material">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="offcanvas-title">Add Material</h5>
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
                <button type="submit" class="btn btn-primary data-submit me-sm-4 me-1" id="form-submit-btn">Save</button>
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
            </div>
        </form>
    </div>
</div>

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
            fetch('/materials/store', {
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
            this.form.onsubmit = null;
            // Reset UI
            document.getElementById("offcanvas-title").innerText = "Add Material";
            document.getElementById("form-submit-btn").innerText = "Save";
        }
    }

    // Initialize add handler once
    if (!window.addMaterial) {
        window.addMaterial = new AddMaterial();
    }
</script>

<script>
class MaterialHandler {
    constructor() {
        this.materials = [];
        this.filtered = [];
        this.currentPage = 1;
        this.pageSize = 5;

        this.loadMaterials();

        // Search listener
        document.getElementById("search-material").addEventListener("input", (e) => {
            const term = e.target.value.toLowerCase();
            this.filtered = this.materials.filter(m =>
                m.name.toLowerCase().includes(term) ||
                (m.description && m.description.toLowerCase().includes(term))
            );
            this.currentPage = 1;
            this.renderTable();
        });
    }

    loadMaterials() {
        fetch('/materials/list')
            .then(res => res.json())
            .then(materials => {
                this.materials = materials;
                this.filtered = materials; // default = all
                this.renderTable();
            })
            .catch(error => console.error("Error loading materials:", error));
    }

    renderTable() {
        const tbody = document.querySelector("#materials-table tbody");
        tbody.innerHTML = "";

        // Pagination math
        const start = (this.currentPage - 1) * this.pageSize;
        const end = start + this.pageSize;
        const pageItems = this.filtered.slice(start, end);

        // Rows
        pageItems.forEach(material => {
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

        this.renderPagination();
    }

renderPagination() {
    const pagination = document.getElementById("materials-pagination");
    pagination.innerHTML = "";

    const totalPages = Math.ceil(this.filtered.length / this.pageSize);
    if (totalPages <= 1) return;

    const ul = document.createElement("ul");
    ul.className = "pagination pagination-rounded";

    // First button
    const firstLi = document.createElement("li");
    firstLi.className = "page-item first" + (this.currentPage === 1 ? " disabled" : "");
    firstLi.innerHTML = `
        <a class="page-link" href="javascript:void(0);">
            <i class="icon-base ti tabler-chevrons-left icon-sm"></i>
        </a>`;
    firstLi.addEventListener("click", () => {
        if (this.currentPage > 1) {
            this.currentPage = 1;
            this.renderTable();
        }
    });
    ul.appendChild(firstLi);

    // Prev button
    const prevLi = document.createElement("li");
    prevLi.className = "page-item prev" + (this.currentPage === 1 ? " disabled" : "");
    prevLi.innerHTML = `
        <a class="page-link" href="javascript:void(0);">
            <i class="icon-base ti tabler-chevron-left icon-sm"></i>
        </a>`;
    prevLi.addEventListener("click", () => {
        if (this.currentPage > 1) {
            this.currentPage--;
            this.renderTable();
        }
    });
    ul.appendChild(prevLi);

    // Numbered buttons
    for (let i = 1; i <= totalPages; i++) {
        const li = document.createElement("li");
        li.className = "page-item" + (i === this.currentPage ? " active" : "");
        li.innerHTML = `<a class="page-link" href="javascript:void(0);">${i}</a>`;
        li.addEventListener("click", () => {
            this.currentPage = i;
            this.renderTable();
        });
        ul.appendChild(li);
    }

    // Next button
    const nextLi = document.createElement("li");
    nextLi.className = "page-item next" + (this.currentPage === totalPages ? " disabled" : "");
    nextLi.innerHTML = `
        <a class="page-link" href="javascript:void(0);">
            <i class="icon-base ti tabler-chevron-right icon-sm"></i>
        </a>`;
    nextLi.addEventListener("click", () => {
        if (this.currentPage < totalPages) {
            this.currentPage++;
            this.renderTable();
        }
    });
    ul.appendChild(nextLi);

    // Last button
    const lastLi = document.createElement("li");
    lastLi.className = "page-item last" + (this.currentPage === totalPages ? " disabled" : "");
    lastLi.innerHTML = `
        <a class="page-link" href="javascript:void(0);">
            <i class="icon-base ti tabler-chevrons-right icon-sm"></i>
        </a>`;
    lastLi.addEventListener("click", () => {
        if (this.currentPage < totalPages) {
            this.currentPage = totalPages;
            this.renderTable();
        }
    });
    ul.appendChild(lastLi);

    pagination.appendChild(ul);
}


    }

window.materialHandler = new MaterialHandler();
</script>

<script>
    class MaterialHandler {
        constructor() {
            this.materials = [];
            this.filtered = [];
            this.searchInput = document.getElementById("search-material");

            if (this.searchInput) {
                this.searchInput.addEventListener("input", (e) => {
                    this.filterMaterials(e.target.value);
                });
            }

            this.loadMaterials();
        }

        loadMaterials() {
            fetch('/materials/list')
                .then(res => res.json())
                .then(materials => {
                    this.materials = materials;
                    this.filtered = materials;
                    this.renderTable();
                })
                .catch(error => console.error("Error loading materials:", error));
        }

        filterMaterials(searchText) {
            const query = searchText.toLowerCase();
            this.filtered = this.materials.filter(m =>
                m.name.toLowerCase().includes(query) ||
                (m.description || '').toLowerCase().includes(query) ||
                (m.unit || '').toLowerCase().includes(query)
            );
            this.renderTable();
        }

        renderTable() {
            const table = document.getElementById("materials-table");
            if (!table) return;

            const tbody = table.querySelector("tbody");
            tbody.innerHTML = "";

            if (this.filtered.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center">No materials found</td></tr>`;
                return;
            }

            this.filtered.forEach(material => {
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
        }
    }

    // Initialize
    window.materialHandler = new MaterialHandler();
</script>


<script>
    // Unified Add Button JS
    document.addEventListener("DOMContentLoaded", () => {
        document.getElementById("btn-add-material").addEventListener("click", () => {
            const offcanvasEl = document.getElementById('add-new-material');
            const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);

            // Reset form & UI
            const form = document.getElementById("form-add-material");
            form.reset();
            delete form.dataset.editing;
            form.onsubmit = null;

            document.getElementById("offcanvas-title").innerText = "Add Material";
            document.getElementById("form-submit-btn").innerText = "Save";

            offcanvas.show();
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
