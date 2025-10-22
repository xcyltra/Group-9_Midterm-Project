<?php
session_start();
require_once '../src/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kasir.php");
    exit();
}

// Ambil data dari form
$cart_data = json_decode($_POST['cart_data'], true);
$metode_pembayaran = mysqli_real_escape_string($con, $_POST['metode_pembayaran']);
$jumlah_bayar = floatval($_POST['jumlah_bayar']);
$catatan = mysqli_real_escape_string($con, $_POST['catatan']);
$id_kasir = $_SESSION['user_id'];

// Validasi cart
if (empty($cart_data)) {
    $_SESSION['error_message'] = 'Keranjang kosong!';
    header("Location: kasir.php");
    exit();
}

// Hitung total
$total_harga = 0;
foreach ($cart_data as $item) {
    $total_harga += $item['harga'] * $item['qty'];
}

// Validasi jumlah bayar
if ($jumlah_bayar < $total_harga) {
    $_SESSION['error_message'] = 'Jumlah bayar tidak mencukupi!';
    header("Location: kasir.php");
    exit();
}

$kembalian = $jumlah_bayar - $total_harga;

// Generate nomor transaksi
$tanggal = date('Ymd');
$query_last = mysqli_query($con, "SELECT no_transaksi FROM transaksi WHERE DATE(tanggal_transaksi) = CURDATE() ORDER BY id_transaksi DESC LIMIT 1");

if (mysqli_num_rows($query_last) > 0) {
    $last = mysqli_fetch_assoc($query_last)['no_transaksi'];
    $last_num = intval(substr($last, -4));
    $new_num = $last_num + 1;
} else {
    $new_num = 1;
}

$no_transaksi = 'TRX-' . $tanggal . '-' . str_pad($new_num, 4, '0', STR_PAD_LEFT);

// Mulai transaksi database
mysqli_begin_transaction($con);

try {
    // Insert header transaksi
    $query_transaksi = "INSERT INTO transaksi (no_transaksi, id_kasir, tanggal_transaksi, total_harga, jumlah_bayar, kembalian, metode_pembayaran, catatan) 
                        VALUES ('$no_transaksi', $id_kasir, NOW(), $total_harga, $jumlah_bayar, $kembalian, '$metode_pembayaran', '$catatan')";
    
    if (!mysqli_query($con, $query_transaksi)) {
        throw new Exception('Gagal menyimpan transaksi: ' . mysqli_error($con));
    }
    
    $id_transaksi = mysqli_insert_id($con);
    
    // Insert detail transaksi dan update stok
    foreach ($cart_data as $item) {
        $id_produk = intval($item['id']);
        $jumlah = intval($item['qty']);
        $harga_satuan = floatval($item['harga']);
        $subtotal = $harga_satuan * $jumlah;
        
        // Cek stok
        $check_stok = mysqli_query($con, "SELECT stok FROM produk WHERE id_produk = $id_produk");
        $stok_data = mysqli_fetch_assoc($check_stok);
        
        if ($stok_data['stok'] < $jumlah) {
            throw new Exception('Stok produk tidak mencukupi!');
        }
        
        // Insert detail
        $query_detail = "INSERT INTO detail_transaksi (id_transaksi, id_produk, jumlah, harga_satuan, subtotal) 
                        VALUES ($id_transaksi, $id_produk, $jumlah, $harga_satuan, $subtotal)";
        
        if (!mysqli_query($con, $query_detail)) {
            throw new Exception('Gagal menyimpan detail transaksi: ' . mysqli_error($con));
        }
        
        // Update stok
        $query_update_stok = "UPDATE produk SET stok = stok - $jumlah WHERE id_produk = $id_produk";
        
        if (!mysqli_query($con, $query_update_stok)) {
            throw new Exception('Gagal update stok: ' . mysqli_error($con));
        }
    }
    
    // Commit transaksi
    mysqli_commit($con);
    
    // Redirect ke halaman cetak struk
    $_SESSION['success_message'] = 'Transaksi berhasil!';
    header("Location: cetak_struk.php?id=" . $id_transaksi);
    exit();
    
} catch (Exception $e) {
    // Rollback jika ada error
    mysqli_rollback($con);
    $_SESSION['error_message'] = $e->getMessage();
    header("Location: kasir.php");
    exit();
}
?>