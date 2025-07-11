<?php
require_once '../../includes/header.php';

$errors = [];
$success = '';

// Fetch kategori and brand list for dropdowns
$kategoriList = getAll("SELECT * FROM kategori ORDER BY kode ASC");
$brandList = getAll("SELECT * FROM brand ORDER BY kode ASC");

// Handle create or update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = sanitize($_POST['kode'] ?? '');
    $nama = sanitize($_POST['nama'] ?? '');
    $hargabeli = (int)($_POST['hargabeli'] ?? 0);
    $hargajual = (int)($_POST['hargajual'] ?? 0);
    $keterangan = sanitize($_POST['keterangan'] ?? '');
    $kategori = sanitize($_POST['kategori'] ?? '');
    $brand = sanitize($_POST['brand'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verifyToken($csrf_token)) {
        $errors[] = 'Token CSRF tidak valid.';
    } elseif (empty($kode) || empty($nama) || $hargabeli <= 0 || $hargajual <= 0 || empty($kategori) || empty($brand)) {
        $errors[] = 'Semua field wajib diisi dengan benar.';
    } else {
        $exists = getOne("SELECT * FROM barang WHERE kode = ?", [$kode]);
        if ($exists) {
            // Update
            query("UPDATE barang SET nama = ?, hargabeli = ?, hargajual = ?, keterangan = ?, kategori = ?, brand = ? WHERE kode = ?", 
                [$nama, $hargabeli, $hargajual, $keterangan, $kategori, $brand, $kode]);
            $success = 'Data barang berhasil diperbarui.';
        } else {
            // Insert
            query("INSERT INTO barang (kode, nama, hargabeli, hargajual, keterangan, kategori, brand, sisa) VALUES (?, ?, ?, ?, ?, ?, ?, 0)", 
                [$kode, $nama, $hargabeli, $hargajual, $keterangan, $kategori, $brand]);
            $success = 'Data barang berhasil ditambahkan.';
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $kode = sanitize($_GET['delete']);
    query("DELETE FROM barang WHERE kode = ?", [$kode]);
    $success = 'Data barang berhasil dihapus.';
}

$csrf_token = generateToken();
$barangList = getAll("SELECT b.*, k.nama as nama_kategori, br.nama as nama_brand FROM barang b JOIN kategori k ON b.kategori = k.kode JOIN brand br ON b.brand = br.kode ORDER BY b.kode ASC");
?>

<section class="content-header">
    <div class="container-fluid">
        <h1>Manajemen Barang</h1>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalForm">Tambah Barang</button>
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

        <table id="tableBarang" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Keterangan</th>
                    <th>Kategori</th>
                    <th>Brand</th>
                    <th>Sisa Stok</th>
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
                        <td><?= htmlspecialchars($barang['keterangan']) ?></td>
                        <td><?= htmlspecialchars($barang['nama_kategori']) ?></td>
                        <td><?= htmlspecialchars($barang['nama_brand']) ?></td>
                        <td><?= htmlspecialchars($barang['sisa']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning btn-edit" 
                                data-kode="<?= htmlspecialchars($barang['kode']) ?>" 
                                data-nama="<?= htmlspecialchars($barang['nama']) ?>" 
                                data-hargabeli="<?= htmlspecialchars($barang['hargabeli']) ?>" 
                                data-hargajual="<?= htmlspecialchars($barang['hargajual']) ?>" 
                                data-keterangan="<?= htmlspecialchars($barang['keterangan']) ?>" 
                                data-kategori="<?= htmlspecialchars($barang['kategori']) ?>" 
                                data-brand="<?= htmlspecialchars($barang['brand']) ?>">Edit</button>
                            <a href="?delete=<?= urlencode($barang['kode']) ?>" class="btn btn-sm btn-danger btn-delete">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</section>

<!-- Modal Form -->
<div class="modal fade" id="modalForm" tabindex="-1" aria-labelledby="modalFormLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" id="formBarang" novalidate>
      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>" />
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalFormLabel">Tambah Barang</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="kode" class="form-label">Kode</label>
            <input type="text" class="form-control" id="kode" name="kode" required />
          </div>
          <div class="mb-3">
            <label for="nama" class="form-label">Nama</label>
            <input type="text" class="form-control" id="nama" name="nama" required />
          </div>
          <div class="mb-3">
            <label for="hargabeli" class="form-label">Harga Beli</label>
            <input type="number" class="form-control" id="hargabeli" name="hargabeli" required min="0" />
          </div>
          <div class="mb-3">
            <label for="hargajual" class="form-label">Harga Jual</label>
            <input type="number" class="form-control" id="hargajual" name="hargajual" required min="0" />
          </div>
          <div class="mb-3">
            <label for="keterangan" class="form-label">Keterangan</label>
            <textarea class="form-control" id="keterangan" name="keterangan" rows="2"></textarea>
          </div>
          <div class="mb-3">
            <label for="kategori" class="form-label">Kategori</label>
            <select class="form-select" id="kategori" name="kategori" required>
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($kategoriList as $kategori): ?>
                    <option value="<?= htmlspecialchars($kategori['kode']) ?>"><?= htmlspecialchars($kategori['nama']) ?></option>
                <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="brand" class="form-label">Brand</label>
            <select class="form-select" id="brand" name="brand" required>
                <option value="">-- Pilih Brand --</option>
                <?php foreach ($brandList as $brand): ?>
                    <option value="<?= htmlspecialchars($brand['kode']) ?>"><?= htmlspecialchars($brand['nama']) ?></option>
                <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    $('#tableBarang').DataTable();

    // Edit button click
    $('.btn-edit').on('click', function() {
        const kode = $(this).data('kode');
        const nama = $(this).data('nama');
        const hargabeli = $(this).data('hargabeli');
        const hargajual = $(this).data('hargajual');
        const keterangan = $(this).data('keterangan');
        const kategori = $(this).data('kategori');
        const brand = $(this).data('brand');
        $('#modalFormLabel').text('Edit Barang');
        $('#kode').val(kode).prop('readonly', true);
        $('#nama').val(nama);
        $('#hargabeli').val(hargabeli);
        $('#hargajual').val(hargajual);
        $('#keterangan').val(keterangan);
        $('#kategori').val(kategori);
        $('#brand').val(brand);
        $('#modalForm').modal('show');
    });

    // When modal hidden, reset form
    $('#modalForm').on('hidden.bs.modal', function () {
        $('#modalFormLabel').text('Tambah Barang');
        $('#kode').val('').prop('readonly', false);
        $('#nama').val('');
        $('#hargabeli').val('');
        $('#hargajual').val('');
        $('#keterangan').val('');
        $('#kategori').val('');
        $('#brand').val('');
    });

    // Confirm delete
    $('.btn-delete').on('click', function(e) {
        e.preventDefault();
        const href = $(this).attr('href');
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = href;
            }
        });
    });
});
</script>
