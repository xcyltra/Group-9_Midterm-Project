<?php
session_start();
require_once '../src/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = 'Tambah Produk';

// Proses tambah produk
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
    
    // Validasi kode produk unik
    $check = mysqli_query($con, "SELECT id_produk FROM produk WHERE kode_produk = '$kode_produk'");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error_message'] = 'Kode produk sudah digunakan!';
    } else {
        $query = "INSERT INTO produk (id_kategori, kode_produk, nama_produk, harga_beli, harga_jual, stok, stok_minimum, satuan, deskripsi) 
                  VALUES ($id_kategori, '$kode_produk', '$nama_produk', $harga_beli, $harga_jual, $stok, $stok_minimum, '$satuan', '$deskripsi')";
        
        if (mysqli_query($con, $query)) {
            $_SESSION['success_message'] = 'Produk berhasil ditambahkan!';
            header("Location: kelola_produk.php");
            exit();
        } else {
            $_SESSION['error_message'] = 'Gagal menambahkan produk: ' . mysqli_error($con);
        }
    }
}

// Ambil daftar kategori
$kategori_list = mysqli_query($con, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

include '../partials/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Tambah Produk</h2>
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
                            <input type="text" class="form-control" name="kode_produk" required placeholder="PRD-001">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_kategori" required>
                                <option value="">Pilih Kategori</option>
                                <?php while ($kat = mysqli_fetch_assoc($kategori_list)): ?>
                                    <option value="<?= $kat['id_kategori'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_produk" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Harga Beli <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="harga_beli" required min="0" step="0.01">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="harga_jual" required min="0" step="0.01">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stok Awal <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="stok" required min="0" value="0">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stok Minimum <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="stok_minimum" required min="0" value="5">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Satuan <span class="text-danger">*</span></label>
                            <select class="form-select" name="satuan" required>
                                <option value="pcs">Pcs</option>
                                <option value="box">Box</option>
                                <option value="kg">Kg</option>
                                <option value="gram">Gram</option>
                                <option value="liter">Liter</option>
                                <option value="ml">ML</option>
                                <option value="lusin">Lusin</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="3"></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Produk
                        </button>
                        <a href="kelola_produk.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Informasi</h6>
            </div>
            <div class="card-body">
                <small>
                    <ul class="mb-0">
                        <li>Kode produk harus unik</li>
                        <li>Harga jual sebaiknya lebih tinggi dari harga beli</li>
                        <li>Stok minimum untuk notifikasi stok menipis</li>
                        <li>Field bertanda <span class="text-danger">*</span> wajib diisi</li>
                    </ul>
                </small>
            </div>
        </div>
    </div>
</div>

<?php include '../partials/footer.php'; ?>