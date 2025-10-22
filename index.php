<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: transaksi/kasir.php");
    }
    exit();
}

header("Location: auth/login.php");
exit();
?>