<?php
session_start();
require_once '../src/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = '$username' AND is_active = 1";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../transaksi/kasir.php");
            }
            exit();
        } else {
            $_SESSION['error'] = 'Password salah!';
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = 'Username tidak ditemukan atau akun tidak aktif!';
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>