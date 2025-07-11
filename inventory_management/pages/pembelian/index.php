<?php
require_once '../../includes/header.php';

$errors = [];
$success = '';

// Fetch supplier and barang list for dropdowns
$supplierList = getAll("SELECT * FROM supplier ORDER BY kode ASC");
$barangList = getAll("SELECT * FROM barang ORDER BY kode ASC");

// Auto-generate nota
function generateNota() {
    $date = date('Ymd');
    $lastNota = getOne("SELECT nota FROM beli WHERE nota LIKE ? ORDER BY nota DESC LIMIT 1", ["B$date%"]);
    if ($lastNota) {
        $lastNumber = (int)substr($lastNota['nota'], 9);
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }
    return 'B' . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nota = sanitize($_POST['nota'] ?? '');
    $tglbeli = sanitize($_POST['tglbeli'] ?? '');
    $supplier = sanitize($_POST['supplier'] ?? '');
    $kasir = $_SESSION['username'];
    $keterangan = sanitize($_POST['keterangan'] ?? '');
    $items = $_POST['items'] ?? [];
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verifyToken($csrf_token)) {
        $errors[] = 'Token CSRF tidak valid.';
    } elseif (empty($nota) || empty($tglbeli) || empty($supplier) || empty($items)) {
        $errors[] = 'Semua field wajib diisi.';
    } else {
        $total = 0;
        foreach ($items as $item) {
            $jumlah = (int)$item['jumlah'];
            $harga = (int)$item['harga'];
            $total += $jumlah * $harga;
        }

        // Insert into beli
        query("INSERT INTO beli (nota, tglbeli, total, supplier, kasir, keterangan) VALUES (?, ?, ?, ?, ?, ?)", 
            [$nota, $tglbeli, $total, $supplier, $kasir, $keterangan]);

        // Insert into transaksi_beli and update stok
        foreach ($items as $item) {
            $kode = sanitize($item['kode']);
            $nama = sanitize($item['nama']);
            $harga = (int)$item['harga'];
            $jumlah = (int)$item['jumlah'];
            $hargaakhir = $harga * $jumlah;

            query("INSERT INTO transaksi_beli (nota, kode, nama, harga, jumlah, hargaakhir) VALUES (?, ?, ?, ?, ?, ?)", 
                [$nota, $kode, $nama, $harga, $jumlah, $hargaakhir]);

            // Update stok barang
            query("UPDATE barang SET terbeli = terbeli + ?, sisa = sisa + ? WHERE kode = ?", [$jumlah, $jumlah, $kode]);
        }

        $success = 'Data pembelian berhasil disimpan.';
    }
}

$csrf_token = generateToken();
$nota = generateNota();
?>

<section class="content-header">
    <div class="container-fluid">
        <h1>Transaksi Pembelian</h1>
    </div>
</section>

<section class="content">
    <div class="container-fluid">

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" id="formPembelian" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>" />
            <div class="mb-3">
                <label for="nota" class="form-label">Nota</label>
                <input type="text" class="form-control" id="nota" name="nota" value="<?= $nota ?>" readonly />
            </div>
            <div class="mb-3">
                <label for="tglbeli" class="form-label">Tanggal Pembelian</label>
                <input type="date" class="form-control" id="tglbeli" name="tglbeli" value="<?= date('Y-m-d') ?>" required />
            </div>
            <div class="mb-3">
                <label for="supplier" class="form-label">Supplier</label>
                <select class="form-select" id="supplier" name="supplier" required>
                    <option value="">-- Pilih Supplier --</option>
                    <?php foreach ($supplierList as $supplier): ?>
                        <option value="<?= htmlspecialchars($supplier['kode']) ?>"><?= htmlspecialchars($supplier['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="keterangan" class="form-label">Keterangan</label>
                <textarea class="form-control" id="keterangan" name="keterangan" rows="2"></textarea>
            </div>

            <h5>Daftar Barang</h5>
            <table class="table table-bordered" id="tableItems">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Items will be added here -->
                </tbody>
            </table>
            <button type="button" class="btn btn-secondary mb-3" id="btnAddItem">Tambah Barang</button>

            <button type="submit" class="btn btn-primary">Simpan Pembelian</button>
        </form>

    </div>
</section>

<!-- Modal for selecting barang -->
<div class="modal fade" id="modalSelectBarang" tabindex="-1" aria-labelledby="modalSelectBarangLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalSelectBarangLabel">Pilih Barang</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <table id="tableBarangSelect" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($barangList as $barang): ?>
                    <tr>
                        <td><?= htmlspecialchars($barang['kode']) ?></td>
                        <td><?= htmlspecialchars($barang['nama']) ?></td>
                        <td><?= number_format($barang['hargabeli'], 0, ',', '.') ?></td>
                        <td><?= number_format($barang['hargajual'], 0, ',', '.') ?></td>
                        <td><button class="btn btn-sm btn-primary btn-select" data-kode="<?= htmlspecialchars($barang['kode']) ?>" data-nama="<?= htmlspecialchars($barang['nama']) ?>" data-harga="<?= htmlspecialchars($barang['hargabeli']) ?>">Pilih</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    $('#tableBarangSelect').DataTable();

    $('#btnAddItem').on('click', function() {
        $('#modalSelectBarang').modal('show');
    });

    $('#tableBarangSelect').on('click', '.btn-select', function() {
        const kode = $(this).data('kode');
        const nama = $(this).data('nama');
        const harga = $(this).data('harga');

        // Check if item already added
        if ($('#tableItems tbody tr[data-kode="' + kode + '"]').length > 0) {
            Swal.fire('Barang sudah ditambahkan.');
            return;
        }

        const row = `<tr data-kode="${kode}">
            <td>
                <input type="hidden" name="items[][kode]" value="${kode}" />
                <input type="hidden" name="items[][nama]" value="${nama}" />
                ${nama}
            </td>
            <td>
                <input type="number" name="items[][harga]" value="${harga}" class="form-control" readonly />
            </td>
            <td>
                <input type="number" name="items[][jumlah]" value="1" min="1" class="form-control" required />
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-remove">Hapus</button>
            </td>
        </tr>`;

        $('#tableItems tbody').append(row);
        $('#modalSelectBarang').modal('hide');
    });

    $('#tableItems').on('click', '.btn-remove', function() {
        $(this).closest('tr').remove();
    });
});
</script>
