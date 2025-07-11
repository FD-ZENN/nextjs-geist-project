<?php
require_once '../../includes/header.php';

$errors = [];
$success = '';

// Fetch barang list for dropdown
$barangList = getAll("SELECT * FROM barang ORDER BY kode ASC");

// Auto-generate nota
function generateNota() {
    $date = date('Ymd');
    $lastNota = getOne("SELECT nota FROM bayar WHERE nota LIKE ? ORDER BY nota DESC LIMIT 1", ["P$date%"]);
    if ($lastNota) {
        $lastNumber = (int)substr($lastNota['nota'], 9);
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }
    return 'P' . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nota = sanitize($_POST['nota'] ?? '');
    $tglbayar = sanitize($_POST['tglbayar'] ?? '');
    $kasir = $_SESSION['username'];
    $items = $_POST['items'] ?? [];
    $bayar = (int)($_POST['bayar'] ?? 0);
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verifyToken($csrf_token)) {
        $errors[] = 'Token CSRF tidak valid.';
    } elseif (empty($nota) || empty($tglbayar) || empty($items)) {
        $errors[] = 'Semua field wajib diisi.';
    } else {
        $total = 0;
        foreach ($items as $item) {
            $jumlah = (int)$item['jumlah'];
            $harga = (int)$item['harga'];
            $total += $jumlah * $harga;
        }

        if ($bayar < $total) {
            $errors[] = 'Jumlah bayar kurang dari total.';
        } else {
            $kembali = $bayar - $total;

            // Insert into bayar
            query("INSERT INTO bayar (nota, tglbayar, bayar, total, kembali, kasir) VALUES (?, ?, ?, ?, ?, ?)", 
                [$nota, $tglbayar, $bayar, $total, $kembali, $kasir]);

            // Insert into transaksi_masuk and update stok
            foreach ($items as $item) {
                $kode = sanitize($item['kode']);
                $nama = sanitize($item['nama']);
                $harga = (int)$item['harga'];
                $hargabeli = (int)$item['hargabeli'];
                $jumlah = (int)$item['jumlah'];
                $hargaakhir = $harga * $jumlah;
                $hargabeliakhir = $hargabeli * $jumlah;

                query("INSERT INTO transaksi_masuk (nota, kode, nama, harga, hargabeli, jumlah, hargaakhir, hargabeliakhir) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", 
                    [$nota, $kode, $nama, $harga, $hargabeli, $jumlah, $hargaakhir, $hargabeliakhir]);

                // Update stok barang
                query("UPDATE barang SET terjual = terjual + ?, sisa = sisa - ? WHERE kode = ?", [$jumlah, $jumlah, $kode]);
            }

            $success = 'Data penjualan berhasil disimpan.';
        }
    }
}

$csrf_token = generateToken();
$nota = generateNota();
?>

<section class="content-header">
    <div class="container-fluid">
        <h1>Transaksi Penjualan</h1>
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

        <form method="post" id="formPenjualan" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>" />
            <div class="mb-3">
                <label for="nota" class="form-label">Nota</label>
                <input type="text" class="form-control" id="nota" name="nota" value="<?= $nota ?>" readonly />
            </div>
            <div class="mb-3">
                <label for="tglbayar" class="form-label">Tanggal Penjualan</label>
                <input type="date" class="form-control" id="tglbayar" name="tglbayar" value="<?= date('Y-m-d') ?>" required />
            </div>

            <h5>Daftar Barang</h5>
            <table class="table table-bordered" id="tableItems">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Harga Jual</th>
                        <th>Harga Beli</th>
                        <th>Jumlah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Items will be added here -->
                </tbody>
            </table>
            <button type="button" class="btn btn-secondary mb-3" id="btnAddItem">Tambah Barang</button>

            <div class="mb-3">
                <label for="bayar" class="form-label">Bayar</label>
                <input type="number" class="form-control" id="bayar" name="bayar" min="0" required />
            </div>

            <button type="submit" class="btn btn-primary">Simpan Penjualan</button>
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
                    <th>Stok</th>
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
                        <td><?= htmlspecialchars($barang['sisa']) ?></td>
                        <td><button class="btn btn-sm btn-primary btn-select" data-kode="<?= htmlspecialchars($barang['kode']) ?>" data-nama="<?= htmlspecialchars($barang['nama']) ?>" data-hargajual="<?= htmlspecialchars($barang['hargajual']) ?>" data-hargabeli="<?= htmlspecialchars($barang['hargabeli']) ?>">Pilih</button></td>
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
        const hargajual = $(this).data('hargajual');
        const hargabeli = $(this).data('hargabeli');

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
                <input type="number" name="items[][harga]" value="${hargajual}" class="form-control" readonly />
            </td>
            <td>
                <input type="number" name="items[][hargabeli]" value="${hargabeli}" class="form-control" readonly />
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
