<!-- Add Linked Quotation Modal -->
<div class="modal fade" id="addLinkedQuotationModal" tabindex="-1" role="dialog" aria-labelledby="addLinkedQuotationLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addLinkedQuotationLabel">Add Add-On Quotation</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addLinkedQuotationForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="linkedSubject">Subject <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            id="linkedSubject" 
                            name="subject" 
                            class="form-control" 
                            maxlength="255"
                            placeholder="Enter add-on quotation subject"
                            required
                        >
                        <div class="invalid-feedback" id="linkedSubjectError"></div>
                    </div>

                    <div class="form-group">
                        <label for="linkedDescription">Description</label>
                        <textarea 
                            id="linkedDescription" 
                            name="description" 
                            class="form-control" 
                            rows="3"
                            maxlength="5000"
                            placeholder="Enter add-on quotation description"
                        ></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="linkedLaborFee">Labor Fee</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input 
                                    type="number" 
                                    id="linkedLaborFee" 
                                    name="labor_fee" 
                                    class="form-control price-input" 
                                    step="0.01"
                                    min="0"
                                    max="999999.99"
                                    value="0"
                                    placeholder="0.00"
                                >
                            </div>
                            <div class="invalid-feedback" id="linkedLaborFeeError"></div>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="linkedDeliveryFee">Delivery Fee</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input 
                                    type="number" 
                                    id="linkedDeliveryFee" 
                                    name="delivery_fee" 
                                    class="form-control price-input" 
                                    step="0.01"
                                    min="0"
                                    max="999999.99"
                                    value="0"
                                    placeholder="0.00"
                                >
                            </div>
                            <div class="invalid-feedback" id="linkedDeliveryFeeError"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="linkedStatus">Status <span class="text-danger">*</span></label>
                        <select id="linkedStatus" name="status_id" class="form-control" required>
                            <option value="">-- Select Status --</option>
                            @foreach($statuses ?? [] as $status)
                                <option value="{{ $status->id }}">{{ $status->status_name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="linkedStatusError"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitLinkedBtn">Add Add-On</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const parentQuotationId = "{{ $quotation->id ?? '' }}";

    // Handle form submission
    document.getElementById('addLinkedQuotationForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = {
            subject: document.getElementById('linkedSubject').value.trim(),
            description: document.getElementById('linkedDescription').value.trim(),
            labor_fee: document.getElementById('linkedLaborFee').value,
            delivery_fee: document.getElementById('linkedDeliveryFee').value,
            status_id: document.getElementById('linkedStatus').value,
            quotation_type: 'addon'
        };

        // Client-side validation
        let hasError = false;

        if (!formData.subject) {
            document.getElementById('linkedSubjectError').textContent = 'Subject is required';
            hasError = true;
        }

        if (!formData.status_id) {
            document.getElementById('linkedStatusError').textContent = 'Status is required';
            hasError = true;
        }

        if (formData.labor_fee < 0) {
            document.getElementById('linkedLaborFeeError').textContent = 'Labor fee cannot be negative';
            hasError = true;
        }

        if (formData.delivery_fee < 0) {
            document.getElementById('linkedDeliveryFeeError').textContent = 'Delivery fee cannot be negative';
            hasError = true;
        }

        if (hasError) return;

        // Send request
        const submitBtn = document.getElementById('submitLinkedBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';

        fetch(`/api/quotations/${parentQuotationId}/linked`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Authorization': `Bearer ${localStorage.getItem('auth_token') || ''}`
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Add-on quotation created successfully');
                $('#addLinkedQuotationModal').modal('hide');
                location.reload();
            } else {
                alert(data.error || 'Failed to create add-on quotation');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Add Add-On';
        });
    });
});
</script>
@endpush
