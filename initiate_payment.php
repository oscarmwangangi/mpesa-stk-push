<?php
include 'get_access_token.php';

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mpesa_payments";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function initiateSTKPush($accessToken, $shortcode, $passkey, $amount, $phone, $businessTill, $conn) {
    if (!$accessToken) {
        die('Access token is invalid or not available.');
    }

    $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

    $timestamp = date('YmdHis');
    $password = base64_encode($shortcode . $passkey . $timestamp);

    $data = [
        'BusinessShortCode' => $shortcode,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $amount,
        'PartyA' => $phone,
        'PartyB' => $businessTill,
        'PhoneNumber' => $phone,
        'CallBackURL' => 'https://your_callback_url', // Replace with your actual callback URL
        'AccountReference' => 'SchoolFees',
        'TransactionDesc' => 'Paying School Fees'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $response = curl_exec($ch);

    if ($response === false) {
        die('Curl error: ' . curl_error($ch));
    }

    curl_close($ch);

    $response = json_decode($response, true);

    // Insert the payment request into the database
    $stmt = $conn->prepare("INSERT INTO payments (phone, amount, merchant_request_id, checkout_request_id, status) VALUES (?, ?, ?, ?, ?)");
    $status = ($response['ResponseCode'] == 0) ? 'Pending' : 'Failed';
    $stmt->bind_param("sdsss", $phone, $amount, $response['MerchantRequestID'], $response['CheckoutRequestID'], $status);
    $stmt->execute();
    $stmt->close();

    return $response;
}

// Retrieve form data
$phone = $_POST['phone'];
$amount = $_POST['amount'];

// Use your actual values
$shortcode = '174379'; // Business Shortcode for PayBill
$passkey = 'your_actual_passkey'; // Replace with your actual passkey
$businessTill = '123456'; // Your business short code or till number
$response = initiateSTKPush($accessToken, $shortcode, $passkey, $amount, $phone, $businessTill, $conn);

$conn->close();

print_r($response);
?>
