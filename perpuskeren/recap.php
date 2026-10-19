<?php
header('Content-Type: application/json');
include 'koneksi.php';

$totalBooks = 0;
$totalPetugas = 0;
$totalAnggota = 0;
$totalDenda = 0;
$lowStockBooks = [];

$queryBooks = mysqli_query($conn, "SELECT COUNT(*) AS total FROM buku");
if ($queryBooks && mysqli_num_rows($queryBooks) > 0) {
    $rowBooks = mysqli_fetch_assoc($queryBooks);
    $totalBooks = (int) ($rowBooks['total'] ?? 0);
}

$queryPetugas = mysqli_query($conn, "SELECT COUNT(*) AS total FROM petugas");
if ($queryPetugas && mysqli_num_rows($queryPetugas) > 0) {
    $rowPetugas = mysqli_fetch_assoc($queryPetugas);
    $totalPetugas = (int) ($rowPetugas['total'] ?? 0);
}

$queryAnggota = mysqli_query($conn, "SELECT COUNT(*) AS total FROM anggota");
if ($queryAnggota && mysqli_num_rows($queryAnggota) > 0) {
    $rowAnggota = mysqli_fetch_assoc($queryAnggota);
    $totalAnggota = (int) ($rowAnggota['total'] ?? 0);
}

$queryLowStock = mysqli_query($conn, "SELECT judul_buku, stok FROM buku WHERE stok < 5 ORDER BY stok ASC, judul_buku ASC");
if ($queryLowStock) {
    while ($rowLowStock = mysqli_fetch_assoc($queryLowStock)) {
        $lowStockBooks[] = [
            'judul_buku' => $rowLowStock['judul_buku'] ?? 'Judul tidak tersedia',
            'stok' => (int) ($rowLowStock['stok'] ?? 0),
        ];
    }
}

$queryDenda = mysqli_query($conn, "SELECT COALESCE(SUM(denda), 0) AS total FROM peminjaman");
if ($queryDenda && mysqli_num_rows($queryDenda) > 0) {
    $rowDenda = mysqli_fetch_assoc($queryDenda);
    $totalDenda = (int) ($rowDenda['total'] ?? 0);
}

$lastUpdated = date('d M Y, H:i');

echo json_encode([
    'total_books' => $totalBooks,
    'total_petugas' => $totalPetugas,
    'total_anggota' => $totalAnggota,
    'total_denda' => number_format($totalDenda, 0, ',', '.'),
    'low_stock_count' => count($lowStockBooks),
    'low_stock_books' => $lowStockBooks,
    'last_updated' => $lastUpdated,
]);
