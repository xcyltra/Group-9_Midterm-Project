<?php
session_start();
require_once '../src/config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: kasir.php");
    exit();
}

$id_transaksi = intval($_GET['id']);

$query = "SELECT t.*, u.nama_lengkap as nama_kasir 
          FROM transaksi t 
          JOIN users u ON t.id_kasir = u.id_user 
          WHERE t.id_transaksi = $id_transaksi";
$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) === 0) {
    $_SESSION['error_message'] = 'Transaksi tidak ditemukan!';
    header("Location: kasir.php");
    exit();
}

$transaksi = mysqli_fetch_assoc($result);

$query_detail = "SELECT dt.*, p.nama_produk, p.satuan 
                 FROM detail_transaksi dt 
                 JOIN produk p ON dt.id_produk = p.id_produk 
                 WHERE dt.id_transaksi = $id_transaksi";
$detail = mysqli_query($con, $query_detail);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi - <?= $transaksi['no_transaksi'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 10px;
            }
            .struk-container {
                width: 80mm;
                margin: 0;
            }
        }
        
        .struk-container {
            width: 80mm;
            margin: 20px auto;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            background: white;
            border: 1px solid #ddd;
        }
        
        .struk-header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .struk-header h4 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }
        
        .struk-info {
            margin-bottom: 10px;
            font-size: 11px;
        }
        
        .struk-table {
            width: 100%;
            margin-bottom: 10px;
        }
        
        .struk-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        
        .struk-footer {
            border-top: 2px dashed #000;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .struk-total {
            font-weight: bold;
            font-size: 14px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4 no-print">
        <div class="text-center mb-3">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Cetak Struk
            </button>
            <a href="kasir.php" class="btn btn-success">
                <i class="bi bi-arrow-left"></i> Transaksi Baru
            </a>
            <a href="riwayat_transaksi.php" class="btn btn-info">
                <i class="bi bi-clock-history"></i> Riwayat
            </a>
        </div>
    </div>

    <div class="struk-container">
        <div class="struk-header">
            <h4>SISTEM KASIRKU</h4>
            <small>Kelompok 9 - Pemrograman Lanjut</small><br>
            <small>Jl. Contoh No. 123, Semarang</small><br>
            <small>Telp: 0812-3456-7890</small>
        </div>

        <div class="struk-info">
            <table style="width: 100%; font-size: 11px;">
                <tr>
                    <td>No. Transaksi</td>
                    <td>: <?= htmlspecialchars($transaksi['no_transaksi']) ?></td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>: <?= date('d/m/Y H:i', strtotime($transaksi['tanggal_transaksi'])) ?></td>
                </tr>
                <tr>
                    <td>Kasir</td>
                    <td>: <?= htmlspecialchars($transaksi['nama_kasir']) ?></td>
                </tr>
            </table>
        </div>

        <div style="border-bottom: 1px dashed #000; margin: 10px 0;"></div>

        <table class="struk-table">
            <?php while ($item = mysqli_fetch_assoc($detail)): ?>
            <tr>
                <td colspan="3"><strong><?= htmlspecialchars($item['nama_produk']) ?></strong></td>
            </tr>
            <tr>
                <td style="width: 40%;">
                    <?= $item['jumlah'] ?> <?= htmlspecialchars($item['satuan']) ?> x Rp <?= number_format($item['harga_satuan'], 0, ',', '.') ?>
                </td>
                <td style="width: 20%;"></td>
                <td style="width: 40%;" class="text-right">
                    Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                </td>
            </tr>
            <tr><td colspan="3" style="height: 5px;"></td></tr>
            <?php endwhile; ?>
        </table>

        <div class="struk-footer">
            <table style="width: 100%;">
                <tr>
                    <td><strong>TOTAL</strong></td>
                    <td class="text-right struk-total">Rp <?= number_format($transaksi['total_harga'], 0, ',', '.') ?></td>
                </tr>
                <tr>
                    <td>Bayar (<?= ucfirst($transaksi['metode_pembayaran']) ?>)</td>
                    <td class="text-right">Rp <?= number_format($transaksi['jumlah_bayar'], 0, ',', '.') ?></td>
                </tr>
                <tr>
                    <td>Kembalian</td>
                    <td class="text-right">Rp <?= number_format($transaksi['kembalian'], 0, ',', '.') ?></td>
                </tr>
            </table>
        </div>

        <?php if (!empty($transaksi['catatan'])): ?>
        <div style="margin-top: 10px; font-size: 10px;">
            <strong>Catatan:</strong><br>
            <?= htmlspecialchars($transaksi['catatan']) ?>
        </div>
        <?php endif; ?>

        <div style="border-top: 1px dashed #000; margin-top: 15px; padding-top: 10px; text-align: center; font-size: 11px;">
            <p style="margin: 5px 0;">Terima kasih atas kunjungan Anda</p>
            <p style="margin: 5px 0;">Barang yang sudah dibeli tidak dapat ditukar</p>
            <p style="margin: 5px 0; font-size: 10px;">Dicetak: <?= date('d/m/Y H:i:s') ?></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>