<?php
/**
 * Saint Michael Technical High School
 * Student Registration Endpoint
 * File: register.php
 */

require_once __DIR__ . '/config.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, [], 'Method Not Allowed. Use POST.', 405);
}

// 1. Read Input (Handles both JSON payloads from fetch() and traditional HTML Form POST)
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data) {
    $data = $_POST;
}

// 2. Validate Required Fields
$requiredFields = ['name', 'dob', 'district', 'prog', 'parent', 'phone'];
$missing = [];

foreach ($requiredFields as $field) {
    if (empty($data[$field]) || trim($data[$field]) === '') {
        $missing[] = $field;
    }
}

if (!empty($missing)) {
    sendJsonResponse(false, ['missing_fields' => $missing], 'Missing required fields: ' . implode(', ', $missing), 400);
}

// 3. Sanitize and Extract Fields
$fullName = htmlspecialchars(trim($data['name']), ENT_QUOTES, 'UTF-8');
$dob      = trim($data['dob']);
$district = htmlspecialchars(trim($data['district']), ENT_QUOTES, 'UTF-8');
$school   = !empty($data['school']) ? htmlspecialchars(trim($data['school']), ENT_QUOTES, 'UTF-8') : null;
$prog     = trim($data['prog']);
$parent   = htmlspecialchars(trim($data['parent']), ENT_QUOTES, 'UTF-8');
$phone    = htmlspecialchars(trim($data['phone']), ENT_QUOTES, 'UTF-8');
$email    = !empty($data['email']) ? filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL) : null;
$notes    = !empty($data['msg']) ? htmlspecialchars(trim($data['msg']), ENT_QUOTES, 'UTF-8') : null;

// Validate Program Key
$allowedPrograms = ['p1', 'p2', 'p3', 'p4', 'p5'];
if (!in_array($prog, $allowedPrograms)) {
    sendJsonResponse(false, [], 'Invalid program selected.', 400);
}

// 4. Generate Unique Registration Code (e.g. SMTHS-2025-XXXX)
$year = date('Y');
$randomNumber = str_pad(mt_rand(100, 9999), 4, '0', STR_PAD_LEFT);
$regCode = "SMTHS-{$year}-{$randomNumber}";

// 5. Insert Record using PDO Prepared Statement (Prevents SQL Injection)
try {
    $pdo = getDbConnection();

    $stmt = $pdo->prepare("
        INSERT INTO registrations 
        (reg_code, full_name, dob, district, previous_school, program_id, parent_name, parent_phone, email, notes, status)
        VALUES 
        (:reg_code, :full_name, :dob, :district, :previous_school, :program_id, :parent_name, :parent_phone, :email, :notes, 'pending')
    ");

    $stmt->execute([
        ':reg_code'        => $regCode,
        ':full_name'       => $fullName,
        ':dob'             => $dob,
        ':district'        => $district,
        ':previous_school' => $school,
        ':program_id'      => $prog,
        ':parent_name'     => $parent,
        ':parent_phone'    => $phone,
        ':email'           => $email,
        ':notes'           => $notes,
    ]);

    $insertedId = $pdo->lastInsertId();

    sendJsonResponse(true, [
        'registration_id' => $insertedId,
        'reg_code'        => $regCode,
        'student_name'    => $fullName,
        'program'         => $prog,
        'status'          => 'pending',
        'whatsapp_message' => "Saint Michael Registration\nName: {$fullName}\nCode: {$regCode}\nTrade: {$prog}\nParent: {$parent} ({$phone})"
    ], 'Registration successfully saved in the database!', 201);

} catch (PDOException $e) {
    sendJsonResponse(false, ['error_detail' => $e->getMessage()], 'Failed to save student registration.', 500);
}
?>