<?php
function getAccessToken($consumerKey, $consumerSecret) {
    $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

    $credentials = base64_encode($consumerKey . ':' . $consumerSecret);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);

    if ($response === false) {
        die('Curl error: ' . curl_error($ch));
    }

    curl_close($ch);

    $response = json_decode($response);

    if (isset($response->access_token)) {
        return $response->access_token;
    } else {
        die('Failed to obtain access token.');
    }
}

// Use your actual consumer key and secret
$consumerKey = 'd2VtApXQ7tFoWD4GRVBVmGGVHs9SazVQRO1eOHRIMG8hCAwU';
$consumerSecret = 'qw7iKlMW8z9F1AGKz1oWhGuUgGKkL8tM1QtGSUyTcQkkFYiTEy9TlAZ8hjeaQ8zi';
$accessToken = getAccessToken($consumerKey, $consumerSecret);

echo "Access Token: " . $accessToken;
?>
