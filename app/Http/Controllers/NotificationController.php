<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // Get unread notifications count
    public function getUnreadCount()
    {
        $count = Notification::where('user_id', Auth::id())->unread()->count();
        return response()->json(['count' => $count]);
    }

    // Get notifications (paginated, most recent first)
    public function getNotifications(Request $request)
    {
        $limit = $request->get('limit', 10);
        
        $notifications = Notification::where('user_id', Auth::id())
            ->recent()
            ->paginate($limit);

        return response()->json($notifications);
    }

    // Get unread notifications only
    public function getUnreadNotifications(Request $request)
    {
        $limit = $request->get('limit', 5);
        
        $notifications = Notification::where('user_id', Auth::id())
            ->unread()
            ->recent()
            ->limit($limit)
            ->get();

        return response()->json($notifications);
    }

    // Mark notification as read
    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true, 'message' => 'Notification marked as read']);
    }

    // Mark all notifications as read
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->unread()
            ->update([
                'read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true, 'message' => 'All notifications marked as read']);
    }

    // Delete notification
    public function delete($id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
        $notification->delete();

        return response()->json(['success' => true, 'message' => 'Notification deleted']);
    }

    // Clear all notifications
    public function clearAll()
    {
        Notification::where('user_id', Auth::id())->delete();

        return response()->json(['success' => true, 'message' => 'All notifications cleared']);
    }
}
