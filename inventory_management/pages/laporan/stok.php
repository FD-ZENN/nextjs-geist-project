<?php
require_once '../../includes/header.php';

$barangList = getAll("SELECT b.*, k.nama as nama_kategori, br.nama as nama_brand FROM barang b JOIN kategori k ON b.kategori = k.kode JOIN brand br ON b.brand = br.kode ORDER BY b.kode ASC");
?>

<section class="content-header">
    <div class="container-fluid">
        <h1>Laporan Stok Barang</h1>
    </div>
</section>

<section class="content">
    <div class="container-fluid">

        <table id="tableStok" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Brand</th>
                    <th>Stok Tersedia</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($barangList as $barang): ?>
                    <tr>
                        <td><?= htmlspecialchars($barang['kode']) ?></td>
                        <td><?= htmlspecialchars($barang['nama']) ?></td>
                        <td><?= htmlspecialchars($barang['nama_kategori']) ?></td>
                        <td><?= htmlspecialchars($barang['nama_brand']) ?></td>
                        <td><?= htmlspecialchars($barang['sisa']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</section>

<?php require_once '../../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    $('#tableStok').DataTable({
        dom: 'Bfrtip',
        buttons: ['excel', 'pdf', 'print']
    });
});
</script>
