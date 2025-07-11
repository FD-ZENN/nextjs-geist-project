<?php
require_once '../../includes/header.php';

$errors = [];
$success = '';

// Handle create or update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = sanitize($_POST['kode'] ?? '');
    $tgldaftar = sanitize($_POST['tgldaftar'] ?? '');
    $nama = sanitize($_POST['nama'] ?? '');
    $alamat = sanitize($_POST['alamat'] ?? '');
    $nohp = sanitize($_POST['nohp'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verifyToken($csrf_token)) {
        $errors[] = 'Token CSRF tidak valid.';
    } elseif (empty($kode) || empty($tgldaftar) || empty($nama) || empty($alamat) || empty($nohp)) {
        $errors[] = 'Semua field harus diisi.';
    } else {
        $exists = getOne("SELECT * FROM supplier WHERE kode = ?", [$kode]);
        if ($exists) {
            // Update
            query("UPDATE supplier SET tgldaftar = ?, nama = ?, alamat = ?, nohp = ? WHERE kode = ?", [$tgldaftar, $nama, $alamat, $nohp, $kode]);
            $success = 'Data supplier berhasil diperbarui.';
        } else {
            // Insert
            query("INSERT INTO supplier (kode, tgldaftar, nama, alamat, nohp) VALUES (?, ?, ?, ?, ?)", [$kode, $tgldaftar, $nama, $alamat, $nohp]);
            $success = 'Data supplier berhasil ditambahkan.';
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $kode = sanitize($_GET['delete']);
    query("DELETE FROM supplier WHERE kode = ?", [$kode]);
    $success = 'Data supplier berhasil dihapus.';
}

$csrf_token = generateToken();
$supplierList = getAll("SELECT * FROM supplier ORDER BY kode ASC");
?>

<section class="content-header">
    <div class="container-fluid">
        <h1>Manajemen Supplier</h1>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalForm">Tambah Supplier</button>
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

        <table id="tableSupplier" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Tanggal Daftar</th>
                    <th>Nama</th>
                    <th>Alamat</th>
                    <th>No HP</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($supplierList as $supplier): ?>
                    <tr>
                        <td><?= htmlspecialchars($supplier['kode']) ?></td>
                        <td><?= htmlspecialchars($supplier['tgldaftar']) ?></td>
                        <td><?= htmlspecialchars($supplier['nama']) ?></td>
                        <td><?= htmlspecialchars($supplier['alamat']) ?></td>
                        <td><?= htmlspecialchars($supplier['nohp']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning btn-edit" 
                                data-kode="<?= htmlspecialchars($supplier['kode']) ?>" 
                                data-tgldaftar="<?= htmlspecialchars($supplier['tgldaftar']) ?>" 
                                data-nama="<?= htmlspecialchars($supplier['nama']) ?>" 
                                data-alamat="<?= htmlspecialchars($supplier['alamat']) ?>" 
                                data-nohp="<?= htmlspecialchars($supplier['nohp']) ?>">Edit</button>
                            <a href="?delete=<?= urlencode($supplier['kode']) ?>" class="btn btn-sm btn-danger btn-delete">Hapus</a>
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
    <form method="post" id="formSupplier" novalidate>
      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>" />
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalFormLabel">Tambah Supplier</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="kode" class="form-label">Kode</label>
            <input type="text" class="form-control" id="kode" name="kode" required />
          </div>
          <div class="mb-3">
            <label for="tgldaftar" class="form-label">Tanggal Daftar</label>
            <input type="date" class="form-control" id="tgldaftar" name="tgldaftar" required />
          </div>
          <div class="mb-3">
            <label for="nama" class="form-label">Nama</label>
            <input type="text" class="form-control" id="nama" name="nama" required />
          </div>
          <div class="mb-3">
            <label for="alamat" class="form-label">Alamat</label>
            <textarea class="form-control" id="alamat" name="alamat" rows="2" required></textarea>
          </div>
          <div class="mb-3">
            <label for="nohp" class="form-label">No HP</label>
            <input type="text" class="form-control" id="nohp" name="nohp" required />
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
    $('#tableSupplier').DataTable();

    // Edit button click
    $('.btn-edit').on('click', function() {
        const kode = $(this).data('kode');
        const tgldaftar = $(this).data('tgldaftar');
        const nama = $(this).data('nama');
        const alamat = $(this).data('alamat');
        const nohp = $(this).data('nohp');
        $('#modalFormLabel').text('Edit Supplier');
        $('#kode').val(kode).prop('readonly', true);
        $('#tgldaftar').val(tgldaftar);
        $('#nama').val(nama);
        $('#alamat').val(alamat);
        $('#nohp').val(nohp);
        $('#modalForm').modal('show');
    });

    // When modal hidden, reset form
    $('#modalForm').on('hidden.bs.modal', function () {
        $('#modalFormLabel').text('Tambah Supplier');
        $('#kode').val('').prop('readonly', false);
        $('#tgldaftar').val('');
        $('#nama').val('');
        $('#alamat').val('');
        $('#nohp').val('');
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
