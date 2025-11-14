{{-- resources/views/materials.blade.php --}}
{{-- Do NOT extend layout here since it's included inside dashboard --}}
<div class="col-12">
    <div class="card">
        <!-- Card Header -->
        <div class="card-header flex-wrap d-flex justify-content-between align-items-center gap-2">
            <h5 class="mb-0 flex-grow-1">Materials</h5>
            <div class="d-flex flex-wrap justify-content-end align-items-center gap-2 w-100 w-sm-auto">
                <!-- 🔎 Search Bar -->
                <input type="text" id="search-material" class="form-control"
                    style="max-width: 250px; flex: 1 1 auto;" placeholder="Search materials...">

                <!-- ➕ Add Material Button -->
                <button class="btn btn-primary flex-shrink-0" id="btn-add-material">
                    <i class="fa-solid fa-plus me-1"></i> Add Material
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
                <input type="text" id="materialName" class="form-control" name="name" placeholder="Cement" required />
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

<!-- Responsive fix for mobile view -->
<style>
@media (max-width: 576px) {
    #search-material {
        width: 100% !important;
    }

    .card-header > div.d-flex {
        flex-direction: column;
        align-items: stretch !important;
    }

    #btn-add-material {
        width: 100%;
    }
}
</style>

<script>
/* =====================================================
   AddMaterial (handles both Add + Edit)
   ===================================================== */
class AddMaterial {
    constructor() {
        this.form = document.getElementById("form-add-material");

        // Handle submit
        this.form.addEventListener("submit", (e) => this.onSubmit(e));

        // Reset when offcanvas closes
        const off = document.getElementById('add-new-material');
        off.addEventListener('hidden.bs.offcanvas', () => this.resetForm());
    }

    onSubmit(e) {
        e.preventDefault();

        const isEditing = this.form.dataset.editing === "true";
        const id = this.form.dataset.id;
        const formData = new FormData(this.form);

        const url = isEditing ? `/materials/update/${id}` : `/materials/store`;
        const method = "POST"; // keep consistent with Laravel form routes

        fetch(url, {
                method,
                headers: {
                    "X-CSRF-TOKEN": '{{ csrf_token() }}',
                    "Accept": "application/json"
                },
                body: formData,
                credentials: "same-origin"
            })
            .then(async (res) => {
                const json = await res.json();
                return { ok: res.ok, json };
            })
            .then(({ ok, json }) => {
                if (!ok) {
                    if (json.errors) {
                        const messages = Object.values(json.errors)
                            .flat()
                            .map(msg => `<li>${msg}</li>`)
                            .join("");
                        Swal.fire({
                            title: "Validation Error",
                            html: `<ul style='text-align:left; margin:0; padding-left:1.5em;'>${messages}</ul>`,
                            icon: "warning"
                        });
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: json.message || (isEditing ? "Failed to update material." : "Failed to add material."),
                            icon: "error"
                        });
                    }
                    return;
                }

                Swal.fire({
                    title: json.message || (isEditing ? "Material updated successfully!" : "Material added successfully!"),
                    icon: "success"
                });

                const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('add-new-material'));
                if (offcanvas) offcanvas.hide();

                if (window.materialHandler) window.materialHandler.loadMaterials();
                this.resetForm();
            })
            .catch(err => {
                console.error("Error submitting material:", err);
                Swal.fire({
                    title: "Something went wrong!",
                    text: "Please try again later.",
                    icon: "error"
                });
            });
    }

    resetForm() {
        this.form.reset();
        delete this.form.dataset.editing;
        delete this.form.dataset.id;
    }
}

if (!window.addMaterial) {
    window.addMaterial = new AddMaterial();
}

/* =====================================================
   MaterialHandler (with pagination restored)
   ===================================================== */
class MaterialHandler {
    constructor() {
        this.materials = [];
        this.filtered = [];
        this.currentPage = 1;
        this.pageSize = 10;

        this.searchInput = document.getElementById("search-material");
        if (this.searchInput) {
            this.searchInput.addEventListener("input", (e) => this.filterMaterials(e.target.value));
        }

        this.paginationEl = document.getElementById("materials-pagination");

        this.loadMaterials();
    }

    loadMaterials() {
        fetch('/materials/list')
            .then(res => res.json())
            .then(data => {
                this.materials = data;
                this.filtered = data;
                this.currentPage = 1;
                this.renderTable();
                this.renderPagination();
            })
            .catch(err => {
                console.error("Error loading materials:", err);
                Swal.fire("Failed to load materials", "", "error");
            });
    }

