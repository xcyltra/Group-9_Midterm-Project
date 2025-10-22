<?php
session_start();
require_once '../src/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = 'Edit Produk';

// Ambil ID produk
if (!isset($_GET['id'])) {
    header("Location: kelola_produk.php");
    exit();
}

$id_produk = intval($_GET['id']);

// Proses update produk
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_kategori = intval($_POST['id_kategori']);
    $kode_produk = mysqli_real_escape_string($con, $_POST['kode_produk']);
    $nama_produk = mysqli_real_escape_string($con, $_POST['nama_produk']);
    $harga_beli = floatval($_POST['harga_beli']);
    $harga_jual = floatval($_POST['harga_jual']);
    $stok = intval($_POST['stok']);
    $stok_minimum = intval($_POST['stok_minimum']);
    $satuan = mysqli_real_escape_string($con, $_POST['satuan']);
    $deskripsi = mysqli_real_escape_string($con, $_POST['deskripsi']);
    
    // Validasi kode produk unik (kecuali untuk produk sendiri)
    $check = mysqli_query($con, "SELECT id_produk FROM produk WHERE kode_produk = '$kode_produk' AND id_produk != $id_produk");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error_message'] = 'Kode produk sudah digunakan!';
    } else {
        $query = "UPDATE produk SET 
                  id_kategori = $id_kategori,
                  kode_produk = '$kode_produk',
                  nama_produk = '$nama_produk',
                  harga_beli = $harga_beli,
                  harga_jual = $harga_jual,
                  stok = $stok,
                  stok_minimum = $stok_minimum,
                  satuan = '$satuan',
                  deskripsi = '$deskripsi'
                  WHERE id_produk = $id_produk";
        
        if (mysqli_query($con, $query)) {
            $_SESSION['success_message'] = 'Produk berhasil diupdate!';
            header("Location: kelola_produk.php");
            exit();
        } else {
            $_SESSION['error_message'] = 'Gagal mengupdate produk: ' . mysqli_error($con);
        }
    }
}

// Ambil data produk
$query = "SELECT * FROM produk WHERE id_produk = $id_produk";
$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) === 0) {
    $_SESSION['error_message'] = 'Produk tidak ditemukan!';
    header("Location: kelola_produk.php");
    exit();
}

$produk = mysqli_fetch_assoc($result);

// Ambil daftar kategori
$kategori_list = mysqli_query($con, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

include '../partials/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Edit Produk</h2>
            <a href="kelola_produk.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode Produk <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="kode_produk" required 
                                   value="<?= htmlspecialchars($produk['kode_produk']) ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_kategori" required>
                                <option value="">Pilih Kategori</option>
                                <?php while ($kat = mysqli_fetch_assoc($kategori_list)): ?>
                                    <option value="<?= $kat['id_kategori'] ?>" 
                                            <?= $kat['id_kategori'] == $produk['id_kategori'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($kat['nama_kategori']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_produk" required
                               value="<?= htmlspecialchars($produk['nama_produk']) ?>">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Harga Beli <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="harga_beli" required min="0" step="0.01"
                                   value="<?= $produk['harga_beli'] ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="harga_jual" required min="0" step="0.01"
                                   value="<?= $produk['harga_jual'] ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stok <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="stok" required min="0"
                                   value="<?= $produk['stok'] ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stok Minimum <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="stok_minimum" required min="0"
                                   value="<?= $produk['stok_minimum'] ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Satuan <span