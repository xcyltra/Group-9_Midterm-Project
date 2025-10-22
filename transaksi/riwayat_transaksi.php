<?php
session_start();
require_once '../src/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = 'Riwayat Transaksi';

$dari_tanggal = isset($_GET['dari_tanggal']) ? $_GET['dari_tanggal'] : date('Y-m-d');
$sampai_tanggal = isset($_GET['sampai_tanggal']) ? $_GET['sampai_tanggal'] : date('Y-m-d');
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($con, $_GET['status']) : 'semua';

$query = "SELECT t.*, u.nama_lengkap as nama_kasir 
          FROM transaksi t 
          JOIN users u ON t.id_kasir = u.id_user 
          WHERE DATE(t.tanggal_transaksi) BETWEEN '$dari_tanggal' AND '$sampai_tanggal'";

if ($_SESSION['role'] === 'kasir') {
    $query .= " AND t.id_kasir = " . $_SESSION['user_id'];
}

if ($status_filter !== 'semua') {
    $query .= " AND t.status = '$status_filter'";
}

$query .= " ORDER BY t.tanggal_transaksi DESC, t.id_transaksi DESC";
$result = mysqli_query($con, $query);

include '../partials/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="bi bi-clock-history"></i> Riwayat Transaksi</h2>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" class="form-control" name="dari_tanggal" value="<?= $dari_tanggal ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" class="form-control" name="sampai_tanggal" value="<?= $sampai_tanggal ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="semua" <?= $status_filter == 'semua' ? 'selected' : '' ?>>Semua</option>
                    <option value="selesai" <?= $status_filter == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                    <option value="batal" <?= $status_filter == 'batal' ? 'selected' : '' ?>>Batal</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>No. Transaksi</th>
                        <th>Tanggal</th>
                        <th>Kasir</th>
                        <th>Total</th>
                        <th>Metode Bayar</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if (mysqli_num_rows($result) > 0):
                        while ($row = mysqli_fetch_assoc($result)): 
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><strong><?= htmlspecialchars($row['no_transaksi']) ?></strong></td>
                        <td><?= date('d/m/Y H:i', strtotime($row['tanggal_transaksi'])) ?></td>
                        <td><?= htmlspecialchars($row['nama_kasir']) ?></td>
                        <td><strong>Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></strong></td>
                        <td>
                            <span class="badge bg-info">
                                <?= ucfirst($row['metode_pembayaran']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= $row['status'] == 'selesai' ? 'success' : 'danger' ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="detail_transaksi.php?id=<?= $row['id_transaksi'] ?>" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                            <a href="cetak_struk.php?id=<?= $row['id_transaksi'] ?>" class="btn btn-sm btn-primary" target="_blank">
                                <i class="bi bi-printer"></i>
                            </a>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data transaksi</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../partials/footer.php'; ?>