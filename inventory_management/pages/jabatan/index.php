<?php
require_once '../../includes/header.php';

$errors = [];
$success = '';

// Handle create or update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = sanitize($_POST['kode'] ?? '');
    $nama = sanitize($_POST['nama'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verifyToken($csrf_token)) {
        $errors[] = 'Token CSRF tidak valid.';
    } elseif (empty($kode) || empty($nama)) {
        $errors[] = 'Kode dan Nama harus diisi.';
    } else {
        // Check if updating or inserting
        $exists = getOne("SELECT * FROM jabatan WHERE kode = ?", [$kode]);
        if ($exists) {
            // Update
            $stmt = query("UPDATE jabatan SET nama = ? WHERE kode = ?", [$nama, $kode]);
            $success = 'Data jabatan berhasil diperbarui.';
        } else {
            // Insert
            $stmt = query("INSERT INTO jabatan (kode, nama) VALUES (?, ?)", [$kode, $nama]);
            $success = 'Data jabatan berhasil ditambahkan.';
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $kode = sanitize($_GET['delete']);
    query("DELETE FROM jabatan WHERE kode = ?", [$kode]);
    $success = 'Data jabatan berhasil dihapus.';
}

$csrf_token = generateToken();
$jabatanList = getAll("SELECT * FROM jabatan ORDER BY kode ASC");
?>

<section class="content-header">
    <div class="container-fluid">
        <h1>Manajemen Jabatan</h1>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalForm">Tambah Jabatan</button>
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

        <table id="tableJabatan" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Jabatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jabatanList as $jabatan): ?>
                    <tr>
                        <td><?= htmlspecialchars($jabatan['kode']) ?></td>
                        <td><?= htmlspecialchars($jabatan['nama']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning btn-edit" data-kode="<?= htmlspecialchars($jabatan['kode']) ?>" data-nama="<?= htmlspecialchars($jabatan['nama']) ?>">Edit</button>
                            <a href="?delete=<?= urlencode($jabatan['kode']) ?>" class="btn btn-sm btn-danger btn-delete">Hapus</a>
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
    <form method="post" id="formJabatan" novalidate>
      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>" />
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalFormLabel">Tambah Jabatan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="kode" class="form-label">Kode</label>
            <input type="text" class="form-control" id="kode" name="kode" required />
          </div>
          <div class="mb-3">
            <label for="nama" class="form-label">Nama Jabatan</label>
            <input type="text" class="form-control" id="nama" name="nama" required />
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
    $('#tableJabatan').DataTable();

    // Edit button click
    $('.btn-edit').on('click', function() {
        const kode = $(this).data('kode');
        const nama = $(this).data('nama');
        $('#modalFormLabel').text('Edit Jabatan');
        $('#kode').val(kode).prop('readonly', true);
        $('#nama').val(nama);
        $('#modalForm').modal('show');
    });

    // When modal hidden, reset form
    $('#modalForm').on('hidden.bs.modal', function () {
        $('#modalFormLabel').text('Tambah Jabatan');
        $('#kode').val('').prop('readonly', false);
        $('#nama').val('');
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
