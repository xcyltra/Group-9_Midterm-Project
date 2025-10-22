<?php
session_start();
require_once '../src/config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: riwayat_transaksi.php");
    exit();
}

$page_title = 'Detail Transaksi';
$id_transaksi = intval($_GET['id']);

$query = "SELECT t.*, u.nama_lengkap as nama_kasir, u.username 
          FROM transaksi t 
          JOIN users u ON t.id_kasir = u.id_user 
          WHERE t.id_transaksi = $id_transaksi";

if ($_SESSION['role'] === 'kasir') {
    $query .= " AND t.id_kasir = " . $_SESSION['user_id'];
}

$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) === 0) {
    $_SESSION['error_message'] = 'Transaksi tidak ditemukan!';
    header("Location: riwayat_transaksi.php");
    exit();
}

$transaksi = mysqli_fetch_assoc($result);

$query_detail = "SELECT dt.*, p.nama_produk, p.kode_produk, p.satuan, k.nama_kategori 
                 FROM detail_transaksi dt 
                 JOIN produk p ON dt.id_produk = p.id_produk 
                 LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
                 WHERE dt.id_transaksi = $id_transaksi";
$detail = mysqli_query($con, $query_detail);

include '../partials/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-file-text"></i> Detail Transaksi</h2>
            <div>
                <a href="cetak_struk.php?id=<?= $id_transaksi ?>" class="btn btn-primary" target="_blank">
                    <i class="bi bi-printer"></i> Cetak Struk
                </a>
                <a href="riwayat_transaksi.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Transaksi</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="150"><strong>No. Transaksi</strong></td>
                                <td>: <?= htmlspecialchars($transaksi['no_transaksi']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal</strong></td>
                                <td>: <?= date('d/m/Y H:i:s', strtotime($transaksi['tanggal_transaksi'])) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Kasir</strong></td>
                                <td>: <?= htmlspecialchars($transaksi['nama_kasir']) ?> (<?= htmlspecialchars($transaksi['username']) ?>)</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="150"><strong>Metode Pembayaran</strong></td>
                                <td>: <span class="badge bg-info"><?= ucfirst($transaksi['metode_pembayaran']) ?></span></td>
                            </tr>
                            <tr>
                                <td><strong>Status</strong></td>
                                <td>: <span class="badge bg-<?= $transaksi['status'] == 'selesai' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($transaksi['status']) ?>
                                </span></td>
                            </tr>
                            <?php if (!empty($transaksi['catatan'])): ?>
                            <tr>
                                <td><strong>Catatan</strong></td>
                                <td>: <?= htmlspecialchars($transaksi['catatan']) ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-list-ul"></i> Detail Produk</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="50">No</th>
                                <th>Kode</th>
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($item = mysqli_fetch_assoc($detail)): 
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><code><?= htmlspecialchars($item['kode_produk']) ?></code></td>
                                <td><?= htmlspecialchars($item['nama_produk']) ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($item['nama_kategori']) ?></span></td>
                                <td class="text-center"><?= $item['jumlah'] ?> <?= htmlspecialchars($item['satuan']) ?></td>
                                <td class="text-end">Rp <?= number_format($item['harga_satuan'], 0, ',', '.') ?></td>
                                <td class="text-end"><strong>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></strong></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-calculator"></i> Ringkasan Pembayaran</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Total Harga</strong></td>
                        <td class="text-end"><strong class="text-primary">Rp <?= number_format($transaksi['total_harga'], 0, ',', '.') ?></strong></td>
                    </tr>
                    <tr>
                        <td>Jumlah Bayar</td>
                        <td class="text-end">Rp <?= number_format($transaksi['jumlah_bayar'], 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td>Kembalian</td>
                        <td class="text-end">Rp <?= number_format($transaksi['kembalian'], 0, ',', '.') ?></td>
                    </tr>
                </table>
                
                <hr>
                
                <div class="d-grid gap-2">
                    <a href="cetak_struk.php?id=<?= $id_transaksi ?>" class="btn btn-primary" target="_blank">
                        <i class="bi bi-printer"></i> Cetak Ulang Struk
                    </a>
                    <a href="riwayat_transaksi.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali ke Riwayat
                    </a>
                    <?php if ($_SESSION['role'] === 'kasir'): ?>
                    <a href="kasir.php" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Transaksi Baru
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Tambahan</h6>
            </div>
            <div class="card-body">
                <small>
                    <ul class="mb-0">
                        <li>Transaksi ini <?= $transaksi['status'] == 'selesai' ? 'telah selesai' : 'dibatalkan' ?></li>
                        <li>Struk dapat dicetak ulang kapan saja</li>
                        <li>Data transaksi tersimpan permanen</li>
                        <?php if ($transaksi['status'] == 'selesai'): ?>
                        <li>Stok produk telah dikurangi</li>
                        <?php endif; ?>
                    </ul>
                </small>
            </div>
        </div>
    </div>
</div>

<?php include '../partials/footer.php'; ?>