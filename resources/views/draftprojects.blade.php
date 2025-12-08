{{-- resources/views/drafts.blade.php --}}
<meta name="viewport" content="width=device-width, initial-scale=1">

@if (Auth::user()->can('view_drafts'))
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Draft Quotations</h5>
                <div class="d-flex align-items-center gap-2">
                    <!-- 🔎 Search Bar -->
                    <input type="text" id="search-drafts" class="form-control" placeholder="Search drafts...">
                </div>
            </div>

            <div class="table-responsive pt-0">
                <table class="table table-bordered table-striped align-middle" id="drafts-table">
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

                <!-- 📄 Pagination -->
                <div class="card-footer d-flex justify-content-end">
                    <nav>
                        <ul class="pagination pagination-rounded" id="drafts-pagination"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <script>
        class DraftHandler {
            constructor() {
                this.currentPage = 1;
                this.perPage = 10; // items per page
                this.searchQuery = "";
                this.quotations = [];

                this.initEvents();
                this.loadDrafts();
            }

            initEvents() {
                document.getElementById("search-drafts").addEventListener("input", (e) => {
                    this.searchQuery = e.target.value.toLowerCase();
                    this.currentPage = 1;
                    this.renderTable();
                });
            }

            loadDrafts() {
                fetch("{{ url('quotations/drafts') }}")
                    .then(res => res.json())
                    .then(data => {
                        this.quotations = data;
                        this.renderTable();
                    })
                    .catch(error => console.error("Error loading drafts:", error));
            }

            getFilteredData() {
                if (!this.searchQuery) return this.quotations;
                return this.quotations.filter(q =>
                    (q.subject && q.subject.toLowerCase().includes(this.searchQuery)) ||
                    (q.description && q.description.toLowerCase().includes(this.searchQuery)) ||
                    (q.client && ((q.client.first_name + " " + q.client.last_name).toLowerCase().includes(this
                        .searchQuery))) ||
                    (q.employee && q.employee.name.toLowerCase().includes(this.searchQuery))
                );
            }

            renderTable() {
                const tbody = document.getElementById("drafts-table").getElementsByTagName("tbody")[0];
                tbody.innerHTML = "";

                const filtered = this.getFilteredData();
                const start = (this.currentPage - 1) * this.perPage;
                const pageData = filtered.slice(start, start + this.perPage);

                pageData.forEach(q => {
                    // 🔗 Make subject clickable — styled properly
                    const subjectLink = `<a href="/quotations/${q.id}">${q.subject}</a>`;

                    const row = `
            <tr>
                <td>${subjectLink}</td>
                <td>${q.description}</td>
                <td>${q.client ? (q.client.first_name + " " + q.client.last_name) : 'N/A'}</td>
                <td>${q.employee ? q.employee.name : 'N/A'}</td>
                <td>
                    <span class="badge bg-warning text-dark">
                        ${q.status ? q.status.status_name : 'Draft'}
                    </span>
                </td>
                <td>${new Date(q.created_at).toLocaleDateString()}</td>
            </tr>
        `;
                    tbody.insertAdjacentHTML("beforeend", row);
                });

                this.renderPagination(filtered.length);
            }



            renderPagination(totalItems) {
                const totalPages = Math.ceil(totalItems / this.perPage);
                const pagination = document.getElementById("drafts-pagination");
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

        new DraftHandler();
    </script>
@else
    {{-- Staff users cannot view draft quotations --}}
    <div class="col-12">
        <div class="card border-danger">
            <div class="card-body text-center py-5">
                <i class="fa-solid fa-lock text-warning" style="font-size: 2rem;"></i>
                <h5 class="text-warning mt-3 mb-2">Draft Quotations</h5>
                <p class="text-muted mb-0">This section is restricted to owner only.</p>
            </div>
        </div>
    </div>
@endif

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
