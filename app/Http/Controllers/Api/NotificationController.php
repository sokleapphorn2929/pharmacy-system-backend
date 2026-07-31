<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MongoDB\Laravel\Eloquent\Casts\ObjectId;

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
            // Convert to array if it's an object to standardize handling
            $notificationArray = (array) $notification;

            // Extract _id safely
            $id = $notificationArray['_id'] ?? null;
            if ($id instanceof ObjectId) {
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
        
        // If your MongoDB stores _id as ObjectId, you may need to wrap $id in an ObjectId instance depending on your package version:
        // e.g., new \MongoDB\BSON\ObjectId($id)
        $queryId = $id;
        if (class_exists(ObjectId::class) && ObjectId::isValid($id)) {
            $queryId = new ObjectId($id);
        }

        $notification = DB::connection('mongodb')
            ->collection('notifications')
            ->where('_id', $queryId)
            ->where('notifiable_id', (string) $user->_id)
            ->first();

        if ($notification) {
            DB::connection('mongodb')
                ->collection('notifications')
                ->where('_id', $queryId)
                ->update(['read_at' => now()]);

            return response()->json(['message' => 'Notification marked as read']);
        }

        return response()->json(['message' => 'Notification not found'], 404);
    }
}