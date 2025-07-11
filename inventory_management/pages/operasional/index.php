<?php
require_once '../../includes/header.php';

$errors = [];
$success = '';

// Handle create or update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = sanitize($_POST['kode'] ?? '');
    $nama = sanitize($_POST['nama'] ?? '');
    $tanggal = sanitize($_POST['tanggal'] ?? '');
    $biaya = (int)($_POST['biaya'] ?? 0);
    $keterangan = sanitize($_POST['keterangan'] ?? '');
    $kasir = $_SESSION['username'];
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verifyToken($csrf_token)) {
        $errors[] = 'Token CSRF tidak valid.';
    } elseif (empty($kode) || empty($nama) || empty($tanggal) || $biaya <= 0) {
        $errors[] = 'Semua field wajib diisi dengan benar.';
    } else {
        $exists = getOne("SELECT * FROM operasional WHERE kode = ?", [$kode]);
        if ($exists) {
            // Update
            query("UPDATE operasional SET nama = ?, tanggal = ?, biaya = ?, keterangan = ?, kasir = ? WHERE kode = ?", 
                [$nama, $tanggal, $biaya, $keterangan, $kasir, $kode]);
            $success = 'Data operasional berhasil diperbarui.';
        } else {
            // Insert
            query("INSERT INTO operasional (kode, nama, tanggal, biaya, keterangan, kasir) VALUES (?, ?, ?, ?, ?, ?)", 
                [$kode, $nama, $tanggal, $biaya, $keterangan, $kasir]);
            $success = 'Data operasional berhasil ditambahkan.';
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $kode = sanitize($_GET['delete']);
    query("DELETE FROM operasional WHERE kode = ?", [$kode]);
    $success = 'Data operasional berhasil dihapus.';
}

$csrf_token = generateToken();
$operasionalList = getAll("SELECT * FROM operasional ORDER BY tanggal DESC");
?>

<section class="content-header">
    <div class="container-fluid">
        <h1>Manajemen Operasional</h1>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalForm">Tambah Operasional</button>
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

        <table id="tableOperasional" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Tanggal</th>
                    <th>Biaya</th>
                    <th>Keterangan</th>
                    <th>Kasir</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($operasionalList as $op): ?>
                    <tr>
                        <td><?= htmlspecialchars($op['kode']) ?></td>
                        <td><?= htmlspecialchars($op['nama']) ?></td>
                        <td><?= htmlspecialchars($op['tanggal']) ?></td>
                        <td><?= number_format($op['biaya'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($op['keterangan']) ?></td>
                        <td><?= htmlspecialchars($op['kasir']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning btn-edit" 
                                data-kode="<?= htmlspecialchars($op['kode']) ?>" 
                                data-nama="<?= htmlspecialchars($op['nama']) ?>" 
                                data-tanggal="<?= htmlspecialchars($op['tanggal']) ?>" 
                                data-biaya="<?= htmlspecialchars($op['biaya']) ?>" 
                                data-keterangan="<?= htmlspecialchars($op['keterangan']) ?>">Edit</button>
                            <a href="?delete=<?= urlencode($op['kode']) ?>" class="btn btn-sm btn-danger btn-delete">Hapus</a>
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
    <form method="post" id="formOperasional" novalidate>
      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>" />
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalFormLabel">Tambah Operasional</h5>
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
            <label for="tanggal" class="form-label">Tanggal</label>
            <input type="date" class="form-control" id="tanggal" name="tanggal" required />
          </div>
          <div class="mb-3">
            <label for="biaya" class="form-label">Biaya</label>
            <input type="number" class="form-control" id="biaya" name="biaya" required min="0" />
          </div>
          <div class="mb-3">
            <label for="keterangan" class="form-label">Keterangan</label>
            <textarea class="form-control" id="keterangan" name="keterangan" rows="2"></textarea>
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
    $('#tableOperasional').DataTable();

    // Edit button click
    $('.btn-edit').on('click', function() {
        const kode = $(this).data('kode');
        const nama = $(this).data('nama');
        const tanggal = $(this).data('tanggal');
        const biaya = $(this).data('biaya');
        const keterangan = $(this).data('keterangan');
        $('#modalFormLabel').text('Edit Operasional');
        $('#kode').val(kode).prop('readonly', true);
        $('#nama').val(nama);
        $('#tanggal').val(tanggal);
        $('#biaya').val(biaya);
        $('#keterangan').val(keterangan);
        $('#modalForm').modal('show');
    });

    // When modal hidden, reset form
    $('#modalForm').on('hidden.bs.modal', function () {
        $('#modalFormLabel').text('Tambah Operasional');
        $('#kode').val('').prop('readonly', false);
        $('#nama').val('');
        $('#tanggal').val('');
        $('#biaya').val('');
        $('#keterangan').val('');
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
