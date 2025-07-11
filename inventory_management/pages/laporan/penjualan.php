<?php
require_once '../../includes/header.php';

$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

$query = "SELECT b.nota, b.tglbayar, b.bayar, b.total, b.kembali, u.nama as kasir FROM bayar b JOIN user u ON b.kasir = u.username WHERE b.tglbayar BETWEEN ? AND ? ORDER BY b.tglbayar DESC";
$penjualanList = getAll($query, [$startDate, $endDate]);
?>

<section class="content-header">
    <div class="container-fluid">
        <h1>Laporan Penjualan</h1>
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

        <table id="tablePenjualan" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Nota</th>
                    <th>Tanggal</th>
                    <th>Bayar</th>
                    <th>Total</th>
                    <th>Kembali</th>
                    <th>Kasir</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($penjualanList as $penjualan): ?>
                    <tr>
                        <td><?= htmlspecialchars($penjualan['nota']) ?></td>
                        <td><?= htmlspecialchars($penjualan['tglbayar']) ?></td>
                        <td><?= number_format($penjualan['bayar'], 0, ',', '.') ?></td>
                        <td><?= number_format($penjualan['total'], 0, ',', '.') ?></td>
                        <td><?= number_format($penjualan['kembali'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($penjualan['kasir']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</section>

<?php require_once '../../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    $('#tablePenjualan').DataTable({
        dom: 'Bfrtip',
        buttons: ['excel', 'pdf', 'print']
    });
});
</script>
