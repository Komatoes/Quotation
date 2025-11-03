@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Customers</h6>
                    @can('manage customers')
                    <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm float-end">
                        <i class="fas fa-plus"></i> Add Customer
                    </a>
                    @endcan
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Quotations</th>
                                    <th>Last Interaction</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customers as $customer)
                                <tr>
                                    <td>
                                        <h6 class="mb-0">{{ $customer->first_name }} {{ $customer->last_name }}</h6>
                                    </td>
                                    <td>{{ $customer->email }}</td>
                                    <td>{{ $customer->phone }}</td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ $customer->quotations_count ?? $customer->quotations->count() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($customer->interactions->isNotEmpty())
                                            {{ $customer->interactions->sortByDesc('interaction_date')->first()->interaction_date->diffForHumans() }}
                                        @else
                                            No interactions
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('customers.show', $customer) }}" 
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('manage customers')
                                        <a href="{{ route('customers.edit', $customer) }}" 
                                           class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete customers')
                                        <form action="{{ route('customers.destroy', $customer) }}" 
                                              method="POST" 
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-3 mt-3">
                        {{ $customers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection