<?php
session_start();
require_once '../src/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = mysqli_real_escape_string($con, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $no_telp = mysqli_real_escape_string($con, $_POST['no_telp']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if (strlen($username) < 4) {
        $_SESSION['error'] = 'Username minimal 4 karakter!';
        header("Location: register.php");
        exit();
    }

    if (strlen($password) < 6) {
        $_SESSION['error'] = 'Password minimal 6 karakter!';
        header("Location: register.php");
        exit();
    }

    if ($password !== $password_confirm) {
        $_SESSION['error'] = 'Password dan konfirmasi password tidak sama!';
        header("Location: register.php");
        exit();
    }

    $check_username = mysqli_query($con, "SELECT id_user FROM users WHERE username = '$username'");
    if (mysqli_num_rows($check_username) > 0) {
        $_SESSION['error'] = 'Username sudah digunakan!';
        header("Location: register.php");
        exit();
    }

    $check_email = mysqli_query($con, "SELECT id_user FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check_email) > 0) {
        $_SESSION['error'] = 'Email sudah digunakan!';
        header("Location: register.php");
        exit();
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO users (username, password, nama_lengkap, email, no_telp, role) 
              VALUES ('$username', '$password_hash', '$nama_lengkap', '$email', '$no_telp', 'kasir')";

    if (mysqli_query($con, $query)) {
        $_SESSION['success'] = 'Registrasi berhasil! Silakan login.';
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = 'Registrasi gagal: ' . mysqli_error($con);
        header("Location: register.php");
        exit();
    }
} else {
    header("Location: register.php");
    exit();
}
?>