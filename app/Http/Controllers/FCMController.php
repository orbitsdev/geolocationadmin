<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;

class FCMController extends Controller
{
    public static function sendPushNotification()
{
    try {
        (new FirebaseService())->sendNotification(
            'fUyNeZkhQ-6wn2-S-Jn48C:APA91bHZSBE0Lu8bOBpc98TPcXi6BywPoTpFr9aXfQjuJjIhK_6H8mlaoNRdpu_U2YXbLghaM-v1DiNH_8jMLcrhLcoCoPL4eiF8ioZp8oacivLXBqi1SC8',
            'Test Title',
            'Test Body',
            ['extraKey' => 'extraValue']
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
