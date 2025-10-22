<?php
session_start();
require_once '../src/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = 'Kelola Kategori';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah') {
    $nama_kategori = mysqli_real_escape_string($con, $_POST['nama_kategori']);
    $deskripsi = mysqli_real_escape_string($con, $_POST['deskripsi']);
    
    $query = "INSERT INTO kategori (nama_kategori, deskripsi) VALUES ('$nama_kategori', '$deskripsi')";
    if (mysqli_query($con, $query)) {
        $_SESSION['success_message'] = 'Kategori berhasil ditambahkan!';
    } else {
        $_SESSION['error_message'] = 'Gagal menambahkan kategori: ' . mysqli_error($con);
    }
    header("Location: kelola_kategori.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id_kategori = intval($_POST['id_kategori']);
    $nama_kategori = mysqli_real_escape_string($con, $_POST['nama_kategori']);
    $deskripsi = mysqli_real_escape_string($con, $_POST['deskripsi']);
    
    $query = "UPDATE kategori SET nama_kategori = '$nama_kategori', deskripsi = '$deskripsi' WHERE id_kategori = $id_kategori";
    if (mysqli_query($con, $query)) {
        $_SESSION['success_message'] = 'Kategori berhasil diupdate!';
    } else {
        $_SESSION['error_message'] = 'Gagal mengupdate kategori: ' . mysqli_error($con);
    }
    header("Location: kelola_kategori.php");
    exit();
}

if (isset($_GET['hapus'])) {
    $id_kategori = intval($_GET['hapus']);
    
    $check = mysqli_query($con, "SELECT id_produk FROM produk WHERE id_kategori = $id_kategori LIMIT 1");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error_message'] = 'Kategori tidak dapat dihapus karena masih digunakan!';
    } else {
        $query = "DELETE FROM kategori WHERE id_kategori = $id_kategori";
        if (mysqli_query($con, $query)) {
            $_SESSION['success_message'] = 'Kategori berhasil dihapus!';
        } else {
            $_SESSION['error_message'] = 'Gagal menghapus kategori: ' . mysqli_error($con);
        }
    }
    header("Location: kelola_kategori.php");
    exit();
}

$query = "SELECT k.*, COUNT(p.id_produk) as jumlah_produk 
          FROM kategori k 
          LEFT JOIN produk p ON k.id_kategori = p.id_kategori 
          GROUP BY k.id_kategori 
          ORDER BY k.nama_kategori ASC";
$result = mysqli_query($con, $query);

include '../partials/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Kelola Kategori</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-circle"></i> Tambah Kategori
            </button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th width="150">Jumlah Produk</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result)): 
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                        <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                        <td><span class="badge bg-info"><?= $row['jumlah_produk'] ?> produk</span></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-warning" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEdit<?= $row['id_kategori'] ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="?hapus=<?= $row['id_kategori'] ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="modalEdit<?= $row['id_kategori'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Kategori</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="id_kategori" value="<?= $row['id_kategori'] ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Nama Kategori</label>
                                            <input type="text" class="form-control" name="nama_kategori" 
                                                   value="<?= htmlspecialchars($row['nama_kategori']) ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Deskripsi</label>
                                            <textarea class="form-control" name="deskripsi" rows="3"><?= htmlspecialchars($row['deskripsi']) ?></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="tambah">
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_kategori" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../partials/footer.php'; ?>