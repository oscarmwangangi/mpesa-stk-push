<?php
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

// Get the raw POST data from the callback
$data = file_get_contents('php://input');

// Decode the JSON data
$transaction = json_decode($data, true);

// Extract the details you need
$merchantRequestID = $transaction['Body']['stkCallback']['MerchantRequestID'];
$checkoutRequestID = $transaction['Body']['stkCallback']['CheckoutRequestID'];
$resultCode = $transaction['Body']['stkCallback']['ResultCode'];
$resultDesc = $transaction['Body']['stkCallback']['ResultDesc'];

// Check if the transaction was successful
if ($resultCode == 0) {
    $amount = $transaction['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value'];
    $mpesaReceiptNumber = $transaction['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value'];
    $transactionDate = $transaction['Body']['stkCallback']['CallbackMetadata']['Item'][2]['Value'];
    $phoneNumber = $transaction['Body']['stkCallback']['CallbackMetadata']['Item'][3]['Value'];
} else {
    $amount = null;
    $mpesaReceiptNumber = null;
    $transactionDate = null;
    $phoneNumber = null;
}

// Update the payment record in the database
$stmt = $conn->prepare("UPDATE payments SET result_code = ?, result_desc = ?, mpesa_receipt_number = ?, transaction_date = ?, status = ? WHERE checkout_request_id = ?");
$status = ($resultCode == 0) ? 'Completed' : 'Failed';
$stmt->bind_param("isssss", $resultCode, $resultDesc, $mpesaReceiptNumber, $transactionDate, $status, $checkoutRequestID);
$stmt->execute();
$stmt->close();

$conn->close();
?>
