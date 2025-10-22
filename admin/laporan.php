<?php
session_start();
require_once '../src/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = 'Laporan Penjualan';

$dari_tanggal = isset($_GET['dari_tanggal']) ? $_GET['dari_tanggal'] : date('Y-m-01');
$sampai_tanggal = isset($_GET['sampai_tanggal']) ? $_GET['sampai_tanggal'] : date('Y-m-d');

$query = "SELECT DATE(tanggal_transaksi) as tanggal, 
          COUNT(*) as jumlah_transaksi, 
          SUM(total_harga) as total_pendapatan,
          SUM(jumlah_bayar - kembalian) as total_bayar
          FROM transaksi 
          WHERE status = 'selesai' 
          AND DATE(tanggal_transaksi) BETWEEN '$dari_tanggal' AND '$sampai_tanggal'
          GROUP BY DATE(tanggal_transaksi)
          ORDER BY tanggal DESC";
$result = mysqli_query($con, $query);

$query_total = "SELECT 
                COUNT(*) as total_transaksi,
                SUM(total_harga) as total_pendapatan
                FROM transaksi 
                WHERE status = 'selesai' 
                AND DATE(tanggal_transaksi) BETWEEN '$dari_tanggal' AND '$sampai_tanggal'";
$total = mysqli_fetch_assoc(mysqli_query($con, $query_total));

$query_terlaris = "SELECT p.nama_produk, k.nama_kategori, 
                   SUM(dt.jumlah) as total_terjual,
                   SUM(dt.subtotal) as total_nilai
                   FROM detail_transaksi dt
                   JOIN produk p ON dt.id_produk = p.id_produk
                   JOIN kategori k ON p.id_kategori = k.id_kategori
                   JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
                   WHERE t.status = 'selesai'
                   AND DATE(t.tanggal_transaksi) BETWEEN '$dari_tanggal' AND '$sampai_tanggal'
                   GROUP BY dt.id_produk
                   ORDER BY total_terjual DESC
                   LIMIT 10";
$produk_terlaris = mysqli_query($con, $query_terlaris);

include '../partials/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Laporan Penjualan</h2>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" class="form-control" name="dari_tanggal" value="<?= $dari_tanggal ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" class="form-control" name="sampai_tanggal" value="<?= $sampai_tanggal ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Tampilkan Laporan
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6>Total Transaksi</h6>
                <h2><?= $total['total_transaksi'] ?? 0 ?></h2>
                <small>Periode: <?= date('d/m/Y', strtotime($dari_tanggal)) ?> - <?= date('d/m/Y', strtotime($sampai_tanggal)) ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6>Total Pendapatan</h6>
                <h2>Rp <?= number_format($total['total_pendapatan'] ?? 0, 0, ',', '.') ?></h2>
                <small>Periode: <?= date('d/m/Y', strtotime($dari_tanggal)) ?> - <?= date('d/m/Y', strtotime($sampai_tanggal)) ?></small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Laporan Harian</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th class="text-center">Jumlah Transaksi</th>
                                <th class="text-end">Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (mysqli_num_rows($result) > 0):
                                while ($row = mysqli_fetch_assoc($result)): 
                            ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-info"><?= $row['jumlah_transaksi'] ?></span>
                                </td>
                                <td class="text-end">Rp <?= number_format($row['total_pendapatan'], 0, ',', '.') ?></td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="3" class="text-center">Tidak ada data transaksi</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-trophy"></i> Produk Terlaris</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-center">Terjual</th>
                                <th class="text-end">Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (mysqli_num_rows($produk_terlaris) > 0):
                                while ($row = mysqli_fetch_assoc($produk_terlaris)): 
                            ?>
                            <tr>
                                <td>
                                    <small><strong><?= htmlspecialchars($row['nama_produk']) ?></strong><br>
                                    <span class="text-muted"><?= htmlspecialchars($row['nama_kategori']) ?></span></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success"><?= $row['total_terjual'] ?></span>
                                </td>
                                <td class="text-end">
                                    <small>Rp <?= number_format($row['total_nilai'], 0, ',', '.') ?></small>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="3" class="text-center">Tidak ada data</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <button onclick="window.print()" class="btn btn-secondary">
            <i class="bi bi-printer"></i> Cetak Laporan
        </button>
    </div>
</div>

<style>
@media print {
    .no-print, .navbar, .btn, form {
        display: none !important;
    }
    .card {
        page-break-inside: avoid;
    }
}
</style>

<?php include '../partials/footer.php'; ?>