<?php
require_once '../../includes/header.php';

$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

$query = "SELECT b.nota, b.tglbeli, b.total, s.nama as supplier, u.nama as kasir FROM beli b JOIN supplier s ON b.supplier = s.kode JOIN user u ON b.kasir = u.username WHERE b.tglbeli BETWEEN ? AND ? ORDER BY b.tglbeli DESC";
$pembelianList = getAll($query, [$startDate, $endDate]);
?>

<section class="content-header">
    <div class="container-fluid">
        <h1>Laporan Pembelian</h1>
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

        <table id="tablePembelian" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Nota</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th>Supplier</th>
                    <th>Kasir</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pembelianList as $pembelian): ?>
                    <tr>
                        <td><?= htmlspecialchars($pembelian['nota']) ?></td>
                        <td><?= htmlspecialchars($pembelian['tglbeli']) ?></td>
                        <td><?= number_format($pembelian['total'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($pembelian['supplier']) ?></td>
                        <td><?= htmlspecialchars($pembelian['kasir']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</section>

<?php require_once '../../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    $('#tablePembelian').DataTable({
        dom: 'Bfrtip',
        buttons: ['excel', 'pdf', 'print']
    });
});
</script>
