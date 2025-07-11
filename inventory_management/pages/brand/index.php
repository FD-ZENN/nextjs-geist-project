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
        $exists = getOne("SELECT * FROM brand WHERE kode = ?", [$kode]);
        if ($exists) {
            // Update
            query("UPDATE brand SET nama = ? WHERE kode = ?", [$nama, $kode]);
            $success = 'Data brand berhasil diperbarui.';
        } else {
            // Insert
            query("INSERT INTO brand (kode, nama) VALUES (?, ?)", [$kode, $nama]);
            $success = 'Data brand berhasil ditambahkan.';
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $kode = sanitize($_GET['delete']);
    query("DELETE FROM brand WHERE kode = ?", [$kode]);
    $success = 'Data brand berhasil dihapus.';
}

$csrf_token = generateToken();
$brandList = getAll("SELECT * FROM brand ORDER BY kode ASC");
?>

<section class="content-header">
    <div class="container-fluid">
        <h1>Manajemen Brand</h1>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalForm">Tambah Brand</button>
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

        <table id="tableBrand" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Brand</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($brandList as $brand): ?>
                    <tr>
                        <td><?= htmlspecialchars($brand['kode']) ?></td>
                        <td><?= htmlspecialchars($brand['nama']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning btn-edit" 
                                data-kode="<?= htmlspecialchars($brand['kode']) ?>" 
                                data-nama="<?= htmlspecialchars($brand['nama']) ?>">Edit</button>
                            <a href="?delete=<?= urlencode($brand['kode']) ?>" class="btn btn-sm btn-danger btn-delete">Hapus</a>
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
    <form method="post" id="formBrand" novalidate>
      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>" />
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalFormLabel">Tambah Brand</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="kode" class="form-label">Kode</label>
            <input type="text" class="form-control" id="kode" name="kode" required />
          </div>
          <div class="mb-3">
            <label for="nama" class="form-label">Nama Brand</label>
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
    $('#tableBrand').DataTable();

    // Edit button click
    $('.btn-edit').on('click', function() {
        const kode = $(this).data('kode');
        const nama = $(this).data('nama');
        $('#modalFormLabel').text('Edit Brand');
        $('#kode').val(kode).prop('readonly', true);
        $('#nama').val(nama);
        $('#modalForm').modal('show');
    });

    // When modal hidden, reset form
    $('#modalForm').on('hidden.bs.modal', function () {
        $('#modalFormLabel').text('Tambah Brand');
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
