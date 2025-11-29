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
    @php
        // Determine if the current user is a staff member.
        // We try a few common patterns to avoid breaking if the project uses a role relation or hasRole helper.
        $isStaff = false;
        if (Auth::check()) {
            $user = Auth::user();
            if (method_exists($user, 'hasRole')) {
                try {
                    $isStaff = (bool) $user->hasRole('staff');
                } catch (\Throwable $e) {
                    $isStaff = false;
                }
            } elseif (isset($user->role)) {
                if (is_string($user->role)) {
                    $isStaff = strtolower($user->role) === 'staff';
                } elseif (is_object($user->role) && isset($user->role->name)) {
                    $isStaff = strtolower($user->role->name) === 'staff';
                }
            } elseif (isset($user->role_name)) {
                // fallback if role_name attribute exists
                $isStaff = strtolower($user->role_name) === 'staff';
            }
        }
    @endphp

    // Server-provided flag: true when the current user should be treated as staff
    const IS_STAFF = @json($isStaff);

    class ArchiveHandler {
        constructor() {
            this.currentPage = 1;
            this.perPage = 10;
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
            // Start from the full dataset
            let data = this.quotations || [];

            // If the current user is staff, hide 'rejected' items entirely
            if (IS_STAFF) {
                data = data.filter(q => {
                    const s = q.status && q.status.status_name ? q.status.status_name.toLowerCase() : '';
                    return s !== 'rejected';
                });
            }

            // If there's no search query, return the (possibly filtered) dataset
            if (!this.searchQuery) return data;

            // Otherwise apply the text search filter
            return data.filter(q =>
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
                const statusName = q.status ? q.status.status_name : 'Unknown';
                // If the quotation/project is completed, link to the report view so users can see project reports.
                const subjectLink = (statusName && statusName.toLowerCase() === 'completed') ?
                    `<a href="/view-report/${q.id}">${q.subject}</a>` :
                    `<a href="/quotations/${q.id}">${q.subject}</a>`;
                

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

    new ArchiveHandler();
</script>
