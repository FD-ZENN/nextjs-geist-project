<?php
require_once '../../includes/header.php';

$errors = [];
$success = '';

// Fetch jabatan list for dropdown
$jabatanList = getAll("SELECT * FROM jabatan ORDER BY kode ASC");

// Handle create or update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $nama = sanitize($_POST['nama'] ?? '');
    $jabatan = sanitize($_POST['jabatan'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verifyToken($csrf_token)) {
        $errors[] = 'Token CSRF tidak valid.';
    } elseif (empty($username) || empty($nama) || empty($jabatan)) {
        $errors[] = 'Username, Nama, dan Jabatan harus diisi.';
    } else {
        $exists = getOne("SELECT * FROM user WHERE username = ?", [$username]);
        if ($exists) {
            // Update user (password optional)
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                query("UPDATE user SET nama = ?, jabatan = ?, password = ? WHERE username = ?", [$nama, $jabatan, $hashedPassword, $username]);
            } else {
                query("UPDATE user SET nama = ?, jabatan = ? WHERE username = ?", [$nama, $jabatan, $username]);
            }
            $success = 'Data user berhasil diperbarui.';
        } else {
            if (empty($password)) {
                $errors[] = 'Password harus diisi untuk user baru.';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                query("INSERT INTO user (username, password, nama, jabatan) VALUES (?, ?, ?, ?)", [$username, $hashedPassword, $nama, $jabatan]);
                $success = 'Data user berhasil ditambahkan.';
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $username = sanitize($_GET['delete']);
    query("DELETE FROM user WHERE username = ?", [$username]);
    $success = 'Data user berhasil dihapus.';
}

$csrf_token = generateToken();
$userList = getAll("SELECT u.*, j.nama as nama_jabatan FROM user u JOIN jabatan j ON u.jabatan = j.kode ORDER BY u.username ASC");
?>

<section class="content-header">
    <div class="container-fluid">
        <h1>Manajemen User</h1>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalForm">Tambah User</button>
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

        <table id="tableUser" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Nama</th>
                    <th>Jabatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($userList as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['nama']) ?></td>
                        <td><?= htmlspecialchars($user['nama_jabatan']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning btn-edit" 
                                data-username="<?= htmlspecialchars($user['username']) ?>" 
                                data-nama="<?= htmlspecialchars($user['nama']) ?>" 
                                data-jabatan="<?= htmlspecialchars($user['jabatan']) ?>">Edit</button>
                            <a href="?delete=<?= urlencode($user['username']) ?>" class="btn btn-sm btn-danger btn-delete">Hapus</a>
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
    <form method="post" id="formUser" novalidate>
      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>" />
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalFormLabel">Tambah User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required />
          </div>
          <div class="mb-3">
            <label for="nama" class="form-label">Nama</label>
            <input type="text" class="form-control" id="nama" name="nama" required />
          </div>
          <div class="mb-3">
            <label for="jabatan" class="form-label">Jabatan</label>
            <select class="form-select" id="jabatan" name="jabatan" required>
                <option value="">-- Pilih Jabatan --</option>
                <?php foreach ($jabatanList as $jabatan): ?>
                    <option value="<?= htmlspecialchars($jabatan['kode']) ?>"><?= htmlspecialchars($jabatan['nama']) ?></option>
                <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password <small>(Kosongkan jika tidak ingin diubah)</small></label>
            <input type="password" class="form-control" id="password" name="password" />
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
    $('#tableUser').DataTable();

    // Edit button click
    $('.btn-edit').on('click', function() {
        const username = $(this).data('username');
        const nama = $(this).data('nama');
        const jabatan = $(this).data('jabatan');
        $('#modalFormLabel').text('Edit User');
        $('#username').val(username).prop('readonly', true);
        $('#nama').val(nama);
        $('#jabatan').val(jabatan);
        $('#password').val('');
        $('#modalForm').modal('show');
    });

    // When modal hidden, reset form
    $('#modalForm').on('hidden.bs.modal', function () {
        $('#modalFormLabel').text('Tambah User');
        $('#username').val('').prop('readonly', false);
        $('#nama').val('');
        $('#jabatan').val('');
        $('#password').val('');
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
