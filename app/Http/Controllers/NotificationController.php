<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function getNotifications(Request $request)
    {


        $page = $request->query('page', 1);
        $perPage = $request->query('perPage', 20);
        $user = $request->user();

        $notifications = DB::table('notifications')
            ->select('id', 'type', 'notifiable_id', 'data', 'read_at', 'created_at', 'updated_at')
            ->where('notifiable_id', $user->id) 
            ->where('notifiable_type', User::class)
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);
    


   return ApiResponse::paginated($notifications, 'Notification retrieved successfully', \App\Http\Resources\NotificationResource::class);
    }


    public function markMultipleAsRead(Request $request)
    {
        $validated = $request->validate([
            
            'notification_ids' => ['required', 'array'],
            'notification_ids.*' => ['exists:notifications,id'],
        ]);

        $user = $request->user();

        $updated = DB::table('notifications')
            ->whereIn('id', $validated['notification_ids'])
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', User::class)
            ->update(['read_at' => now()]);

        if ($updated) {

            return ApiResponse::success([],'Notifications marked as read');
            
        }
        
        return ApiResponse::error('Failed to mark notifications as read',400);

    }

}
