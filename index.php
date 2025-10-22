<?php
session_start();

// Redirect ke halaman login jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

// Redirect berdasarkan role
if ($_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard.php");
} else {
    header("Location: transaksi/kasir.php");
}
exit();
?>