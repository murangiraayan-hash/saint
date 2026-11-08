<?php
/**
 * Saint Michael Technical High School
 * Fee Payment Processing Endpoint
 * File: pay.php
 */

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, [], 'Method Not Allowed. Use POST.', 405);
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data) {
    $data = $_POST;
}

// Required fields for payment
$name    = isset($data['name']) ? trim($data['name']) : '';
$phone   = isset($data['phone']) ? trim($data['phone']) : '';
$prog    = isset($data['prog']) ? trim($data['prog']) : '';
$method  = isset($data['method']) ? trim($data['method']) : '';
$amount  = isset($data['amount']) ? floatval($data['amount']) : 20000;
$item    = isset($data['item']) ? trim($data['item']) : 'registration';
$regCode = isset($data['regCode']) ? trim($data['regCode']) : null;

if (empty($name) || empty($phone) || empty($prog) || empty($method)) {
    sendJsonResponse(false, [], 'Missing student name, phone, program, or payment method.', 400);
}

// Generate Unique Transaction Reference (e.g. TXN-MOMO-84920)
$methodPrefix = strtoupper($method);
$txnRef = "TXN-{$methodPrefix}-" . mt_rand(10000, 99999);

try {
    $pdo = getDbConnection();

    $stmt = $pdo->prepare("
        INSERT INTO payments 
        (txn_ref, reg_code, student_name, phone, program_id, payment_method, amount_rwf, item_type, status)
        VALUES 
        (:txn_ref, :reg_code, :student_name, :phone, :program_id, :payment_method, :amount_rwf, :item_type, 'completed')
    ");

    $stmt->execute([
        ':txn_ref'        => $txnRef,
        ':reg_code'       => $regCode,
        ':student_name'   => $name,
        ':phone'          => $phone,
        ':program_id'     => $prog,
        ':payment_method' => $method,
        ':amount_rwf'     => $amount,
        ':item_type'      => $item,
    ]);

    // If a registration code was provided, mark registration as verified
    if ($regCode) {
        $updateStmt = $pdo->prepare("UPDATE registrations SET status = 'verified' WHERE reg_code = :reg_code");
        $updateStmt->execute([':reg_code' => $regCode]);
    }

    sendJsonResponse(true, [
        'transaction_ref' => $txnRef,
        'student_name'    => $name,
        'amount_rwf'      => $amount,
        'method'          => $method,
        'item'            => $item,
        'receipt_note'    => 'Payment received by Saint Michael Technical High School. Please present this reference at school.'
    ], 'Fee payment recorded successfully!', 201);

} catch (PDOException $e) {
    sendJsonResponse(false, ['error' => $e->getMessage()], 'Failed to record payment.', 500);
}
?>