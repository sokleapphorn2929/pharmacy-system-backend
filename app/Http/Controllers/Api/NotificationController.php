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

        // Use table() instead of collection()
        $rawNotifications = DB::connection('mongodb')
            ->table('notifications')
            ->where('notifiable_id', (string) $user->_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $notifications = $rawNotifications->map(function ($notification) {
            $notificationArray = (array) $notification;

            $id = $notificationArray['_id'] ?? null;
            if ($id instanceof \MongoDB\BSON\ObjectId) {
                $id = (string) $id;
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
        
        $queryId = $id;
        if (class_exists(\MongoDB\BSON\ObjectId::class) && \MongoDB\BSON\ObjectId::isValid($id)) {
            $queryId = new \MongoDB\BSON\ObjectId($id);
        }

        // Use table() here as well
        $notification = DB::connection('mongodb')
            ->table('notifications')
            ->where('_id', $queryId)
            ->where('notifiable_id', (string) $user->_id)
            ->first();

        if ($notification) {
            DB::connection('mongodb')
                ->table('notifications')
                ->where('_id', $queryId)
                ->update(['read_at' => now()]);

            return response()->json(['message' => 'Notification marked as read']);
        }

        return response()->json(['message' => 'Notification not found'], 404);
    }
}