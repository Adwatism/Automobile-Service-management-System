<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    $role = $_SESSION['role'];
    if ($role === 'admin') {
        redirect('/admin/dashboard.php');
    } elseif ($role === 'advisor') {
        redirect('/advisor/dashboard.php');
    } elseif ($role === 'mechanic') {
        redirect('/mechanic/dashboard.php');
    } else {
        redirect('/customer/dashboard.php');
    }
} else {
    redirect('/login.php');
}
?>
