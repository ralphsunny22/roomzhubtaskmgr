<?php

namespace App\CentralLogics;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Helpers
{
    public static function generateJWT($user) {
        $payload = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $user->password,
            'timestamp' => now()->timestamp // Add a timestamp for expiration validation
        ];

        // Generate the JWT token
        $jwt = JWT::encode($payload, env('LOGIN_SECRET'), 'HS256');

        return $jwt;
    }
    /**
     * Encrypt data
     *
     * @param string $text
     * @param string $secretKey
     * @return array
     */
    public static function encryptData($text, $secretKey) {
        $algorithm = 'aes-256-cbc';

        // Ensure the secret key is a valid length for the algorithm
        $key = hash('sha256', $secretKey, true); // Hash the key to get 256-bit key
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($algorithm)); // Generate IV

        // Encrypt the text
        $encryptedText = openssl_encrypt($text, $algorithm, $key, OPENSSL_RAW_DATA, $iv);

        return [
            'iv' => bin2hex($iv), // Convert IV to hex for safe transport
            'data' => bin2hex($encryptedText) // Convert encrypted data to hex
        ];
    }

    public static function decryptData($hash, $secretKey) {
        try {
            $algorithm = 'aes-256-cbc';

            $key = hash('sha256', $secretKey, true); // Hash the key to get 256-bit key
            $iv = hex2bin($hash['iv']); // Convert IV back to binary
            $encryptedText = hex2bin($hash['data']); // Convert encrypted data back to binary

            $decryptedText = openssl_decrypt($encryptedText, $algorithm, $key, OPENSSL_RAW_DATA, $iv);

            // If decryption fails, $decryptedText will be false
            if ($decryptedText === false) {
                throw new \Exception('Decryption failed. Data may have been tampered with.');
            }

            return $decryptedText;
        } catch (\Exception $e) {
            // Log or handle the error as needed
            return false; // Return false to indicate decryption failure
        }
    }

    public static function error_processor($validator)
    {
        $err_keeper = [];
        foreach ($validator->errors()->getMessages() as $index => $error) {
            array_push($err_keeper, ['code' => $index, 'message' => $error[0]]);
        }
        return $err_keeper;
    }

    public static function upload(string $dir, string $format, $image, $default_image)
    {
        if ($image != null) {
            $imageName = \Carbon\Carbon::now()->toDateString() . "-" . uniqid() . "." . $format;
            if (!Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->makeDirectory($dir);
            }
            Storage::disk('public')->putFileAs($dir, $image, $imageName);
        } else {
            $imageName = $default_image;
        }

        return $imageName;
    }

    public static function uploadUrl($path, $extension, $file, $default = null)
    {
        if ($file) {
            $fileName = date('Y-m-d') . '-' . uniqid() . '.' . $extension;
            $file->storeAs('public/' . $path, $fileName);
            return url('storage/' . $path . $fileName); // Full URL of the uploaded file
        }
        return $default ? url('storage/' . $path . $default) : null;
    }


    public static function update(string $dir, $old_image, string $format, $image)
    {
        if ($image == null) {
            return $old_image;
        }
        if (Storage::disk('public')->exists($dir . $old_image)) {
            Storage::disk('public')->delete($dir . $old_image);
        }
        $imageName = Helpers::upload($dir, $format, $image, 'noimage.png');
        return $imageName;
    }

    public static function removeFile(string $dir, $old_image, $default_image)
    {
        if ($old_image !== $default_image) {
            if (Storage::disk('public')->exists($dir . $old_image)) {
                Storage::disk('public')->delete($dir . $old_image);
            }
        }

        //$imageName = Helpers::upload($dir, $format, $image, 'noimage.png');
        //return $imageName;
    }

    ///////////////////////////////////////////////////////////

    public static function getFirebaseCredentials()
    {
        // Load the JSON file. to generate this file in firebase, goto 'Project settings' >>> 'Service accounts' >>> 'Generate new private keys
        $path = storage_path('app/firebase/firebase_credentials.json');

        // Check if the file exists
        if (!file_exists($path)) {
            throw new \Exception("Firebase credentials file not found.");
        }

        // Read the contents of the file
        $jsonContent = file_get_contents($path);

        // Decode the JSON content into an array
        $credentials = json_decode($jsonContent, true);

        return $credentials;
    }


    public static function sendNotificationToHttp(array|null $data)
    {
        $config = self::get_business_settings('push_notification_service_file_content');
        $key = (array)$config;
        if($key['project_id']){
            $url = 'https://fcm.googleapis.com/v1/projects/'.$key['project_id'].'/messages:send';
            $headers = [
                'Authorization' => 'Bearer ' . self::getAccessToken($key),
                'Content-Type' => 'application/json',
            ];
            try {
                Http::withHeaders($headers)->post($url, $data);
            }catch (\Exception $exception){
                return false;
            }
        }
        return false;
    }

    public static function sendToFirebase(array|null $data)
    {
        // Retrieve Firebase project settings from your configuration
        $credentials = self::getFirebaseCredentials();
        $key = (array)$credentials;

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

    //firebase access token
    public static function getAccessToken($key)
    {
        $jwtToken = [
            'iss' => $key['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => time() + 3600,
            'iat' => time(),
        ];
        $jwtHeader = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $jwtPayload = base64_encode(json_encode($jwtToken));
        $unsignedJwt = $jwtHeader . '.' . $jwtPayload;
        openssl_sign($unsignedJwt, $signature, $key['private_key'], OPENSSL_ALGO_SHA256);
        $jwt = $unsignedJwt . '.' . base64_encode($signature);

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);
        return $response->json('access_token');
    }

    //to use the decryptData
    public function adminRegister(Request $request) {
        $data = $request->query('data');
        $iv = $request->query('iv');

        try {
            $secretKey = 'your_hex_encoded_secret_key'; // Should be 64 hex chars for AES-256

            $encryptedString = [
                'data' => $data,
                'iv' => $iv
            ];

            $decryptedData = decryptData($encryptedString, $secretKey);
            $userData = json_decode($decryptedData, true);

            // Log or handle decrypted data as needed
            \Log::info('Decrypted user data:', $userData);

            return view('admin.register', compact('userData'));
        } catch (\Exception $error) {
            \Log::error('Error on register page:', ['error' => $error->getMessage()]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public static function calculateDistance($latitude1, $longitude1, $latitude2, $longitude2)
    {
        $toRadian = function ($degree) {
            return ($degree * M_PI) / 180; // Convert degree to radian
        };

        $R = 6371; // Radius of Earth in kilometers

        $latDiff = $latitude2 - $latitude1;
        $dLat = $toRadian($latDiff);

        $lonDiff = $longitude2 - $longitude1;
        $dLon = $toRadian($lonDiff);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos($toRadian($latitude1)) * cos($toRadian($latitude2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $R * $c; // Distance in kilometers

        // Round to 1 decimal place
        return round($distance, 1);
    }

// // Example usage
// $distance = calculateDistance(25.3773009, 68.3034496, 25.3947972, 68.3312881);
// echo "Distance: " . $distance . " km";


}
