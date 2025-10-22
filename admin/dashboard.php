<?php
session_start();
require_once '../src/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = 'Dashboard Admin';

// Query untuk statistik
$query_total_produk = mysqli_query($con, "SELECT COUNT(*) as total FROM produk");
$total_produk = mysqli_fetch_assoc($query_total_produk)['total'];

$query_total_transaksi = mysqli_query($con, "SELECT COUNT(*) as total FROM transaksi WHERE status = 'selesai'");
$total_transaksi = mysqli_fetch_assoc($query_total_transaksi)['total'];

$query_total_pendapatan = mysqli_query($con, "SELECT SUM(total_harga) as total FROM transaksi WHERE status = 'selesai'");
$total_pendapatan = mysqli_fetch_assoc($query_total_pendapatan)['total'] ?? 0;

$query_stok_menipis = mysqli_query($con, "SELECT COUNT(*) as total FROM produk WHERE stok <= stok_minimum");
$stok_menipis = mysqli_fetch_assoc($query_stok_menipis)['total'];

// Transaksi hari ini
$query_transaksi_hari_ini = mysqli_query($con, "SELECT COUNT(*) as total, SUM(total_harga) as pendapatan FROM transaksi WHERE DATE(tanggal_transaksi) = CURDATE() AND status = 'selesai'");
$transaksi_hari_ini = mysqli_fetch_assoc($query_transaksi_hari_ini);

// Produk terlaris
$query_produk_terlaris = mysqli_query($con, "
    SELECT p.nama_produk, SUM(dt.jumlah) as total_terjual 
    FROM detail_transaksi dt 
    JOIN produk p ON dt.id_produk = p.id_produk 
    JOIN transaksi t ON dt.id_transaksi = t.id_transaksi 
    WHERE t.status = 'selesai' 
    GROUP BY dt.id_produk 
    ORDER BY total_terjual DESC 
    LIMIT 5
");

include '../partials/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Dashboard Admin</h2>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Produk</h6>
                        <h2 class="mb-0"><?= $total_produk ?></h2>
                    </div>
                    <i class="bi bi-box-seam" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Transaksi</h6>
                        <h2 class="mb-0"><?= $total_transaksi ?></h2>
                    </div>
                    <i class="bi bi-cart-check" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Pendapatan</h6>
                        <h2 class="mb-0">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h2>
                    </div>
                    <i class="bi bi-cash-stack" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Stok Menipis</h6>
                        <h2 class="mb-0"><?= $stok_menipis ?></h2>
                    </div>
                    <i class="bi bi-exclamation-triangle" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Transaksi Hari Ini</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h6 class="text-muted">Jumlah Transaksi</h6>
                        <h3><?= $transaksi_hari_ini['total'] ?? 0 ?></h3>
                    </div>
                    <div class="col-6">
                        <h6 class="text-muted">Pendapatan</h6>
                        <h3>Rp <?= number_format($transaksi_hari_ini['pendapatan'] ?? 0, 0, ',', '.') ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-trophy"></i> Produk Terlaris</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th class="text-end">Terjual</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($query_produk_terlaris)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                            <td class="text-end"><span class="badge bg-success"><?= $row['total_terjual'] ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../partials/footer.php'; ?>