<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Exception\FirebaseException;

class FirebaseService{

    protected $messaging;

    public function __construct(){

        $factory = (new Factory)->withServiceAccount(storage_path('avante-foods-0c4324ce9d17.json'));
        $this->messaging = $factory->createMessaging();

    }

    public function sendNotification($deviceToken, $title, $body = [], $data = []){
        $message= CloudMessage::withTarget('token', $deviceToken)->withNotification(['title'=> $title, 'body'=> $body])->withData($data);


        $this->messaging->send($message);


    }





}
