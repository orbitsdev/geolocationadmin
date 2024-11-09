<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;

class FCMController extends Controller
{
    public static function sendPushNotification($token, $title = 'Notification', $body = 'You have a new message', $data = [])
    {
        try {
            (new FirebaseService())->sendNotification(
                $token,
                $title,
                $body,
                $data
            );

            // Log notification success (Optional)
            \Log::info('Notification sent successfully', ['token' => $token, 'data' => $data]);

        } catch (\Kreait\Firebase\Exception\MessagingException $e) {
            \Log::error('Firebase Messaging Exception', ['error' => $e->errors()]);

            return response()->json([
                'message' => 'Failed to send notification',
                'error' => $e->errors(),
            ], 400);
        } catch (\Exception $e) {
            \Log::error('General Notification Error', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
