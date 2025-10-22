<?php
session_start();
require_once '../src/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = 'Kelola Produk';

if (isset($_GET['hapus'])) {
    $id_produk = intval($_GET['hapus']);
    
    $check = mysqli_query($con, "SELECT id_detail FROM detail_transaksi WHERE id_produk = $id_produk LIMIT 1");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error_message'] = 'Produk tidak dapat dihapus karena sudah pernah ditransaksikan!';
    } else {
        $query = "DELETE FROM produk WHERE id_produk = $id_produk";
        if (mysqli_query($con, $query)) {
            $_SESSION['success_message'] = 'Produk berhasil dihapus!';
        } else {
            $_SESSION['error_message'] = 'Gagal menghapus produk!';
        }
    }
    header("Location: kelola_produk.php");
    exit();
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$kategori_filter = isset($_GET['kategori']) ? intval($_GET['kategori']) : 0;

$query = "SELECT p.*, k.nama_kategori 
          FROM produk p 
          LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
          WHERE 1=1";

if ($search) {
    $query .= " AND (p.nama_produk LIKE '%$search%' OR p.kode_produk LIKE '%$search%')";
}

if ($kategori_filter > 0) {
    $query .= " AND p.id_kategori = $kategori_filter";
}

$query .= " ORDER BY p.nama_produk ASC";
$result = mysqli_query($con, $query);

$kategori_list = mysqli_query($con, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

include '../partials/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Kelola Produk</h2>
            <a href="tambah_produk.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Produk
            </a>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <input type="text" class="form-control" name="search" placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-4">
                <select name="kategori" class="form-select">
                    <option value="0">Semua Kategori</option>
                    <?php while ($kat = mysqli_fetch_assoc($kategori_list)): ?>
                        <option value="<?= $kat['id_kategori'] ?>" <?= $kategori_filter == $kat['id_kategori'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kat['nama_kategori']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Cari
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
                        <th>Kode</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga Beli</th>
                        <th>Harga Jual</th>
                        <th>Stok</th>
                        <th>Satuan</th>
                        <th width="200">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if (mysqli_num_rows($result) > 0):
                        while ($row = mysqli_fetch_assoc($result)): 
                            $stok_class = $row['stok'] <= $row['stok_minimum'] ? 'stock-warning' : 'stock-ok';
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><code><?= htmlspecialchars($row['kode_produk']) ?></code></td>
                        <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                        <td><span class="badge bg-info"><?= htmlspecialchars($row['nama_kategori']) ?></span></td>
                        <td>Rp <?= number_format($row['harga_beli'], 0, ',', '.') ?></td>
                        <td>Rp <?= number_format($row['harga_jual'], 0, ',', '.') ?></td>
                        <td>
                            <span class="<?= $stok_class ?>">
                                <?= $row['stok'] ?>
                                <?php if ($row['stok'] <= $row['stok_minimum']): ?>
                                    <i class="bi bi-exclamation-triangle"></i>
                                <?php endif; ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['satuan']) ?></td>
                        <td>
                            <a href="edit_produk.php?id=<?= $row['id_produk'] ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="?hapus=<?= $row['id_produk'] ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Yakin ingin menghapus produk ini?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data produk</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../partials/footer.php'; ?>