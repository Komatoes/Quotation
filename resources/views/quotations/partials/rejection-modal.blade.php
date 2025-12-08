<!-- Rejection Modal -->
<div class="modal fade" id="rejectQuotationModal" tabindex="-1" role="dialog" aria-labelledby="rejectQuotationLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectQuotationLabel">Reject Quotation</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="rejectQuotationForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejectionReason">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea 
                            id="rejectionReason" 
                            name="rejection_reason" 
                            class="form-control" 
                            rows="4"
                            minlength="10"
                            maxlength="1000"
                            placeholder="Please provide a detailed reason for rejection (minimum 10 characters)"
                            required
                        ></textarea>
                        <small class="form-text text-muted">Minimum 10 characters, maximum 1000 characters</small>
                        <div class="invalid-feedback d-block" id="rejectionReasonError"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="submitRejectBtn">Reject Quotation</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentQuotationId = null;

    // Handle reject button clicks
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-reject-quotation')) {
            currentQuotationId = e.target.dataset.quotationId;
            document.getElementById('rejectQuotationForm').reset();
            $('#rejectQuotationModal').modal('show');
        }
    });

    // Handle form submission
    document.getElementById('rejectQuotationForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const rejectionReason = document.getElementById('rejectionReason').value.trim();

        // Client-side validation
        if (rejectionReason.length < 10) {
            document.getElementById('rejectionReasonError').textContent = 'Reason must be at least 10 characters long';
            return;
        }

        if (rejectionReason.length > 1000) {
            document.getElementById('rejectionReasonError').textContent = 'Reason must not exceed 1000 characters';
            return;
        }

        // Send rejection request
        const submitBtn = document.getElementById('submitRejectBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Rejecting...';

        fetch(`/api/quotations/${currentQuotationId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Authorization': `Bearer ${localStorage.getItem('auth_token') || ''}`
            },
            body: JSON.stringify({
                rejection_reason: rejectionReason
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Quotation rejected successfully');
                $('#rejectQuotationModal').modal('hide');
                location.reload();
            } else {
                document.getElementById('rejectionReasonError').textContent = data.error || 'Failed to reject quotation';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('rejectionReasonError').textContent = 'An error occurred. Please try again.';
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Reject Quotation';
        });
    });
});
</script>
@endpush
