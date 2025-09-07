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
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Quantity</th>
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

{{-- Include Add Material Form --}}
@include('include.forms.material-form')

<script>
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
                        <td>${material.id}</td>
                        <td>${material.name}</td>
                        <td>${material.description || ''}</td>
                        <td>${material.quantity}</td>
                        <td>${material.unit}</td>
                        <td>${material.unit_price}</td>
                        <td>
                            <button class="btn btn-sm btn-warning">Edit</button>
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </td>
                    </tr>
                `;
                        tbody.insertAdjacentHTML("beforeend", row);
                    });
                })
                .catch(error => console.error("Error loading materials:", error));
        }
    }

    // Initialize
    const materialHandler = new MaterialHandler();
</script>
