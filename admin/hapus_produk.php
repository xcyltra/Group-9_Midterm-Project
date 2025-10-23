<?php
session_start();
require_once '../src/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = 'Hapus Produk';

if (!isset($_GET['id'])) {
    header("Location: kelola_produk.php");
    exit();
}

$id_produk = intval($_GET['id']);
$query = "SELECT * FROM produk WHERE id_produk = $id_produk";
$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) === 0) {
    $_SESSION['error_message'] = 'Produk tidak ditemukan!';
    header("Location: kelola_produk.php");
    exit();
}

$produk = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delete = mysqli_query($con, "DELETE FROM produk WHERE id_produk = $id_produk");
    if ($delete) {
        $_SESSION['success_message'] = 'Produk berhasil dihapus!';
        header("Location: kelola_produk.php");
        exit();
    } else {
        $_SESSION['error_message'] = 'Gagal menghapus produk: ' . mysqli_error($con);
    }
}

include '../partials/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Hapus Produk</h2>
            <a href="kelola_produk.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Konfirmasi Hapus Produk</h5>
            </div>
            <div class="card-body">
                <p>Apakah Anda yakin ingin menghapus produk berikut?</p>
                <table class="table table-borderless">
                    <tr>
                        <th width="30%">Kode Produk</th>
                        <td><?= htmlspecialchars($produk['kode_produk']) ?></td>
                    </tr>
                    <tr>
                        <th>Nama Produk</th>
                        <td><?= htmlspecialchars($produk['nama_produk']) ?></td>
                    </tr>
                    <tr>
                        <th>Kategori</th>
                        <td>
                            <?php
                            $kat = mysqli_query($con, "SELECT nama_kategori FROM kategori WHERE id_kategori = " . intval($produk['id_kategori']));
                            $kategori = mysqli_fetch_assoc($kat);
                            echo htmlspecialchars($kategori['nama_kategori']);
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Harga Jual</th>
                        <td>Rp <?= number_format($produk['harga_jual'], 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <th>Stok</th>
                        <td><?= intval($produk['stok']) . ' ' . htmlspecialchars($produk['satuan']) ?></td>
                    </tr>
                </table>
                <form method="POST">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-circle"></i> Data produk yang dihapus tidak dapat dikembalikan.
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Hapus Sekarang
                        </button>
                        <a href="kelola_produk.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Produk</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td>Dibuat:</td>
                        <td><?= date('d/m/Y H:i', strtotime($produk['created_at'])) ?></td>
                    </tr>
                    <tr>
                        <td>Update:</td>
                        <td><?= date('d/m/Y H:i', strtotime($produk['updated_at'])) ?></td>
                    </tr>
                </table>
                <hr>
                <small class="text-muted">
                    <ul class="mb-0">
                        <li>Pastikan data yang dihapus tidak sedang digunakan.</li>
                        <li>Periksa kembali transaksi terkait produk ini sebelum menghapus.</li>
                    </ul>
                </small>
            </div>
        </div>
    </div>
</div>

<?php include '../partials/footer.php'; ?