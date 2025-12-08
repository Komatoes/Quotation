@extends('layouts.app')
@include('include.head')
@section('content')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-fluid flex-grow-1 container-p-y">
            <!-- Alert/Notification Cards -->
            <div class="row g-6 mb-4">
                <!-- Unread Notifications Card -->
                <div class="col-lg-6 col-md-6">
                    <div class="card" style="cursor: pointer; border: 1px solid #e3e6f0; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); transition: all 0.3s ease; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);" 
                         onclick="openNotificationsModal()"
                         onmouseover="this.style.boxShadow='0 1rem 3rem rgba(0, 0, 0, 0.175)'; this.style.transform='translateY(-2px)';"
                         onmouseout="this.style.boxShadow='0 0.125rem 0.25rem rgba(0, 0, 0, 0.075)'; this.style.transform='translateY(0)';">
                        <div class="card-body">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <p style="margin: 0 0 0.5rem 0; color: rgba(255,255,255,0.8); font-size: 0.875rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Unread Messages</p>
                                    <h3 style="margin: 0; color: white; font-weight: 600;">{{ $unreadNotifications }}</h3>
                                    <small style="color: rgba(255,255,255,0.7); margin-top: 0.5rem; display: block;">Click to view</small>
                                </div>
                                <i class="fa-solid fa-bell" style="font-size: 2.5rem; color: white; opacity: 0.7;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Approvals Card -->
                <div class="col-lg-6 col-md-6">
                    <div class="card" style="cursor: pointer; border: 1px solid #e3e6f0; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); transition: all 0.3s ease; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);" 
                         onclick="window.location.href='{{ route('quotation.reports') }}'"
                         onmouseover="this.style.boxShadow='0 1rem 3rem rgba(0, 0, 0, 0.175)'; this.style.transform='translateY(-2px)';"
                         onmouseout="this.style.boxShadow='0 0.125rem 0.25rem rgba(0, 0, 0, 0.075)'; this.style.transform='translateY(0)';">
                        <div class="card-body">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <p style="margin: 0 0 0.5rem 0; color: rgba(255,255,255,0.8); font-size: 0.875rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Customer Approvals</p>
                                    <h3 style="margin: 0; color: white; font-weight: 600;">{{ $customerApprovedQuotations }}</h3>
                                    <small style="color: rgba(255,255,255,0.7); margin-top: 0.5rem; display: block;">Approved by customers</small>
                                </div>
                                <i class="fa-solid fa-thumbs-up" style="font-size: 2.5rem; color: white; opacity: 0.7;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Dashboard -->
            <div class="row g-6 mb-4">
                <div class="col-12">
                    <div class="card" style="cursor: pointer; border: 1px solid #e3e6f0; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); transition: all 0.3s ease;" 
                         onclick="window.location.href='{{ route('quotation.reports') }}'" 
                         onmouseover="this.style.boxShadow='0 1rem 3rem rgba(0, 0, 0, 0.175)'; this.style.borderColor='#0d6efd';"
                         onmouseout="this.style.boxShadow='0 0.125rem 0.25rem rgba(0, 0, 0, 0.075)'; this.style.borderColor='#e3e6f0';">
                        <div class="card-header border-bottom bg-white">
                            <h5 class="mb-0 text-dark">
                                <i class="fa-solid fa-chart-column"></i> Quotation Statistics
                            </h5>
                            <small class="text-muted">Click to view detailed monthly and yearly reports</small>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Draft Stats Card -->
                                <div class="col-lg-3 col-md-6 mb-4">
                                    <div style="padding: 1.5rem; border-radius: 0.5rem; background-color: #f8f9fa; border-left: 4px solid #6c757d;">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <div>
                                                <p style="margin: 0 0 0.5rem 0; color: #6c757d; font-size: 0.875rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Draft</p>
                                                <h3 style="margin: 0; color: #212529; font-weight: 600;">{{ $draftProjects }}</h3>
                                            </div>
                                            <i class="fa-solid fa-file" style="font-size: 2rem; color: #6c757d; opacity: 0.5;"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Approved Stats Card -->
                                <div class="col-lg-3 col-md-6 mb-4">
                                    <div style="padding: 1.5rem; border-radius: 0.5rem; background-color: #f8f9fa; border-left: 4px solid #198754;">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <div>
                                                <p style="margin: 0 0 0.5rem 0; color: #198754; font-size: 0.875rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Approved</p>
                                                <h3 style="margin: 0; color: #212529; font-weight: 600;">{{ $approvedProjects }}</h3>
                                            </div>
                                            <i class="fa-solid fa-check" style="font-size: 2rem; color: #198754; opacity: 0.5;"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Rejected Stats Card -->
                                <div class="col-lg-3 col-md-6 mb-4">
                                    <div style="padding: 1.5rem; border-radius: 0.5rem; background-color: #f8f9fa; border-left: 4px solid #dc3545;">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <div>
                                                <p style="margin: 0 0 0.5rem 0; color: #dc3545; font-size: 0.875rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Rejected</p>
                                                <h3 style="margin: 0; color: #212529; font-weight: 600;">{{ $rejectedProjects }}</h3>
                                            </div>
                                            <i class="fa-solid fa-times" style="font-size: 2rem; color: #dc3545; opacity: 0.5;"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Ongoing Stats Card -->
                                <div class="col-lg-3 col-md-6 mb-4">
                                    <div style="padding: 1.5rem; border-radius: 0.5rem; background-color: #f8f9fa; border-left: 4px solid #0dcaf0;">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <div>
                                                <p style="margin: 0 0 0.5rem 0; color: #0dcaf0; font-size: 0.875rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Ongoing</p>
                                                <h3 style="margin: 0; color: #212529; font-weight: 600;">{{ $ongoingProjects }}</h3>
                                            </div>
                                            <i class="fa-solid fa-circle-notch" style="font-size: 2rem; color: #0dcaf0; opacity: 0.5;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Original Cards (Legacy) -->
            <div class="row g-6 d-none">
                <div class="col-xxl-4 col-md-3 col-7">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="badge p-2 bg-label-success mb-3 rounded">
                                <i class="fa-solid fa-folder-open fa-2x"></i>
                            </div>
                            <h5 class="card-title mb-1">Total Projects</h5>
                            <p class="text-heading mb-3 mt-1">{{ $totalProjects }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-4 col-md-3 col-7">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="badge p-2 bg-label-success mb-3 rounded">
                                <i class="fa-solid fa-spinner fa-2x"></i>
                            </div>
                            <h5 class="card-title mb-1">Current Projects</h5>
                            <p class="text-heading mb-3 mt-1">{{ $currentProjects }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-md-3 col-7">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="badge p-2 bg-label-success mb-3 rounded">
                                <i class="fa-solid fa-check-circle fa-2x"></i>
                            </div>
                            <h5 class="card-title mb-1">Finished Projects</h5>
                            <p class="text-heading mb-3 mt-1">{{ $finishedProjects }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <section id="material-list" class="mt-5">
                <div class="row">
                    @if (Auth::user()->can('view_materials'))
                        @include('materials')
                    @else
                        <div class="col-12">
                            <div class="card border-warning">
                                <div class="card-body text-center py-5">
                                    <i class="fa-solid fa-lock text-warning" style="font-size: 2rem;"></i>
                                    <h5 class="text-warning mt-3 mb-2">Materials Management</h5>
                                    <p class="text-muted mb-0">This section is restricted to owner only.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            <section id="current-projects" class="mt-5">
                <div class="row">
                    @include('currentprojects')
                </div>
            </section>

            <section id="draft-quotations" class="mt-5">
                <div class="row">
                    @include('draftprojects')
                </div>
            </section>
            <section id="archived-projects" class="mt-5">
                <div class="row">
                    @include('archivedprojects')
                </div>

            </section>

        </div>
    </div>

    <!-- Notifications Modal -->
    <div id="notificationsModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="notificationsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom: 1px solid #e3e6f0;">
                    <h5 class="modal-title" id="notificationsModalLabel">
                        <i class="fa-solid fa-bell" style="color: #667eea; margin-right: 0.5rem;"></i> Your Notifications
                    </h5>
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="button" class="btn btn-sm btn-link" onclick="markAllAsReadModal()" title="Mark all as read" style="text-decoration: none; color: #667eea;">
                            <i class="fa-solid fa-check-double"></i> Mark All Read
                        </button>
                        <button type="button" class="btn btn-sm btn-link" onclick="clearAllNotificationsModal()" title="Clear all" style="text-decoration: none; color: #dc3545;">
                            <i class="fa-solid fa-trash"></i> Clear All
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
                    <div id="notificationsListModal" style="min-height: 200px;">
                        <!-- Notifications loaded here via JavaScript -->
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e3e6f0;">
                    <a href="{{ route('notifications.list') }}" class="btn btn-link" style="text-decoration: none;">View All Notifications</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Open notifications modal
        function openNotificationsModal() {
            loadNotificationsIntoModal();
            const modal = new bootstrap.Modal(document.getElementById('notificationsModal'));
            modal.show();
        }

        // Load notifications into modal
        function loadNotificationsIntoModal() {
            fetch('{{ route("notifications.unread") }}?limit=10')
                .then(response => response.json())
                .then(data => {
                    const list = document.getElementById('notificationsListModal');
                    if (data.length === 0) {
                        list.innerHTML = `
                            <div style="text-align: center; padding: 3rem 1rem; color: #6c757d;">
                                <i class="fa-solid fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; color: #adb5bd; display: block;"></i>
                                <p style="font-size: 1.1rem; margin: 0;">No notifications yet</p>
                            </div>
                        `;
                        return;
                    }

                    list.innerHTML = data.map(notification => `
                        <div class="notification-item-modal ${notification.read ? '' : 'unread'}" style="padding: 1rem; border-bottom: 1px solid #f1f3f5; cursor: pointer; transition: background-color 0.2s ease;" 
                             onclick="handleNotificationClickModal(${notification.id}, '${notification.related_model}', ${notification.related_id})"
                             onmouseover="this.style.backgroundColor='#f8f9fa';"
                             onmouseout="this.style.backgroundColor='${notification.read ? 'white' : '#f0f4ff'}';"
                             style="background-color: ${notification.read ? 'white' : '#f0f4ff'};">
                            <div style="display: flex; gap: 0.75rem;">
                                <div style="flex-shrink: 0;">
                                    <i class="fa-solid ${getNotificationIconModal(notification.type)}" style="color: ${getNotificationColorModal(notification.type)}; font-size: 1.2rem;"></i>
                                </div>
                                <div style="flex-grow: 1;">
                                    <div style="font-weight: 600; color: #212529; margin-bottom: 0.25rem; font-size: 0.9rem;">${notification.title}</div>
                                    <div style="color: #6c757d; font-size: 0.85rem; margin-bottom: 0.5rem;">${notification.message}</div>
                                    <div style="font-size: 0.75rem; color: #adb5bd;">${formatTimeModal(notification.created_at)}</div>
                                </div>
                                <button onclick="deleteNotificationModal(event, ${notification.id})" style="background: none; border: none; cursor: pointer; color: #dc3545; font-size: 0.9rem; flex-shrink: 0;">
                                    <i class="fa-solid fa-times"></i>
                                </button>
                            </div>
                        </div>
                    `).join('');
                });
        }

        function getNotificationIconModal(type) {
            const icons = {
                'comment': 'fa-comment',
                'approval': 'fa-check-circle',
                'rejection': 'fa-times-circle',
                'project_update': 'fa-sync-alt',
                'new_quotation': 'fa-file-alt',
                'status_change': 'fa-exchange-alt',
                'customer_approval': 'fa-thumbs-up',
            };
            return icons[type] || 'fa-bell';
        }

        function getNotificationColorModal(type) {
            const colors = {
                'comment': '#0d6efd',
                'approval': '#198754',
                'rejection': '#dc3545',
                'project_update': '#ffc107',
                'new_quotation': '#667eea',
                'status_change': '#6c757d',
                'customer_approval': '#f5576c',
            };
            return colors[type] || '#adb5bd';
        }

        function formatTimeModal(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diff = now - date;
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);

            if (minutes < 1) return 'Just now';
            if (minutes < 60) return `${minutes}m ago`;
            if (hours < 24) return `${hours}h ago`;
            if (days < 7) return `${days}d ago`;
            return date.toLocaleDateString();
        }

        function handleNotificationClickModal(notificationId, model, relatedId) {
            // Mark as read
            fetch(`/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            }).then(() => {
                // Redirect based on model type
                if (model === 'Quotation') {
                    window.location.href = `/quotations/${relatedId}`;
                } else if (model === 'Project') {
                    window.location.href = `/projects/${relatedId}`;
                }
            });
        }

        function markAllAsReadModal() {
            fetch('{{ route("notifications.mark-all-read") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            }).then(() => {
                loadNotificationsIntoModal();
            });
        }

        function deleteNotificationModal(event, notificationId) {
            event.stopPropagation();
            fetch(`/notifications/${notificationId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            }).then(() => {
                loadNotificationsIntoModal();
            });
        }

        function clearAllNotificationsModal() {
            if (confirm('Are you sure you want to delete all notifications?')) {
                fetch('{{ route("notifications.clear-all") }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                }).then(() => {
                    loadNotificationsIntoModal();
                });
            }
        }
    </script>
@endsection
