{{-- resources/views/drafts.blade.php --}}
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Rejected Quotations</h5>
            <div class="d-flex align-items-center gap-2">
                <!-- 🔎 Search Bar -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <input type="text" id="search-rejected" class="form-control w-100" placeholder="Search quotations...">
                </div>
            </div>
        </div>

        <div class="card-datatable table-responsive pt-0">
            <table class="table" id="rejected-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Description</th>
                        <th>Client</th>
                        <th>Created By</th>
                        <th>Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <!-- Pagination -->
            <nav class="mt-3">
                <ul class="pagination justify-content-end" id="rejected-pagination"></ul>
            </nav>
        </div>
    </div>
</div>

<script>
class RejectedHandler {
    constructor() {
    this.currentPage = 1;
    this.perPage = 10; // show 10 per page
        this.searchQuery = "";
        this.quotations = [];

        this.initEvents();
        this.loadRejected();
    }

    initEvents() {
        document.getElementById("search-rejected").addEventListener("input", (e) => {
            this.searchQuery = e.target.value.toLowerCase();
            this.currentPage = 1;
            this.renderTable();
        });
    }

    loadRejected() {
        fetch("{{ url('quotations/rejected') }}")
            .then(res => res.json())
            .then(data => {
                this.quotations = data;
                this.renderTable();
            })
            .catch(error => console.error("Error loading rejected:", error));
    }

    getFilteredData() {
        if (!this.searchQuery) return this.quotations;
        return this.quotations.filter(q =>
            (q.subject && q.subject.toLowerCase().includes(this.searchQuery)) ||
            (q.description && q.description.toLowerCase().includes(this.searchQuery)) ||
            (q.client && ((q.client.first_name + " " + q.client.last_name).toLowerCase().includes(this.searchQuery))) ||
            (q.employee && q.employee.name.toLowerCase().includes(this.searchQuery))
        );
    }

    renderTable() {
        const table = document.getElementById("rejected-table").getElementsByTagName("tbody")[0];
        table.innerHTML = "";

        const filtered = this.getFilteredData();
        const start = (this.currentPage - 1) * this.perPage;
        const pageData = filtered.slice(start, start + this.perPage);

        pageData.forEach(q => {
            const row = `
                <tr>
                    <td>${q.subject}</td>
                    <td>${q.description}</td>
                    <td>${q.client ? (q.client.first_name + " " + q.client.last_name) : 'N/A'}</td>
                    <td>${q.employee ? q.employee.name : 'N/A'}</td>
                    <td><span class="badge bg-danger">${q.status ? q.status.status_name : 'Rejected'}</span></td>
                    <td>${new Date(q.created_at).toLocaleDateString()}</td>
                </tr>
            `;
            table.insertAdjacentHTML("beforeend", row);
        });

        this.renderPagination(filtered.length);
    }

    renderPagination(totalItems) {
        const totalPages = Math.ceil(totalItems / this.perPage);
        const pagination = document.getElementById("rejected-pagination");
        pagination.innerHTML = "";

        if (totalPages <= 1) return; // hide pagination if unnecessary

        // Previous
        const prevDisabled = this.currentPage === 1 ? "disabled" : "";
        pagination.insertAdjacentHTML("beforeend", `
            <li class="page-item ${prevDisabled}">
                <a class="page-link" href="#" data-page="${this.currentPage - 1}">&laquo;</a>
            </li>
        `);

        // Pages
        for (let i = 1; i <= totalPages; i++) {
            const active = i === this.currentPage ? "active" : "";
            pagination.insertAdjacentHTML("beforeend", `
                <li class="page-item ${active}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }

        // Next
        const nextDisabled = this.currentPage === totalPages ? "disabled" : "";
        pagination.insertAdjacentHTML("beforeend", `
            <li class="page-item ${nextDisabled}">
                <a class="page-link" href="#" data-page="${this.currentPage + 1}">&raquo;</a>
            </li>
        `);

        // Add click handlers
        pagination.querySelectorAll("a.page-link").forEach(link => {
            link.addEventListener("click", (e) => {
                e.preventDefault();
                const page = parseInt(link.dataset.page);
                if (!isNaN(page) && page >= 1 && page <= totalPages && page !== this.currentPage) {
                    this.currentPage = page;
                    this.renderTable();
                    this.renderPagination(totalItems);
                }
            });
        });
    }
}

new RejectedHandler();
</script>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
