<?php
require_once '../../includes/header.php';

$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Calculate total penjualan, pembelian, operasional
$totalPenjualan = getOne("SELECT SUM(total) as total FROM bayar WHERE tglbayar BETWEEN ? AND ?", [$startDate, $endDate])['total'] ?? 0;
$totalPembelian = getOne("SELECT SUM(total) as total FROM beli WHERE tglbeli BETWEEN ? AND ?", [$startDate, $endDate])['total'] ?? 0;
$totalOperasional = getOne("SELECT SUM(biaya) as total FROM operasional WHERE tanggal BETWEEN ? AND ?", [$startDate, $endDate])['total'] ?? 0;

$labaRugi = $totalPenjualan - $totalPembelian - $totalOperasional;
?>

<section class="content-header">
    <div class="container-fluid">
        <h1>Laporan Laba Rugi</h1>
        <form method="get" class="row g-3 mb-3">
            <div class="col-auto">
                <label for="start_date" class="col-form-label">Dari Tanggal</label>
            </div>
            <div class="col-auto">
                <input type="date" id="start_date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>" />
            </div>
            <div class="col-auto">
                <label for="end_date" class="col-form-label">Sampai Tanggal</label>
            </div>
            <div class="col-auto">
                <input type="date" id="end_date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>" />
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>
</section>

<section class="content">
    <div class="container-fluid">

        <table class="table table-bordered">
            <tr>
                <th>Total Penjualan</th>
                <td><?= number_format($totalPenjualan, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <th>Total Pembelian</th>
                <td><?= number_format($totalPembelian, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <th>Total Operasional</th>
                <td><?= number_format($totalOperasional, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <th>Laba / Rugi</th>
                <td><?= number_format($labaRugi, 0, ',', '.') ?></td>
            </tr>
        </table>

    </div>
</section>

<?php require_once '../../includes/footer.php'; ?>
