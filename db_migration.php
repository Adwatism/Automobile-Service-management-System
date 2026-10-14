<?php
require_once __DIR__ . '/includes/db.php';

try {
    // 1. Alter Enum status
    $stmt = $pdo->prepare("ALTER TABLE bookings MODIFY COLUMN status ENUM('Pending', 'Approved', 'Inspected', 'Assigned', 'In Progress', 'Completed', 'Rejected') NOT NULL DEFAULT 'Pending'");
    $stmt->execute();
    echo "Status ENUM altered successfully.\n";

    // 2. Add inspection columns
    // Check if column exists first to avoid errors on multiple runs
    $check_col = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'inspection_issues'");
    if ($check_col->rowCount() == 0) {
        $stmt = $pdo->prepare("
            ALTER TABLE bookings 
            ADD COLUMN inspection_issues TEXT AFTER issue_description,
            ADD COLUMN inspection_requests TEXT AFTER inspection_issues,
            ADD COLUMN inspection_cost DECIMAL(10,2) AFTER inspection_requests,
            ADD COLUMN inspection_remarks TEXT AFTER inspection_cost
        ");
        $stmt->execute();
        echo "Inspection columns added successfully.\n";
    } else {
        echo "Inspection columns already exist.\n";
    }

} catch (PDOException $e) {
    echo "Migration Failed: " . $e->getMessage() . "\n";
}
?>
