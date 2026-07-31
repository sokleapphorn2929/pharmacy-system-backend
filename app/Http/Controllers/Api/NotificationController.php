<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\ObjectId;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $rawNotifications = DB::connection('mongodb')
            ->table('notifications')
            ->where('notifiable_id', (string) $user->_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $notifications = $rawNotifications->map(function ($notification) {
            $notificationArray = (array) $notification;

            $id = $notificationArray['_id'] ?? null;
            // Convert object or BSON ID representation safely to string
            if (is_object($id) && method_exists($id, '__toString')) {
                $id = (string) $id;
            }

            return [
                'id' => (string) $id,
                'type' => $notificationArray['type'] ?? null,
                'data' => $notificationArray['data'] ?? [],
                'read_at' => $notificationArray['read_at'] ?? null,
                'created_at' => $notificationArray['created_at'] ?? null,
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
        
        // Pass the string ID directly to the query builder
        $notification = DB::connection('mongodb')
            ->table('notifications')
            ->where('_id', $id)
            ->where('notifiable_id', (string) $user->_id)
            ->first();

        if ($notification) {
            DB::connection('mongodb')
                ->table('notifications')
                ->where('_id', $id)
                ->update(['read_at' => now()]);

            return response()->json(['message' => 'Notification marked as read']);
        }

        return response()->json(['message' => 'Notification not found'], 404);
    }

    public function destroy(Request $request, string $id)
    {
        $user = $request->user();

        // Check if the notification belongs to the authenticated user using string matching
        $notification = DB::connection('mongodb')
            ->table('notifications')
            ->where(function($query) use ($id) {
                $query->where('_id', $id)
                    ->orWhere('id', $id);
            })
            ->where('notifiable_id', (string) $user->_id)
            ->first();

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        DB::connection('mongodb')
            ->table('notifications')
            ->where(function($query) use ($id) {
                $query->where('_id', $id)
                    ->orWhere('id', $id);
            })
            ->delete();

        return response()->json(['message' => 'Notification deleted successfully']);
    }
}