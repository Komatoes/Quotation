@extends('layouts.app')
@include('include.head')
@section('content')
<div class="content-wrapper">
    <div class="container-fluid flex-grow-1 container-p-y">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Backup Management</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="card mb-4">
            <div class="card-body text-center bg-light rounded shadow-sm">
                <h1 class="h3 mb-0"><i class="fa-solid fa-database me-2"></i>Backup & Restore Management</h1>
                <small class="text-muted">Admin-only backup control panel with 3-2-1 backup strategy</small>
            </div>
        </div>

        @if(isset($error))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-exclamation-circle me-2"></i>{{ $error }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- 3-2-1 Strategy Overview -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fa-solid fa-shield me-2"></i>3-2-1 Backup Strategy Status</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            <strong>3-2-1 Strategy:</strong> 3 copies of data, 2 different storage media, 1 offsite location
                        </p>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card border-left-primary mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="text-muted mb-2">Local Storage (Server)</p>
                                                <h3 class="mb-0">{{ $backupStats['local_count'] ?? 0 }}</h3>
                                                <small class="text-muted">backups</small>
                                            </div>
                                            <div class="text-primary" style="font-size: 2.5rem;">
                                                <i class="fa-solid fa-hdd"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card border-left-success mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="text-muted mb-2">Google Drive</p>
                                                <h3 class="mb-0">{{ $backupStats['google_drive_count'] ?? 0 }}</h3>
                                                <small class="text-muted">backups</small>
                                            </div>
                                            <div class="text-success" style="font-size: 2.5rem;">
                                                <i class="fa-brands fa-google-drive"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card border-left-info mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="text-muted mb-2">Amazon S3 (Optional)</p>
                                                <h3 class="mb-0">{{ $backupStats['s3_count'] ?? 0 }}</h3>
                                                <small class="text-muted">backups</small>
                                            </div>
                                            <div class="text-info" style="font-size: 2.5rem;">
                                                <i class="fa-solid fa-cloud"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            @if(($backupStats['strategy_compliant'] ?? false))
                                <div class="alert alert-success mb-0">
                                    <i class="fa-solid fa-check-circle me-2"></i>
                                    <strong>✅ 3-2-1 Strategy Compliant</strong> — You have adequate backups across multiple locations.
                                </div>
                            @else
                                <div class="alert alert-warning mb-0">
                                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                                    <strong>⚠️ Strategy Not Compliant</strong> — Add offsite backups (Google Drive or S3) to meet 3-2-1 requirements.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Storage Summary -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title mb-3"><i class="fa-solid fa-chart-pie me-2"></i>Storage Usage</h6>
                        <h4 class="mb-1">{{ $backupStats['total_size'] ?? '0 B' }}</h4>
                        <p class="text-muted mb-0">Total backup size</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title mb-3"><i class="fa-solid fa-google me-2"></i>Google Drive Status</h6>
                        @if($googleDriveConnected)
                            <span class="badge bg-success">✅ Connected</span>
                            <p class="text-muted mb-0 mt-2">Auto-upload enabled</p>
                        @else
                            <span class="badge bg-secondary">⚠️ Not Connected</span>
                            <p class="text-muted mb-0 mt-2">Setup needed</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-primary btn-lg" id="backupBtn" onclick="createBackup()">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Create Backup Now
                        </button>
                        <button type="button" class="btn btn-secondary btn-lg ms-2" id="refreshBtn" onclick="location.reload()">
                            <i class="fa-solid fa-sync me-2"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backup List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Backup Files</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Filename</th>
                            <th>Size</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="backupList">
                        @forelse($backups as $backup)
                            <tr>
                                <td>
                                    <i class="fa-solid fa-file-archive me-2 text-warning"></i>
                                    <code>{{ $backup['name'] }}</code>
                                </td>
                                <td>{{ $backup['size'] }}</td>
                                <td>{{ $backup['created_at'] }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                            onclick="restoreBackup('{{ $backup['name'] }}', '{{ $backup['size'] }}')"
                                            title="Restore from this backup">
                                        <i class="fa-solid fa-rotate-left"></i> Restore
                                    </button>
                                    <a href="{{ route('admin.backup.download', ['filename' => $backup['name']]) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Download backup">
                                        <i class="fa-solid fa-download"></i> Download
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteBackup('{{ $backup['name'] }}')">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fa-solid fa-inbox me-2"></i>No backups found. Create one now!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Backup Progress Modal -->
<div class="modal fade" id="backupModal" tabindex="-1" aria-labelledby="backupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="backupModalLabel">Creating Backup...</h5>
            </div>
            <div class="modal-body">
                <div class="progress mb-3">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 100%"></div>
                </div>
                <p class="text-muted mb-0" id="backupStatus">Preparing backup...</p>
            </div>
        </div>
    </div>
</div>

<!-- Restore Confirmation Modal -->
<div class="modal fade" id="restoreConfirmModal" tabindex="-1" aria-labelledby="restoreConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-warning">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="restoreConfirmLabel">⚠️ Restore Backup - Confirmation Required</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    <strong>⚠️ WARNING!</strong><br>
                    Restoring from a backup will:
                    <ul class="mb-0 mt-2">
                        <li>Replace your current database with the backup version</li>
                        <li>Overwrite all database records with data from <strong id="restoreFilename"></strong></li>
                        <li>Put your application in maintenance mode during the restore</li>
                        <li>Automatically create a safety backup before restoring (in case something goes wrong)</li>
                    </ul>
                </div>
                <p class="text-muted mb-0">
                    <strong>Backup Details:</strong><br>
                    File: <code id="restoreFileInfo"></code><br>
                    Size: <span id="restoreFileSize"></span>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="confirmRestore()">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i>Yes, Restore This Backup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Restore Progress Modal -->
<div class="modal fade" id="restoreProgressModal" tabindex="-1" aria-labelledby="restoreProgressLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="restoreProgressLabel">🔄 Restoring Database...</h5>
            </div>
            <div class="modal-body">
                <div class="progress mb-3">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 100%"></div>
                </div>
                <p class="text-muted mb-0" id="restoreStatus">Creating safety backup...</p>
                <p class="text-sm text-secondary mt-3 mb-0">
                    <i class="fa-solid fa-info-circle me-2"></i>
                    This may take a few minutes depending on your database size.<br>
                    Do not close this window or refresh the page.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Global variable to store current restore filename
let currentRestoreFile = null;

async function restoreBackup(filename, fileSize) {
    currentRestoreFile = filename;
    document.getElementById('restoreFilename').textContent = filename;
    document.getElementById('restoreFileInfo').textContent = filename;
    document.getElementById('restoreFileSize').textContent = fileSize;
    
    const confirmModal = new bootstrap.Modal(document.getElementById('restoreConfirmModal'));
    confirmModal.show();
}

async function confirmRestore() {
    if (!currentRestoreFile) return;

    // Close confirmation modal
    const confirmModal = bootstrap.Modal.getInstance(document.getElementById('restoreConfirmModal'));
    confirmModal.hide();

    // Show progress modal
    const progressModal = new bootstrap.Modal(document.getElementById('restoreProgressModal'));
    progressModal.show();

    try {
        const response = await fetch('{{ route("admin.backup.restore") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ filename: currentRestoreFile })
        });

        const data = await response.json();

        if (data.success) {
            document.getElementById('restoreStatus').innerHTML = '✅ <strong>Restore Completed Successfully!</strong><br>' +
                'Database restored from ' + currentRestoreFile + '<br>' +
                'Safety backup saved as: ' + (data.safety_backup || 'N/A') + '<br><br>' +
                'Application will be refreshed in 3 seconds...';
            
            setTimeout(() => {
                progressModal.hide();
                location.reload();
            }, 3000);
        } else {
            document.getElementById('restoreStatus').innerHTML = '❌ <strong>Restore Failed</strong><br>' + 
                (data.message || 'Unknown error occurred');
            
            setTimeout(() => {
                progressModal.hide();
                Swal.fire('Restore Failed', data.message || 'Failed to restore backup', 'error');
            }, 2000);
        }
    } catch (error) {
        document.getElementById('restoreStatus').innerHTML = '❌ <strong>Error</strong><br>' + error.message;
        
        setTimeout(() => {
            progressModal.hide();
            Swal.fire('Error', error.message, 'error');
        }, 2000);
    }
}

