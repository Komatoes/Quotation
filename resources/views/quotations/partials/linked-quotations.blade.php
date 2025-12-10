<!-- Linked Quotations Display -->
@if($quotation->linkedQuotations()->exists() || $quotation->parent_quotation_id)
    <div class="card mt-4 border-info">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0">
                <i class="fas fa-link"></i> Linked/Associated Quotations
            </h6>
        </div>
        <div class="card-body">
            @if($quotation->parent_quotation_id)
                <div class="alert alert-info">
                    <strong>Parent Quotation:</strong><br>
                    <a href="{{ route('quotations.show', $quotation->parentQuotation->id) }}">
                        {{ $quotation->parentQuotation->subject }}
                    </a>
                    (ID: {{ $quotation->parentQuotation->id }})
                </div>
            @endif

            @if($quotation->linkedQuotations()->exists())
                <div class="mt-3">
                    <strong>Add-On/Linked Quotations:</strong>
                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Labor Fee</th>
                                    <th>Delivery Fee</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quotation->linkedQuotations as $linked)
                                    <tr>
                                        <td>
                                            <a href="{{ route('quotations.show', $linked->id) }}">
                                                {{ $linked->subject }}
                                            </a>
                                        </td>
                                        <td>
                                            @php
                                                $ln = strtolower($linked->status->status_name ?? '');
                                                if ($linked->isRejected()) {
                                                    $icon = '<i class="fa-solid fa-circle text-danger me-2" style="font-size: 0.5rem;"></i>';
                                                } elseif ($ln === 'approved') {
                                                    $icon = '<i class="fa-solid fa-circle text-success me-2" style="font-size: 0.5rem;"></i>';
                                                } elseif ($ln === 'rejected') {
                                                    $icon = '<i class="fa-solid fa-circle text-danger me-2" style="font-size: 0.5rem;"></i>';
                                                } else {
                                                    $icon = '<i class="fa-solid fa-circle text-warning me-2" style="font-size: 0.5rem;"></i>';
                                                }
                                            @endphp
                                            <span class="fw-500">{!! $icon !!}{{ $linked->status->status_name ?? 'Pending' }}</span>
                                        </td>
                                        <td>{{ number_format($linked->labor_fee, 2) }}</td>
                                        <td>{{ number_format($linked->delivery_fee, 2) }}</td>
                                        <td>{{ $linked->created_at->setTimezone(config('app.timezone'))->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('quotations.show', $linked->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if(auth()->check() && auth()->id() === $quotation->employee_id && !$quotation->isRejected())
                <button class="btn btn-sm btn-primary mt-3" data-toggle="modal" data-target="#addLinkedQuotationModal">
                    <i class="fas fa-plus"></i> Add Add-On Quotation
                </button>
            @endif
        </div>
    </div>
@endif
