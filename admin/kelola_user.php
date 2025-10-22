<?php
session_start();
require_once '../src/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = 'Kelola User';

// Handle toggle status user
if (isset($_GET['toggle_status'])) {
    $id_user = intval($_GET['toggle_status']);
    $query = "UPDATE users SET is_active = NOT is_active WHERE id_user = $id_user";
    if (mysqli_query($con, $query)) {
        $_SESSION['success_message'] = 'Status user berhasil diubah!';
    } else {
        $_SESSION['error_message'] = 'Gagal mengubah status user!';
    }
    header("Location: kelola_user.php");
    exit();
}

// Handle hapus user
if (isset($_GET['hapus'])) {
    $id_user = intval($_GET['hapus']);
    
    // Cek apakah user pernah melakukan transaksi
    $check = mysqli_query($con, "SELECT id_transaksi FROM transaksi WHERE id_kasir = $id_user LIMIT 1");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error_message'] = 'User tidak dapat dihapus karena memiliki riwayat transaksi!';
    } else {
        $query = "DELETE FROM users WHERE id_user = $id_user";
        if (mysqli_query($con, $query)) {
            $_SESSION['success_message'] = 'User berhasil dihapus!';
        } else {
            $_SESSION['error_message'] = 'Gagal menghapus user!';
        }
    }
    header("Location: kelola_user.php");
    exit();
}

// Proses tambah user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah') {
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama_lengkap = mysqli_real_escape_string($con, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $no_telp = mysqli_real_escape_string($con, $_POST['no_telp']);
    $role = mysqli_real_escape_string($con, $_POST['role']);
    
    // Cek username
    $check = mysqli_query($con, "SELECT id_user FROM users WHERE username = '$username'");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error_message'] = 'Username sudah digunakan!';
    } else {
        $query = "INSERT INTO users (username, password, nama_lengkap, email, no_telp, role) 
                  VALUES ('$username', '$password', '$nama_lengkap', '$email', '$no_telp', '$role')";
        if (mysqli_query($con, $query)) {
            $_SESSION['success_message'] = 'User berhasil ditambahkan!';
        } else {
            $_SESSION['error_message'] = 'Gagal menambahkan user!';
        }
    }
    header("Location: kelola_user.php");
    exit();
}

// Proses edit user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id_user = intval($_POST['id_user']);
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $nama_lengkap = mysqli_real_escape_string($con, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $no_telp = mysqli_real_escape_string($con, $_POST['no_telp']);
    $role = mysqli_real_escape_string($con, $_POST['role']);
    
    // Update password jika diisi
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query = "UPDATE users SET username = '$username', password = '$password', 
                  nama_lengkap = '$nama_lengkap', email = '$email', no_telp = '$no_telp', role = '$role' 
                  WHERE id_user = $id_user";
    } else {
        $query = "UPDATE users SET username = '$username', 
                  nama_lengkap = '$nama_lengkap', email = '$email', no_telp = '$no_telp', role = '$role' 
                  WHERE id_user = $id_user";
    }
    
    if (mysqli_query($con, $query)) {
        $_SESSION['success_message'] = 'User berhasil diupdate!';
    } else {
        $_SESSION['error_message'] = 'Gagal mengupdate user!';
    }
    header("Location: kelola_user.php");
    exit();
}

// Ambil data user
$query = "SELECT * FROM users ORDER BY created_at DESC";
$result = mysqli_query($con, $query);

include '../partials/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Kelola User</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-person-plus"></i> Tambah User
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
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>No. Telp</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th width="200">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result)): 
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><strong><?= htmlspecialchars($row['username']) ?></strong></td>
                        <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['no_telp']) ?></td>
                        <td>
                            <span class="badge bg-<?= $row['role'] == 'admin' ? 'danger' : 'primary' ?>">
                                <?= ucfirst($row['role']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= $row['is_active'] ? 'success' : 'secondary' ?>">
                                <?= $row['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-warning" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEdit<?= $row['id_user'] ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="?toggle_status=<?= $row['id_user'] ?>" 
                               class="btn btn-sm btn-info"
                               onclick="return confirm('Yakin ingin mengubah status user ini?')">
                                <i class="bi bi-toggle-on"></i>
                            </a>
                            <?php if ($row['id_user'] != $_SESSION['user_id']): ?>
                            <a href="?hapus=<?= $row['id_user'] ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Yakin ingin menghapus user ini?')">
                                <i class="bi bi-trash"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="modalEdit<?= $row['id_user'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit User</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="id_user" value="<?= $row['id_user'] ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Username</label>
                                            <input type="text" class="form-control" name="username" 
                                                   value="<?= htmlspecialchars($row['username']) ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Password Baru</label>
                                            <input type="password" class="form-control" name="password">
                                            <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Nama Lengkap</label>
                                            <input type="text" class="form-control" name="nama_lengkap" 
                                                   value="<?= htmlspecialchars($row['nama_lengkap']) ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email" 
                                                   value="<?= htmlspecialchars($row['email']) ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">No. Telepon</label>
                                            <input type="text" class="form-control" name="no_telp" 
                                                   value="<?= htmlspecialchars($row['no_telp']) ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Role</label>
                                            <select class="form-select" name="role" required>
                                                <option value="admin" <?= $row['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                                <option value="kasir" <?= $row['role'] == 'kasir' ? 'selected' : '' ?>>Kasir</option>
                                            </select>
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

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="tambah">
                    
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" required minlength="4">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" required minlength="6">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_lengkap" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">No. Telepon</label>
                        <input type="text" class="form-control" name="no_telp">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" name="role" required>
                            <option value="">Pilih Role</option>
                            <option value="admin">Admin</option>
                            <option value="kasir">Kasir</option>
                        </select>
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