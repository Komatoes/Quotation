{{-- resources/views/archive.blade.php --}}
<meta name="viewport" content="width=device-width, initial-scale=1">
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Quotation and Project Archive</h5>
            <div class="d-flex align-items-center gap-2">
                <input type="text" id="search-archive" class="form-control" placeholder="Search archived quotations...">
            </div>
        </div>

        <div class="table-responsive pt-0">
            <table class="table table-bordered table-striped align-middle" id="archive-table">
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

            <div class="card-footer d-flex justify-content-end">
                <nav>
                    <ul class="pagination pagination-rounded" id="archive-pagination"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<script>
    class ArchiveHandler {
        constructor() {
            this.currentPage = 1;
            this.perPage = 5;
            this.searchQuery = "";
            this.quotations = [];

            this.initEvents();
            this.loadArchive();
        }

        initEvents() {
            document.getElementById("search-archive").addEventListener("input", (e) => {
                this.searchQuery = e.target.value.toLowerCase();
                this.currentPage = 1;
                this.renderTable();
            });
        }

        loadArchive() {
            fetch("{{ url('quotations/archive') }}") // 👈 route for rejected & completed
                .then(res => res.json())
                .then(data => {
                    this.quotations = data;
                    this.renderTable();
                })
                .catch(error => console.error("Error loading archive:", error));
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
            const tbody = document.getElementById("archive-table").getElementsByTagName("tbody")[0];
            tbody.innerHTML = "";

            const filtered = this.getFilteredData();
            const start = (this.currentPage - 1) * this.perPage;
            const pageData = filtered.slice(start, start + this.perPage);

            pageData.forEach(q => {
                const subjectLink = `<a href="/quotations/${q.id}">${q.subject}</a>`;
                const statusName = q.status ? q.status.status_name : 'Unknown';

                // 🎨 Different badge colors for status
                let badgeClass = 'bg-secondary';
                if (statusName.toLowerCase() === 'rejected') badgeClass = 'bg-danger';
                if (statusName.toLowerCase() === 'completed') badgeClass = 'bg-success';

                const row = `
                    <tr>
                        <td>${subjectLink}</td>
                        <td>${q.description}</td>
                        <td>${q.client ? (q.client.first_name + " " + q.client.last_name) : 'N/A'}</td>
                        <td>${q.employee ? q.employee.name : 'N/A'}</td>
                        <td><span class="badge ${badgeClass}">${statusName}</span></td>
                        <td>${new Date(q.created_at).toLocaleDateString()}</td>
                    </tr>
                `;
                tbody.insertAdjacentHTML("beforeend", row);
            });

            this.renderPagination(filtered.length);
        }

        renderPagination(totalItems) {
            const totalPages = Math.ceil(totalItems / this.perPage);
            const pagination = document.getElementById("archive-pagination");
            pagination.innerHTML = "";

            const prevDisabled = this.currentPage === 1 ? "disabled" : "";
            pagination.insertAdjacentHTML("beforeend", `
                <li class="page-item ${prevDisabled}">
                    <a class="page-link" href="javascript:void(0);"><i class="ti ti-chevron-left"></i></a>
                </li>
            `);

            for (let i = 1; i <= totalPages; i++) {
                const active = i === this.currentPage ? "active" : "";
                pagination.insertAdjacentHTML("beforeend", `
                    <li class="page-item ${active}">
                        <a class="page-link" href="javascript:void(0);">${i}</a>
                    </li>
                `);
            }

            const nextDisabled = this.currentPage === totalPages ? "disabled" : "";
            pagination.insertAdjacentHTML("beforeend", `
                <li class="page-item ${nextDisabled}">
                    <a class="page-link" href="javascript:void(0);"><i class="ti ti-chevron-right"></i></a>
                </li>
            `);

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

    new ArchiveHandler();
</script>
