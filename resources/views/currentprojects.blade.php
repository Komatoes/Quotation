{{-- resources/views/drafts.blade.php --}}
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Current Projects</h5>
        </div>
        <div class="card-datatable table-responsive pt-0">
            <table class="table" id="approved-table">
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
                <tbody>
                    <!-- JS will populate this -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    class ApprovedHandler {
        constructor() {
            this.loadApproved();
        }

        loadApproved() {
            fetch("{{ url('quotations/approved') }}")
                .then(res => res.json())
                .then(quotations => {
                    const table = document.getElementById("approved-table");
                    const tbody = table.getElementsByTagName("tbody")[0];
                    tbody.innerHTML = "";

                    quotations.forEach(q => {
                        const row = `
                            <tr>
                                <td>${q.subject}</td>
                                <td>${q.description}</td>
                                <td>${q.client ? (q.client.first_name + " " + q.client.last_name) : 'N/A'}</td>
                                <td>${q.employee ? q.employee.name : 'N/A'}</td>
                                <td><span class="badge bg-success">${q.status ? q.status.status_name : 'Approved'}</span></td>
                                <td>${new Date(q.created_at).toLocaleDateString()}</td>
                            </tr>
                        `;
                        tbody.insertAdjacentHTML("beforeend", row);
                    });
                })
                .catch(error => console.error("Error loading approved:", error));
        }
    }
    new ApprovedHandler();
</script>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
