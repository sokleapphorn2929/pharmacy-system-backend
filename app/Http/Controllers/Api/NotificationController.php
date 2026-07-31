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

            // Check both '_id' and 'id' keys
            $rawId = $notificationArray['_id'] ?? $notificationArray['id'] ?? null;

            $id = "";
            if ($rawId) {
                if (is_string($rawId)) {
                    $id = $rawId;
                } elseif (is_object($rawId)) {
                    if (method_exists($rawId, '__toString')) {
                        $id = (string) $rawId;
                    } elseif (isset($rawId->{'$oid'})) {
                        $id = (string) $rawId->{'$oid'};
                    } else {
                        $id = (string) $rawId;
                    }
                } elseif (is_array($rawId) && isset($rawId['$oid'])) {
                    $id = (string) $rawId['$oid'];
                }
            }

            return [
                'id' => $id,
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
        $userId = (string) ($user->_id ?? $user->id);
        $notificationId = (string) $id;

        // Find the notification matching either _id or id as strings
        $notification = DB::connection('mongodb')
            ->table('notifications')
            ->where(function($query) use ($notificationId) {
                $query->where('_id', $notificationId)
                    ->orWhere('id', $notificationId);
            })
            ->where('notifiable_id', $userId)
            ->first();

        if (!$notification) {
            return response()->json([
                'message' => 'Notification not found',
                'debug_id' => $notificationId,
                'debug_user' => $userId
            ], 404);
        }

        DB::connection('mongodb')
            ->table('notifications')
            ->where(function($query) use ($notificationId) {
                $query->where('_id', $notificationId)
                    ->orWhere('id', $notificationId);
            })
            ->delete();

        return response()->json(['message' => 'Notification deleted successfully']);
    }
}