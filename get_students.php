<?php
/**
 * Saint Michael Technical High School
 * Fetch Student Records & Administrative Reports
 * File: get_students.php
 */

require_once __DIR__ . '/config.php';

try {
    $pdo = getDbConnection();

    // Query parameters
    $program = isset($_GET['prog']) ? trim($_GET['prog']) : null;
    $status  = isset($_GET['status']) ? trim($_GET['status']) : null;
    $search  = isset($_GET['search']) ? trim($_GET['search']) : null;

    $sql = "
        SELECT r.*, p.name_en AS program_title, p.award
        FROM registrations r
        LEFT JOIN programs p ON r.program_id = p.id
        WHERE 1=1
    ";
    $params = [];

    if ($program) {
        $sql .= " AND r.program_id = :program";
        $params[':program'] = $program;
    }
    if ($status) {
        $sql .= " AND r.status = :status";
        $params[':status'] = $status;
    }
    if ($search) {
        $sql .= " AND (r.full_name LIKE :search OR r.reg_code LIKE :search OR r.parent_phone LIKE :search)";
        $params[':search'] = "%{$search}%";
    }

    $sql .= " ORDER BY r.created_at DESC LIMIT 100";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();

    // Also get summary counts
    $countStmt = $pdo->query("SELECT COUNT(*) as total_students FROM registrations");
    $totalStudents = $countStmt->fetch()['total_students'];

    $paymentStmt = $pdo->query("SELECT SUM(amount_rwf) as total_fees FROM payments");
    $totalFees = $paymentStmt->fetch()['total_fees'] ?? 0;

    sendJsonResponse(true, [
        'count'          => count($students),
        'total_students' => (int)$totalStudents,
        'total_fees_rwf' => (float)$totalFees,
        'students'       => $students
    ], 'Students fetched successfully.');

} catch (PDOException $e) {
    sendJsonResponse(false, ['error' => $e->getMessage()], 'Failed to fetch records.', 500);
}
?>