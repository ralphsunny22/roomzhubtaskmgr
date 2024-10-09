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
}
