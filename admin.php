<?php
/**
 * Saint Michael Technical High School
 * Standalone Admin Portal & CSV Exporter
 * File: admin.php
 */

require_once __DIR__ . '/config.php';
$pdo = getDbConnection();

// Handle CSV Export
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=SMTHS_Students_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Reg Code', 'Full Name', 'Date of Birth', 'District', 'Program', 'Parent Name', 'Parent Phone', 'Email', 'Status', 'Date']);
    
    $stmt = $pdo->query("SELECT * FROM registrations ORDER BY id DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['id'], $row['reg_code'], $row['full_name'], $row['dob'], 
            $row['district'], $row['program_id'], $row['parent_name'], 
            $row['parent_phone'], $row['email'], $row['status'], $row['created_at']
        ]);
    }
    fclose($output);
    exit();
}

// Fetch stats
$totalStudents = $pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
$totalPayments = $pdo->query("SELECT COALESCE(SUM(amount_rwf), 0) FROM payments")->fetchColumn();
$recentStudents = $pdo->query("SELECT * FROM registrations ORDER BY id DESC LIMIT 50")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Saint Michael THS - Admin Portal</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #FFF3E8; color: #59171B; margin: 0; padding: 24px; }
        .container { max-width: 1100px; margin: 0 auto; background: #fff; padding: 28px; border-radius: 14px; border: 3px solid #59171B; box-shadow: 0 10px 25px rgba(89,23,27,0.1); }
        header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #59171B; padding-bottom: 16px; margin-bottom: 24px; }
        h1 { margin: 0; color: #59171B; font-size: 1.5rem; }
        .stats { display: flex; gap: 20px; margin-bottom: 24px; }
        .stat-card { flex: 1; background: #FED7B8; padding: 18px; border-radius: 10px; border-left: 6px solid #59171B; }
        .stat-card b { display: block; font-size: 0.9rem; text-transform: uppercase; }
        .stat-card span { font-size: 1.8rem; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { border: 1px solid #ddd; padding: 10px 14px; text-align: left; }
        th { background: #59171B; color: #FED7B8; }
        tr:nth-child(even) { background: #fffcf8; }
        .btn { display: inline-block; background: #59171B; color: #FED7B8; padding: 10px 18px; text-decoration: none; border-radius: 6px; font-weight: bold; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; }
        .badge.pending { background: #FEF3C7; color: #92400E; }
        .badge.verified { background: #D1FAE5; color: #065F46; }
        .badge.admitted { background: #DBEAFE; color: #1E40AF; }
    </style>
</head>
<body>
<div class="container">
    <header>
        <div>
            <h1>Saint Michael Technical High School</h1>
            <p>Admin Database & Registration Portal (PHP + MySQL)</p>
        </div>
        <a href="?action=export_csv" class="btn">📥 Export to CSV</a>
    </header>

    <div class="stats">
        <div class="stat-card">
            <b>Total Registered Students</b>
            <span><?= number_format($totalStudents) ?></span>
        </div>
        <div class="stat-card">
            <b>Total Fees Collected</b>
            <span><?= number_format($totalPayments) ?> RWF</span>
        </div>
    </div>

    <h2>Registered Students List</h2>
    <table>
        <thead>
            <tr>
                <th>Reg Code</th>
                <th>Full Name</th>
                <th>Trade</th>
                <th>District</th>
                <th>Parent Phone</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($recentStudents)): ?>
                <tr><td colspan="7" style="text-align:center;">No student registrations yet.</td></tr>
            <?php else: ?>
                <?php foreach ($recentStudents as $s): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($s['reg_code']) ?></strong></td>
                    <td><?= htmlspecialchars($s['full_name']) ?></td>
                    <td><?= htmlspecialchars(strtoupper($s['program_id'])) ?></td>
                    <td><?= htmlspecialchars($s['district']) ?></td>
                    <td><?= htmlspecialchars($s['parent_phone']) ?></td>
                    <td><span class="badge <?= htmlspecialchars($s['status']) ?>"><?= ucfirst($s['status']) ?></span></td>
                    <td><?= htmlspecialchars(substr($s['created_at'], 0, 10)) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>