<?php
require_once 'includes/db.php';

session_start();
// Allow both Admin and Advisor roles
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'advisor'])) {
    die("Unauthorized access. Please login as Admin or Advisor.");
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Booking ID required.");
}

$query = "SELECT b.*, 
                 u.name as customer_name, u.email as customer_email,
                 v.name as vehicle_name, v.model as vehicle_model, v.number_plate, v.chassis_number,
                 s.name as service_name
          FROM bookings b
          JOIN users u ON b.customer_id = u.id
          JOIN vehicles v ON b.vehicle_id = v.id
          JOIN services s ON b.service_id = s.id
          WHERE b.id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);
$job = $stmt->fetch();

if (!$job) {
    die("Job not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Card #<?= $job['id'] ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 13px;
            color: #000;
        }
        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 20px;
            margin-bottom: 20px;
        }
        .top-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }
        .top-table td {
            padding: 8px;
            vertical-align: top;
        }
        .cell-label {
            display: inline-block;
            width: 110px;
            font-weight: normal;
        }
        .bottom-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }
        .bottom-table th {
            border-bottom: 1px solid #000;
            padding: 10px;
            text-align: left;
            background-color: #f8f9fa;
        }
        .bottom-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
            .bottom-table th { background-color: transparent !important; }
        }
        .print-btn {
            padding: 10px 20px;
            background: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="no-print print-btn">Print Job Card</button>

    <div class="header-title">JOB CARD / REPAIR ORDER</div>

    <table class="top-table">
        <tr>
            <!-- Column 1 -->
            <td style="width: 33%; border-right: 1px solid #000;">
                <div style="margin-bottom: 10px;"><span class="cell-label">Name</span> <strong><?= htmlspecialchars($job['customer_name']) ?></strong></div>
                <div style="margin-bottom: 10px;"><span class="cell-label">E-mail</span> <?= htmlspecialchars($job['customer_email']) ?></div>
                <div style="margin-bottom: 10px;"><span class="cell-label">Booking No</span> <?= $job['id'] ?></div>
                <div style="margin-bottom: 10px;"><span class="cell-label">Visit Type</span> Service Request</div>
            </td>
            <!-- Column 2 -->
            <td style="width: 33%; border-right: 1px solid #000;">
                <div style="margin-bottom: 10px;"><span class="cell-label">Reg. No</span> <strong><?= htmlspecialchars($job['number_plate']) ?></strong></div>
                <div style="margin-bottom: 10px;"><span class="cell-label">VIN</span> <?= htmlspecialchars($job['chassis_number'] ?? 'N/A') ?></div>
                <div style="margin-bottom: 10px;"><span class="cell-label">Model</span> <?= htmlspecialchars($job['vehicle_name'] . ' ' . $job['vehicle_model']) ?></div>
            </td>
            <!-- Column 3 -->
            <td style="width: 33%;">
                <div style="margin-bottom: 10px;"><span class="cell-label">Work Type</span> <?= htmlspecialchars($job['service_name']) ?></div>
                <div style="margin-bottom: 10px;"><span class="cell-label">RO Date</span> <?= date('d/m/Y', strtotime($job['booking_date'])) ?></div>
                <div style="margin-bottom: 10px;"><span class="cell-label">RO Number</span> R<?= date('Y') . sprintf('%04d', $job['id']) ?></div>
                <div style="margin-bottom: 10px;"><span class="cell-label">Printing Time</span> <?= date('d/m/Y H:i') ?></div>
            </td>
        </tr>
    </table>

    <table class="bottom-table">
        <thead>
            <tr>
                <th style="width: 25%; font-weight: bold;">Description</th>
                <th style="width: 30%; font-weight: bold;">Narration</th>
                <th style="width: 15%; font-weight: bold;">Type</th>
                <th style="width: 30%; font-weight: bold;">Job Control</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="border-right: 1px solid #000;">Original Request</td>
                <td style="border-right: 1px solid #000;"><?= nl2br(htmlspecialchars($job['issue_description'] ?: 'None specified')) ?></td>
                <td style="border-right: 1px solid #000;">Customer Demand</td>
                <td rowspan="4" style="vertical-align: top; border-bottom: 0;">
                    <div style="margin-bottom: 30px;">
                        <strong>Primary Job:</strong><br>
                        <?= htmlspecialchars($job['service_name']) ?>
                    </div>
                    <div style="margin-bottom: 30px;">
                        <strong>Secondary Job:</strong><br>
                        _________________
                    </div>
                    <div>
                        <strong>Additional Jobs:</strong><br>
                        _________________
                    </div>
                    
                    <div style="border-top: 1px dashed #000; padding-top: 15px; margin-top: 40px;">
                        Customer confirmation for Add Job:<br><br>
                        Customer Name: _______________________<br><br>
                        Time: _____________ &nbsp;&nbsp;&nbsp; Date: _____________
                    </div>
                </td>
            </tr>
            <!-- We don't display the 4th column in the remaining rows because of rowspan -->
            <tr>
                <td style="border-right: 1px solid #000;">Issues Found</td>
                <td style="border-right: 1px solid #000; color: #dc3545; font-weight: bold;"><?= nl2br(htmlspecialchars($job['inspection_issues'] ?? '')) ?></td>
                <td style="border-right: 1px solid #000;">Advisor Inspection</td>
            </tr>
            <tr>
                <td style="border-right: 1px solid #000;">Additional Requests</td>
                <td style="border-right: 1px solid #000;"><?= nl2br(htmlspecialchars($job['inspection_requests'] ?? '')) ?></td>
                <td style="border-right: 1px solid #000;">Additional Job</td>
            </tr>
            <tr>
                <td style="border-right: 1px solid #000; border-bottom: 0;">Estimated Service Cost</td>
                <td style="border-right: 1px solid #000; border-bottom: 0; font-size: 16px; font-weight: bold;">₹<?= number_format($job['inspection_cost'] ?? 0, 2) ?></td>
                <td style="border-right: 1px solid #000; border-bottom: 0;">Service Quote</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
