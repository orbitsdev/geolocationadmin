<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;

class FCMController extends Controller
{
    public static function sendPushNotification($token, $title ='geolocation', $body='notification', $data=[])
{
    try {
        (new FirebaseService())->sendNotification( 
            $token,
            $title,
            $body,
            ['task_notification' => 'task_id']
        );

        return response()->json(['message' => 'Notification sent successfully'], 200);
    } catch (\Kreait\Firebase\Exception\MessagingException $e) {
        return response()->json([
            'message' => 'Failed to send notification',
            'error' => $e->errors(),
        ], 400);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