    filterMaterials(searchText) {
        const query = (searchText || '').toLowerCase();
        this.filtered = this.materials.filter(m =>
            (m.name || '').toLowerCase().includes(query) ||
            (m.description || '').toLowerCase().includes(query) ||
            (m.unit || '').toLowerCase().includes(query)
        );
        this.currentPage = 1;
        this.renderTable();
        this.renderPagination();
    }

    get paginatedItems() {
        const start = (this.currentPage - 1) * this.pageSize;
        const end = start + this.pageSize;
        return this.filtered.slice(start, end);
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

        this.paginatedItems.forEach(material => {
            const row = `
                <tr>
                    <td>${material.name}</td>
                    <td>${material.description || ''}</td>
                    <td>${material.unit}</td>
                    <td>${material.unit_price}</td>
                    <td>
                        <button class="btn btn-sm btn-warning edit-btn" data-id="${material.id}">Edit</button>
                    </td>
                </tr>`;
            tbody.insertAdjacentHTML("beforeend", row);
        });

        // Attach edit handlers
        tbody.querySelectorAll(".edit-btn").forEach(btn => {
            btn.addEventListener("click", (e) => {
                const id = e.currentTarget.dataset.id;
                this.openEditOffcanvas(id);
            });
        });
    }

    renderPagination() {
        if (!this.paginationEl) return;

        const totalPages = Math.ceil(this.filtered.length / this.pageSize);
        this.paginationEl.innerHTML = "";

        if (totalPages <= 1) return; // hide pagination if unnecessary

        // Previous
        const prevDisabled = this.currentPage === 1 ? "disabled" : "";
        this.paginationEl.insertAdjacentHTML("beforeend", `
            <li class="page-item ${prevDisabled}">
                <a class="page-link" href="#" data-page="${this.currentPage - 1}">&laquo;</a>
            </li>
        `);

        // Pages
        for (let i = 1; i <= totalPages; i++) {
            const active = i === this.currentPage ? "active" : "";
            this.paginationEl.insertAdjacentHTML("beforeend", `
                <li class="page-item ${active}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }

        // Next
        const nextDisabled = this.currentPage === totalPages ? "disabled" : "";
        this.paginationEl.insertAdjacentHTML("beforeend", `
            <li class="page-item ${nextDisabled}">
                <a class="page-link" href="#" data-page="${this.currentPage + 1}">&raquo;</a>
            </li>
        `);

        // Add click handlers
        this.paginationEl.querySelectorAll("a.page-link").forEach(link => {
            link.addEventListener("click", (e) => {
                e.preventDefault();
                const page = parseInt(e.target.dataset.page);
                if (page >= 1 && page <= totalPages && page !== this.currentPage) {
                    this.currentPage = page;
                    this.renderTable();
                    this.renderPagination();
                }
            });
        });
    }

    openEditOffcanvas(id) {
        const material = this.materials.find(m => m.id == id);
        if (!material) return;

        const form = document.getElementById("form-add-material");
        form.dataset.editing = "true";
        form.dataset.id = material.id;

        form.querySelector("[name='name']").value = material.name;
        form.querySelector("[name='description']").value = material.description || "";
        form.querySelector("[name='unit']").value = material.unit;
        form.querySelector("[name='unit_price']").value = material.unit_price;

        document.getElementById("offcanvas-title").innerText = "Edit Material";
        document.getElementById("form-submit-btn").innerText = "Update";

        const offcanvasEl = document.getElementById('add-new-material');
        const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
        offcanvas.show();
    }

    resetForm() {
        const form = document.getElementById("form-add-material");
        form.reset();
        delete form.dataset.editing;
        delete form.dataset.id;

        document.getElementById("offcanvas-title").innerText = "Add Material";
        document.getElementById("form-submit-btn").innerText = "Save";
    }
}

/* =====================================================
   Initialize on DOM load
   ===================================================== */
window.addEventListener("DOMContentLoaded", () => {
    if (!window.materialHandler) window.materialHandler = new MaterialHandler();

    const addBtn = document.getElementById("btn-add-material");
    if (addBtn) {
        addBtn.addEventListener("click", () => {
            const offcanvasEl = document.getElementById('add-new-material');
            const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);

            const form = document.getElementById("form-add-material");
            form.reset();
            delete form.dataset.editing;
            form.onsubmit = null;

            document.getElementById("offcanvas-title").innerText = "Add Material";
            document.getElementById("form-submit-btn").innerText = "Save";

            offcanvas.show();
        });
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
