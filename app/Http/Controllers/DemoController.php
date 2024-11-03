<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class PaymentController extends Controller
{
    public function initializePayment(Request $request)
    {
        $email = $request->get('email');
        $amount = $request->get('amount') * 100; // Convert amount to kobo

        $client = new Client([
            'base_uri' => 'https://api.paystack.co',
        ]);

        $data = [
            'email' => $email,
            'amount' => $amount,
        ];

        try {
            $response = $client->post('/transaction/initialize', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {
                $responseData = json_decode($response->getBody(), true);

                if ($responseData['status'] === true) {
                    $authorizationUrl = $responseData['data']['authorization_url'];

                    // Redirect to the authorization URL for payment
                    return redirect($authorizationUrl);
                } else {
                    // Handle error from Paystack
                    return response()->json(['error' => $responseData['message']], $statusCode);
                }
            } else {
                // Handle HTTP error from the request
                return response()->json(['error' => 'Failed to initialize payment'], $statusCode);
            }
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();

            // Log the error for debugging
            \Log::error("Paystack API Error: $statusCode - $responseBody");

            return response()->json(['error' => 'An error occurred during payment initialization'], $statusCode);
        }
    }
    //////////////////////////////////////////////////

    public function saveToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        // Assuming you have a user model and want to associate the token with a user
        $user = auth()->user(); // or however you get your user

        // Save or update the token in the database
        DB::table('user_tokens')->updateOrInsert(
            ['user_id' => $user->id],
            ['fcm_token' => $request->token]
        );

        return response()->json(['message' => 'Token saved successfully.']);
    }

    public static function sendToFirebase(array|null $data)
    {
        // Retrieve Firebase project settings from your configuration
        $config = self::get_business_settings('push_notification_service_file_content');
        $key = (array)$config;

        // Check if the project ID is set
        if ($key['project_id']) {
            // Define the FCM endpoint for sending messages
            $url = 'https://fcm.googleapis.com/v1/projects/' . $key['project_id'] . '/messages:send';

            // Get the access token using the service account key
            $accessToken = self::getAccessToken($key);

            // Prepare headers with the access token
            $headers = [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ];

            try {
                // Send the POST request to the FCM endpoint with the specified headers and data
                $response = Http::withHeaders($headers)->post($url, $data);

                // Check if the response is successful
                return $response->successful(); // Returns true if status code is 200-299
            } catch (\Exception $exception) {
                // Log the exception for debugging purposes
                \Log::error('FCM Notification Error: ' . $exception->getMessage());
                return false;
            }
        }

        // Return false if project ID is not set
        return false;
    }

    // Sample usage within your controller or service
    public function sendNotification($userId)
    {
        // Get the user's FCM token from the database
        $token = DB::table('user_tokens')->where('user_id', $userId)->value('fcm_token');

        if (!$token) {
            return response()->json(['message' => 'User token not found.'], 404);
        }

        // Construct the message payload
        $message = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => 'New Order',
                    'body' => 'You have a new order!',
                ],
            ],
        ];

        // Call the sendToFirebase function
        if (self::sendToFirebase($message)) {
            return response()->json(['message' => 'Notification sent successfully.']);
        }

        return response()->json(['message' => 'Failed to send notification.'], 500);
    }

}
