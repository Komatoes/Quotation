<!-- Notifications Bell Icon in Navbar -->
<style>
    .notifications-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background-color: #dc3545;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .notifications-dropdown {
        position: absolute;
        top: 50px;
        right: 0;
        width: 380px;
        background: white;
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1050;
        max-height: 600px;
        overflow-y: auto;
    }

    .notification-item {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f3f5;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .notification-item:hover {
        background-color: #f8f9fa;
    }

    .notification-item.unread {
        background-color: #f0f4ff;
    }

    .notification-title {
        font-weight: 600;
        color: #212529;
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
    }

    .notification-message {
        color: #6c757d;
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
    }

    .notification-time {
        font-size: 0.75rem;
        color: #adb5bd;
    }

    .notifications-empty {
        padding: 40px 20px;
        text-align: center;
        color: #6c757d;
    }

    .notifications-header {
        padding: 16px;
        border-bottom: 1px solid #e3e6f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .notifications-header h6 {
        margin: 0;
        color: #212529;
        font-weight: 600;
    }

    .notifications-footer {
        padding: 12px 16px;
        text-align: center;
        border-top: 1px solid #e3e6f0;
    }

    .notifications-footer a {
        color: #667eea;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
    }
</style>

<div style="position: relative; display: inline-block;">
    <!-- Bell Icon -->


    <!-- Notifications Dropdown -->
    <div id="notificationsDropdown" class="notifications-dropdown" style="display: none;">
        <!-- Header -->
        <div class="notifications-header">
            <h6>Notifications</h6>
            <div style="display: flex; gap: 0.5rem;">
                <button onclick="markAllAsRead()" title="Mark all as read" style="background: none; border: none; cursor: pointer; color: #667eea; font-size: 1rem;">
                    <i class="fa-solid fa-check-double"></i>
                </button>
                <button onclick="clearAllNotifications()" title="Clear all" style="background: none; border: none; cursor: pointer; color: #667eea; font-size: 1rem;">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>

        <!-- Notifications List -->
        <div id="notificationsList" style="max-height: 500px; overflow-y: auto;">
            <!-- Loaded via JavaScript -->
        </div>

        <!-- Footer -->
        <div class="notifications-footer">
        </div>
    </div>
</div>

<script>
    // Load notifications on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadNotificationCount();
        loadUnreadNotifications();
        // Refresh every 30 seconds
        setInterval(loadNotificationCount, 30000);
    });

    function toggleNotificationsDropdown() {
        const dropdown = document.getElementById('notificationsDropdown');
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
        if (dropdown.style.display === 'block') {
            loadUnreadNotifications();
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('notificationsDropdown');
        const bell = document.getElementById('notificationBell');
        if (!dropdown.contains(event.target) && !bell.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });

    function loadNotificationCount() {
        fetch('{{ route("notifications.count") }}')
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('notificationBadge');
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            });
    }

    function loadUnreadNotifications() {
        fetch('{{ route("notifications.unread") }}?limit=5')
            .then(response => response.json())
            .then(data => {
                const list = document.getElementById('notificationsList');
                if (data.length === 0) {
                    list.innerHTML = '<div class="notifications-empty"><i class="fa-solid fa-inbox" style="font-size: 2rem; margin-bottom: 1rem; color: #adb5bd;"></i><p>No notifications</p></div>';
                    return;
                }

                list.innerHTML = data.map(notification => `
                    <div class="notification-item ${notification.read ? '' : 'unread'}" onclick="handleNotificationClick(${notification.id}, '${notification.related_model}', ${notification.related_id})">
                        <div style="display: flex; gap: 0.75rem;">
                            <div style="flex-shrink: 0;">
                                <i class="fa-solid ${getNotificationIcon(notification.type)}" style="color: ${getNotificationColor(notification.type)}; font-size: 1.2rem;"></i>
                            </div>
                            <div style="flex-grow: 1;">
                                <div class="notification-title">${notification.title}</div>
                                <div class="notification-message">${notification.message}</div>
                                <div class="notification-time">${formatTime(notification.created_at)}</div>
                            </div>
                            <button onclick="deleteNotification(event, ${notification.id})" style="background: none; border: none; cursor: pointer; color: #dc3545; font-size: 0.9rem;">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
            });
    }

    function getNotificationIcon(type) {
        const icons = {
            'comment': 'fa-comment',
            'approval': 'fa-check-circle',
            'rejection': 'fa-times-circle',
            'project_update': 'fa-sync-alt',
            'new_quotation': 'fa-file-alt',
            'status_change': 'fa-exchange-alt',
        };
        return icons[type] || 'fa-bell';
    }

    function getNotificationColor(type) {
        const colors = {
            'comment': '#0d6efd',
            'approval': '#198754',
            'rejection': '#dc3545',
            'project_update': '#ffc107',
            'new_quotation': '#667eea',
            'status_change': '#6c757d',
        };
        return colors[type] || '#adb5bd';
    }

    function formatTime(timestamp) {
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

    function handleNotificationClick(notificationId, model, relatedId) {
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

    function markAllAsRead() {
        fetch('{{ route("notifications.mark-all-read") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        }).then(() => {
            loadNotificationCount();
            loadUnreadNotifications();
        });
    }

    function deleteNotification(event, notificationId) {
        event.stopPropagation();
        fetch(`/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        }).then(() => {
            loadNotificationCount();
            loadUnreadNotifications();
        });
    }

    function clearAllNotifications() {
        if (confirm('Are you sure you want to delete all notifications?')) {
            fetch('{{ route("notifications.clear-all") }}', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            }).then(() => {
                loadNotificationCount();
                loadUnreadNotifications();
            });
        }
    }
</script>
