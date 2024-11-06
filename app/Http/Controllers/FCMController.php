<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;

class FCMController extends Controller
{
    public static function sendPushNotification($token, $title, $body, $data){

        try {

       (new FirebaseService())->sendNotification($token, $title, $body, $data);



            return response()->json(['message' => 'Notification sent successfully'], 200);
        } catch (\Kreait\Firebase\Exception\MessagingException $e) {

            return response()->json(['message' => 'Failed to send notification', 'error' => $e->getMessage()], 400);
        } catch (\Exception $e) {

            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }



    }
}
