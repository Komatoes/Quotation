@extends('layouts.app')
@include('include.head')
@section('content')
    <div class="content-wrapper">
        <div class="container-fluid flex-grow-1 container-p-y">
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">
                                <i class="fa-solid fa-file-lines" style="color: #667eea;"></i> System Logs
                            </h3>
                            <small class="text-muted">View and monitor all system activities</small>
                        </div>
                        <div class="btn-group" role="group">
                            <a href="{{ route('admin.logs.export', request()->query()) }}" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-download"></i> Export CSV
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmClearLogs()">
                                <i class="fa-solid fa-trash"></i> Clear Old Logs
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card" style="border: 1px solid #e3e6f0;">
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.logs.index') }}" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Search</label>
                                    <input type="text" name="search" class="form-control form-control-sm" 
                                           placeholder="Search in description..." 
                                           value="{{ $filters['search'] ?? '' }}">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Action</label>
                                    <select name="action" class="form-select form-select-sm">
                                        <option value="">All Actions</option>
                                        @foreach($actions as $action)
                                            <option value="{{ $action }}" 
                                                    {{ $filters['action'] ?? '' === $action ? 'selected' : '' }}>
                                                {{ ucfirst($action) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Model</label>
                                    <select name="model" class="form-select form-select-sm">
                                        <option value="">All Models</option>
                                        @foreach($models as $model)
                                            <option value="{{ $model }}" 
                                                    {{ $filters['model'] ?? '' === $model ? 'selected' : '' }}>
                                                {{ class_basename($model) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" name="start_date" class="form-control form-control-sm"
                                           value="{{ $filters['start_date'] ?? '' }}">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">End Date</label>
                                    <input type="date" name="end_date" class="form-control form-control-sm"
                                           value="{{ $filters['end_date'] ?? '' }}">
                                </div>

                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="submit" class="btn btn-sm btn-primary w-100">
                                        <i class="fa-solid fa-search"></i> Filter
                                    </button>
                                </div>

                                @if(array_filter($filters))
                                    <div class="col-12">
                                        <a href="{{ route('admin.logs.index') }}" class="btn btn-sm btn-link">
                                            <i class="fa-solid fa-times"></i> Clear Filters
                                        </a>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card" style="border: 1px solid #e3e6f0;">
                        <div class="card-body p-0">
                            @if($logs->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead style="background-color: #f8f9fa; border-bottom: 2px solid #e3e6f0;">
                                            <tr>
                                                <th style="padding: 1rem;" width="50">ID</th>
                                                <th style="padding: 1rem;">User</th>
                                                <th style="padding: 1rem;">Action</th>
                                                <th style="padding: 1rem;">Description</th>
                                                <th style="padding: 1rem;">Model</th>
                                                <th style="padding: 1rem;">IP Address</th>
                                                <th style="padding: 1rem;">Date & Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($logs as $log)
                                                <tr style="border-bottom: 1px solid #f1f3f5;">
                                                    <td style="padding: 1rem;">
                                                        <span class="badge bg-light text-dark">{{ $log->id }}</span>
                                                    </td>
                                                    <td style="padding: 1rem;">
                                                        @if($log->user)
                                                            <small class="d-block">{{ $log->user->name }}</small>
                                                            <small class="text-muted">{{ $log->user->email }}</small>
                                                        @else
                                                            <small class="text-muted">System</small>
                                                        @endif
                                                    </td>
                                                    <td style="padding: 1rem;">
                                                        <span class="badge" style="background-color: {{ $log->getActionBadgeColor() }};">
                                                            <i class="fa-solid {{ $log->getActionIcon() }}"></i>
                                                            {{ ucfirst($log->action) }}
                                                        </span>
                                                    </td>
                                                    <td style="padding: 1rem;">
                                                        <small>{{ Str::limit($log->description, 50) }}</small>
                                                    </td>
                                                    <td style="padding: 1rem;">
                                                        @if($log->model)
                                                            <small class="d-block">{{ class_basename($log->model) }}</small>
                                                            <small class="text-muted">#{{ $log->model_id }}</small>
                                                        @else
                                                            <small class="text-muted">-</small>
                                                        @endif
                                                    </td>
                                                    <td style="padding: 1rem;">
                                                        <small class="d-block">{{ $log->ip_address ?? 'N/A' }}</small>
                                                    </td>
                                                    <td style="padding: 1rem;">
                                                        <small class="d-block">{{ $log->created_at->format('Y-m-d H:i') }}</small>
                                                        <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div style="padding: 3rem; text-align: center; color: #6c757d;">
                                    <i class="fa-solid fa-inbox" style="font-size: 2rem; margin-bottom: 1rem; display: block; color: #adb5bd;"></i>
                                    <p class="mb-0">No system logs found</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if($logs->hasPages())
                        <div class="row mt-4">
                            <div class="col-12">
                                <nav aria-label="Page navigation">
                                    {{ $logs->links() }}
                                </nav>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmClearLogs() {
            if (!confirm('Are you sure you want to clear logs older than 90 days? This action cannot be undone.')) {
                return;
            }

            // Create a form and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.logs.clear") }}';
            
            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = '{{ csrf_token() }}';
            
            form.appendChild(token);
            document.body.appendChild(form);
            form.submit();
        }
    </script>
@endsection
