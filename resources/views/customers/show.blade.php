@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Customer Details Card -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Customer Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ $client->first_name }} {{ $client->last_name }}</p>
                            <p><strong>Email:</strong> {{ $client->email }}</p>
                            <p><strong>Phone:</strong> {{ $client->phone }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Company:</strong> {{ $client->company }}</p>
                            <p><strong>Created:</strong> {{ $client->created_at->format('M d, Y') }}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-{{ $client->status === 'active' ? 'success' : 'warning' }}">
                                    {{ ucfirst($client->status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quotations Tab -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Quotations</h6>
                    @can('create quotations')
                    <a href="{{ route('quotations.create', ['client_id' => $client->id]) }}" 
                       class="btn btn-primary btn-sm float-end">
                        <i class="fas fa-plus"></i> New Quotation
                    </a>
                    @endcan
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quotations as $quotation)
                                <tr>
                                    <td>{{ $quotation->id }}</td>
                                    <td>{{ $quotation->subject }}</td>
                                    <td>{{ $quotation->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $quotation->status->name === 'approved' ? 'success' : 'info' }}">
                                            {{ ucfirst($quotation->status->name) }}
                                        </span>
                                    </td>
                                    <td>₱{{ number_format($quotation->total_amount, 2) }}</td>
                                    <td>
                                        <a href="{{ route('quotations.show', $quotation) }}" 
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Interactions Tab -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Interactions</h6>
                    @can('record interactions')
                    <button type="button" 
                            class="btn btn-primary btn-sm float-end" 
                            data-bs-toggle="modal" 
                            data-bs-target="#addInteractionModal">
                        <i class="fas fa-plus"></i> Record Interaction
                    </button>
                    @endcan
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Notes</th>
                                    <th>Status</th>
                                    <th>Recorded By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($interactions as $interaction)
                                <tr>
                                    <td>{{ $interaction->interaction_date->format('M d, Y H:i') }}</td>
                                    <td>{{ ucfirst($interaction->type) }}</td>
                                    <td>{{ Str::limit($interaction->notes, 50) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $interaction->status === 'completed' ? 'success' : 'warning' }}">
                                            {{ ucfirst($interaction->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $interaction->user->name }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Service History Tab -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Service History</h6>
                    @can('manage service history')
                    <button type="button" 
                            class="btn btn-primary btn-sm float-end" 
                            data-bs-toggle="modal" 
                            data-bs-target="#addServiceModal">
                        <i class="fas fa-plus"></i> Add Service Record
                    </button>
                    @endcan
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Service Type</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Related Quotation</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($serviceHistory as $service)
                                <tr>
                                    <td>{{ $service->service_date->format('M d, Y') }}</td>
                                    <td>{{ ucfirst($service->service_type) }}</td>
                                    <td>{{ Str::limit($service->description, 50) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $service->status === 'completed' ? 'success' : 'info' }}">
                                            {{ ucfirst($service->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($service->quotation)
                                            <a href="{{ route('quotations.show', $service->quotation) }}">
                                                #{{ $service->quotation->id }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Interaction Modal -->
@can('record interactions')
<div class="modal fade" id="addInteractionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record New Interaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('customers.interactions.store', $client) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" name="type" required>
                            <option value="call">Call</option>
                            <option value="email">Email</option>
                            <option value="meeting">Meeting</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="interaction_date" class="form-label">Date & Time</label>
                        <input type="datetime-local" class="form-control" name="interaction_date" 
                               value="{{ now()->format('Y-m-d\TH:i') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="scheduled">Scheduled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Interaction</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

<!-- Add Service Modal -->
@can('manage service history')
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Service Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('customers.services.store', $client) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="service_type" class="form-label">Service Type</label>
                        <select class="form-select" name="service_type" required>
                            <option value="installation">Installation</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="repair">Repair</option>
                            <option value="consultation">Consultation</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="service_date" class="form-label">Service Date</label>
                        <input type="date" class="form-control" name="service_date" 
                               value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="scheduled">Scheduled</option>
                            <option value="in-progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quotation_id" class="form-label">Related Quotation (Optional)</label>
                        <select class="form-select" name="quotation_id">
                            <option value="">None</option>
                            @foreach($quotations as $quotation)
                            <option value="{{ $quotation->id }}">#{{ $quotation->id }} - {{ $quotation->subject }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Record</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection