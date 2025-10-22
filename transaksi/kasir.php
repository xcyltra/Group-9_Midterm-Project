<?php
session_start();
require_once '../src/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = 'Kasir';

$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$kategori_filter = isset($_GET['kategori']) ? intval($_GET['kategori']) : 0;

$query = "SELECT p.*, k.nama_kategori 
          FROM produk p 
          LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
          WHERE p.stok > 0";

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
        <h2 class="mb-4"><i class="bi bi-calculator"></i> Kasir</h2>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" class="row g-2">
                    <div class="col-md-7">
                        <input type="text" class="form-control" name="search" placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="kategori" class="form-select">
                            <option value="0">Semua</option>
                            <?php 
                            mysqli_data_seek($kategori_list, 0);
                            while ($kat = mysqli_fetch_assoc($kategori_list)): 
                            ?>
                                <option value="<?= $kat['id_kategori'] ?>" <?= $kategori_filter == $kat['id_kategori'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kat['nama_kategori']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-2" style="max-height: 600px; overflow-y: auto;">
            <?php 
            if (mysqli_num_rows($result) > 0):
                while ($row = mysqli_fetch_assoc($result)): 
            ?>
            <div class="col-md-6">
                <div class="card product-item" onclick="addToCart(<?= $row['id_produk'] ?>, '<?= htmlspecialchars($row['nama_produk'], ENT_QUOTES) ?>', <?= $row['harga_jual'] ?>, <?= $row['stok'] ?>)">
                    <div class="card-body">
                        <h6 class="card-title mb-1"><?= htmlspecialchars($row['nama_produk']) ?></h6>
                        <small class="text-muted"><?= htmlspecialchars($row['nama_kategori']) ?></small>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="badge bg-primary">Rp <?= number_format($row['harga_jual'], 0, ',', '.') ?></span>
                            <small>Stok: <strong><?= $row['stok'] ?></strong></small>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
                endwhile;
            else:
            ?>
            <div class="col-12">
                <div class="alert alert-info">Tidak ada produk tersedia</div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-cart3"></i> Keranjang Belanja</h5>
            </div>
            <div class="card-body">
                <div id="cart-items" style="max-height: 300px; overflow-y: auto;">
                    <p class="text-center text-muted">Keranjang masih kosong</p>
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Total:</strong>
                        <strong id="total-harga" class="text-primary">Rp 0</strong>
                    </div>
                </div>

                <form id="form-pembayaran" action="proses_transaksi.php" method="POST">
                    <input type="hidden" name="cart_data" id="cart-data">
                    
                    <div class="mb-3">
                        <label class="form-label">Metode Pembayaran</label>
                        <select class="form-select" name="metode_pembayaran" required>
                            <option value="tunai">Tunai</option>
                            <option value="debit">Debit</option>
                            <option value="kredit">Kredit</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jumlah Bayar</label>
                        <input type="number" class="form-control" name="jumlah_bayar" id="jumlah-bayar" required min="0" step="1">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kembalian</label>
                        <input type="text" class="form-control" id="kembalian" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan (Opsional)</label>
                        <textarea class="form-control" name="catatan" rows="2"></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg" id="btn-proses" disabled>
                            <i class="bi bi-check-circle"></i> Proses Transaksi
                        </button>
                        <button type="button" class="btn btn-danger" onclick="clearCart()">
                            <i class="bi bi-trash"></i> Kosongkan Keranjang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let cart = [];

function addToCart(id, nama, harga, stokMax) {
    const existingItem = cart.find(item => item.id === id);
    
    if (existingItem) {
        if (existingItem.qty < stokMax) {
            existingItem.qty++;
        } else {
            alert('Stok tidak mencukupi!');
            return;
        }
    } else {
        cart.push({
            id: id,
            nama: nama,
            harga: harga,
            qty: 1,
            stokMax: stokMax
        });
    }
    
    updateCart();
}

function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    updateCart();
}

function updateQty(id, qty) {
    const item = cart.find(item => item.id === id);
    if (item) {
        const newQty = parseInt(qty);
        if (newQty > 0 && newQty <= item.stokMax) {
            item.qty = newQty;
        } else if (newQty > item.stokMax) {
            alert('Jumlah melebihi stok tersedia!');
            item.qty = item.stokMax;
        }
        updateCart();
    }
}

function updateCart() {
    const cartItemsDiv = document.getElementById('cart-items');
    const totalHargaSpan = document.getElementById('total-harga');
    const cartDataInput = document.getElementById('cart-data');
    const btnProses = document.getElementById('btn-proses');
    
    if (cart.length === 0) {
        cartItemsDiv.innerHTML = '<p class="text-center text-muted">Keranjang masih kosong</p>';
        totalHargaSpan.textContent = 'Rp 0';
        btnProses.disabled = true;
        return;
    }
    
    let html = '';
    let total = 0;
    
    cart.forEach(item => {
        const subtotal = item.harga * item.qty;
        total += subtotal;
        
        html += `
            <div class="cart-item">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="flex-grow-1">
                        <strong>${item.nama}</strong><br>
                        <small>Rp ${formatRupiah(item.harga)} x ${item.qty}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeFromCart(${item.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="quantity-control">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="updateQty(${item.id}, ${item.qty - 1})">-</button>
                        <input type="number" class="form-control form-control-sm" value="${item.qty}" onchange="updateQty(${item.id}, this.value)" min="1" max="${item.stokMax}">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="updateQty(${item.id}, ${item.qty + 1})">+</button>
                    </div>
                    <strong class="text-primary">Rp ${formatRupiah(subtotal)}</strong>
                </div>
            </div>
        `;
    });
    
    cartItemsDiv.innerHTML = html;
    totalHargaSpan.textContent = 'Rp ' + formatRupiah(total);
    cartDataInput.value = JSON.stringify(cart);
    btnProses.disabled = false;
    
    calculateKembalian();
}

function calculateKembalian() {
    const jumlahBayar = parseFloat(document.getElementById('jumlah-bayar').value) || 0;
    const totalHarga = cart.reduce((sum, item) => sum + (item.harga * item.qty), 0);
    const kembalian = jumlahBayar - totalHarga;
    
    document.getElementById('kembalian').value = kembalian >= 0 ? 'Rp ' + formatRupiah(kembalian) : 'Rp 0';
}

function clearCart() {
    if (confirm('Yakin ingin mengosongkan keranjang?')) {
        cart = [];
        updateCart();
    }
}

function formatRupiah(angka) {
    return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

document.getElementById('jumlah-bayar').addEventListener('input', calculateKembalian);

document.getElementById('form-pembayaran').addEventListener('submit', function(e) {
    const jumlahBayar = parseFloat(document.getElementById('jumlah-bayar').value) || 0;
    const totalHarga = cart.reduce((sum, item) => sum + (item.harga * item.qty), 0);
    
    if (jumlahBayar < totalHarga) {
        e.preventDefault();
        alert('Jumlah bayar tidak mencukupi!');
        return false;
    }
    
    if (cart.length === 0) {
        e.preventDefault();
        alert('Keranjang masih kosong!');
        return false;
    }
});
</script>

<?php include '../partials/footer.php'; ?>