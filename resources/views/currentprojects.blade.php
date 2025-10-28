{{-- resources/views/approved.blade.php --}}
<meta name="viewport" content="width=device-width, initial-scale=1">
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Current Projects</h5>
            <div class="d-flex align-items-center gap-2">
                <!-- 🔎 Search Bar -->
                <input type="text" id="search-approved" class="form-control" placeholder="Search projects...">
            </div>
        </div>

        <div class="table-responsive pt-0">
            <table class="table table-bordered table-striped align-middle" id="approved-table">
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
                    <ul class="pagination pagination-rounded" id="approved-pagination"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<script>
    class ApprovedHandler {
        constructor() {
            this.currentPage = 1;
            this.perPage = 5;
            this.searchQuery = "";
            this.quotations = [];

            this.initEvents();
            this.loadApproved();
        }

        initEvents() {
            document.getElementById("search-approved").addEventListener("input", (e) => {
                this.searchQuery = e.target.value.toLowerCase();
                this.currentPage = 1;
                this.renderTable();
            });
        }

        loadApproved() {
            fetch("{{ url('quotations/approved') }}")
                .then(res => res.json())
                .then(data => {
                    this.quotations = data;
                    this.renderTable();
                })
                .catch(error => console.error("Error loading approved:", error));
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
            const tbody = document.getElementById("approved-table").getElementsByTagName("tbody")[0];
            tbody.innerHTML = "";

            const filtered = this.getFilteredData();
            const start = (this.currentPage - 1) * this.perPage;
            const pageData = filtered.slice(start, start + this.perPage);

            pageData.forEach(q => {
                // Link with query parameter
                const subjectLink = `<a href="/view-report/${q.id}">${q.subject}</a>`;



                const row = `
            <tr>
                <td>${subjectLink}</td>
                <td>${q.description}</td>
                <td>${q.client ? (q.client.first_name + " " + q.client.last_name) : 'N/A'}</td>
                <td>${q.employee ? q.employee.name : 'N/A'}</td>
                <td><span class="badge bg-success">${q.status ? q.status.status_name : 'Approved'}</span></td>
                <td>${new Date(q.created_at).toLocaleDateString()}</td>
            </tr>
        `;
                tbody.insertAdjacentHTML("beforeend", row);
            });

            this.renderPagination(filtered.length);
        }



        renderPagination(totalItems) {
            const totalPages = Math.ceil(totalItems / this.perPage);
            const pagination = document.getElementById("approved-pagination");
            pagination.innerHTML = "";

            if (totalPages <= 1) return; // hide if only 1 page

            // Prev button
            const prevDisabled = this.currentPage === 1 ? "disabled" : "";
            pagination.insertAdjacentHTML("beforeend", `
            <li class="page-item ${prevDisabled}">
                <a class="page-link" href="javascript:void(0);"><i class="ti ti-chevron-left"></i></a>
            </li>
        `);

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                const active = i === this.currentPage ? "active" : "";
                pagination.insertAdjacentHTML("beforeend", `
                <li class="page-item ${active}">
                    <a class="page-link" href="javascript:void(0);">${i}</a>
                </li>
            `);
            }

            // Next button
            const nextDisabled = this.currentPage === totalPages ? "disabled" : "";
            pagination.insertAdjacentHTML("beforeend", `
            <li class="page-item ${nextDisabled}">
                <a class="page-link" href="javascript:void(0);"><i class="ti ti-chevron-right"></i></a>
            </li>
        `);

            // Event binding
            pagination.querySelectorAll(".page-link").forEach((btn) => {
                btn.addEventListener("click", () => {
                    if (btn.querySelector(".ti-chevron-left")) {
                        if (this.currentPage > 1) this.currentPage--;
                    } else if (btn.querySelector(".ti-chevron-right")) {
                        if (this.currentPage < totalPages) this.currentPage++;
                    } else {
                        this.currentPage = parseInt(btn.textContent);
                    }
                    this.renderTable();
                });
            });
        }
    }

    new ApprovedHandler();
</script>

<!-- In head section -->
<link href="{{ asset('assets/css/responsive-fixes.css') }}" rel="stylesheet">

<!-- Before closing body tag -->
<script src="{{ asset('assets/js/responsive-fixes.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeSidebar();
    initializeResponsiveTables();
});
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
