<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once __DIR__ . '/functions.php';

$hakAkses = getHakAkses($_SESSION['jabatan']);

function menuItem($label, $icon, $link, $show) {
    if (!$show) return '';
    return '<li class="nav-item">
        <a href="' . $link . '" class="nav-link">
            <i class="nav-icon fas fa-' . $icon . '"></i>
            <p>' . $label . '</p>
        </a>
    </li>';
}
?>

<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

        <?= menuItem('Dashboard', 'tachometer-alt', 'index.php', true) ?>

        <?= menuItem('Admin', 'user-shield', 'pages/jabatan/index.php', $hakAkses['menuadmin'] == '1') ?>
        <?= menuItem('Supplier', 'truck', 'pages/supplier/index.php', $hakAkses['menusupplier'] == '1') ?>
        <?= menuItem('Kategori', 'tags', 'pages/kategori/index.php', $hakAkses['menukategori'] == '1') ?>
        <?= menuItem('Brand', 'bookmark', 'pages/brand/index.php', $hakAkses['menubarang'] == '1') ?>
        <?= menuItem('Barang', 'boxes', 'pages/barang/index.php', $hakAkses['menubarang'] == '1') ?>
        <?= menuItem('Pembelian', 'shopping-cart', 'pages/pembelian/index.php', $hakAkses['menupembelian'] == '1') ?>
        <?= menuItem('Penjualan', 'cash-register', 'pages/penjualan/index.php', $hakAkses['menupenjualan'] == '1') ?>
        <?= menuItem('Operasional', 'file-invoice-dollar', 'pages/operasional/index.php', $hakAkses['menuoperasional'] == '1') ?>
        <?= menuItem('Stok', 'warehouse', 'pages/stok/index.php', $hakAkses['menustok'] == '1') ?>
        <?= menuItem('Laporan', 'chart-bar', 'pages/laporan/index.php', $hakAkses['menulaporan'] == '1') ?>
        <?= menuItem('Pengaturan', 'cogs', 'pages/pengaturan/index.php', $hakAkses['menupengaturan'] == '1') ?>

    </ul>
</nav>