async function createBackup() {
    const modal = new bootstrap.Modal(document.getElementById('backupModal'));
    modal.show();

    try {
        const response = await fetch('{{ route("admin.backup.create") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({})
        });

        const data = await response.json();

        if (data.success) {
            document.getElementById('backupStatus').textContent = '✅ Backup completed successfully!';
            setTimeout(() => {
                modal.hide();
                location.reload();
            }, 2000);
        } else {
            document.getElementById('backupStatus').textContent = '❌ ' + (data.message || 'Backup failed');
        }
    } catch (error) {
        document.getElementById('backupStatus').textContent = '❌ Error: ' + error.message;
    }
}

async function deleteBackup(filename) {
    if (!confirm('Are you sure you want to delete this backup?\n\n' + filename)) {
        return;
    }

    try {
        const response = await fetch('{{ route("admin.backup.delete") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ filename })
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire('Deleted!', 'Backup deleted successfully.', 'success')
                .then(() => location.reload());
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    } catch (error) {
        Swal.fire('Error', error.message, 'error');
    }
}
</script>

<style>
.border-left-primary {
    border-left: 4px solid #007bff !important;
}

.border-left-success {
    border-left: 4px solid #28a745 !important;
}

.border-left-info {
    border-left: 4px solid #17a2b8 !important;
}
</style>
@endsection
