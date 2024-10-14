<?php

namespace App\Services;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Factory;

class FCMService
{
    protected $messaging;

    public function __construct()
    {
        $this->messaging = (new Factory)
        // ->withServiceAccount(config('firebase.credentials'))
        ->withServiceAccount(base_path('storage/app/firebase/firebase_credentials.json'))
        ->createMessaging();
    }

    public function sendNotification($token, $title, $body, $data = [])
    {
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(['title' => $title, 'body' => $body])
            ->withData($data);

        try {
            $this->messaging->send($message);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
