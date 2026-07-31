<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Directly query the notifications collection matching the user's ID string
        $rawNotifications = DB::connection('mongodb')
            ->collection('notifications')
            ->where('notifiable_id', (string) $user->_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $notifications = $rawNotifications->map(function ($notification) {
            // Handle MongoDB collection results (which may return _id as an ObjectId or string)
            $id = is_array($notification) ? ($notification['_id'] ?? null) : ($notification->_id ?? null);
            
            return [
                'id' => (string) $id,
                'type' => $notification['type'] ?? $notification->type ?? null,
                'data' => $notification['data'] ?? $notification->data ?? [],
                'read_at' => $notification['read_at'] ?? $notification->read_at ?? null,
                'created_at' => $notification['created_at'] ?? $notification->created_at ?? null,
            ];
        });

        return response()->json([
            'message' => 'Notifications retrieved successfully',
            'data' => $notifications
        ]);
    }

    public function markAsRead(Request $request, string $id)
    {
        $user = $request->user();
        
        $notification = DB::connection('mongodb')
            ->collection('notifications')
            ->where('_id', $id)
            ->where('notifiable_id', (string) $user->_id)
            ->first();

        if ($notification) {
            DB::connection('mongodb')
                ->collection('notifications')
                ->where('_id', $id)
                ->update(['read_at' => now()]);

            return response()->json(['message' => 'Notification marked as read']);
        }

        return response()->json(['message' => 'Notification not found'], 404);
    }
}