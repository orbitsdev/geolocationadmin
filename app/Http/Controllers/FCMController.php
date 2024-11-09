<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;

class FCMController extends Controller
{
    public static function sendPushNotification(){

        try {

       (new FirebaseService())->sendNotification('dm7BpX-ORLC8ObJkndCxnu:APA91bG1Fkt6EoFUA7qfUzEP4KTiasyuyA0vqfHbTiCGWA7P9LLoXcB6cooILO-VYTizfvjjPZ4D2fjrHUUvSCO_wdaLQzPaB5smvrCNsP7pVfVe7d_iN1M', 'test', 'test', ['test']);


            return response()->json(['message' => 'Notification sent successfully'], 200);
        } catch (\Kreait\Firebase\Exception\MessagingException $e) {

            return response()->json(['message' => 'Failed to send notification', 'error' => $e->getMessage()], 400);
        } catch (\Exception $e) {

            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }



    }
}
