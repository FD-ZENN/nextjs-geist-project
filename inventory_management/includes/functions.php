<?php
// includes/functions.php
session_start();

require_once __DIR__ . '/../config/database.php';

// Fungsi untuk menjalankan query dengan prepared statement
function query($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

// Fungsi untuk mendapatkan satu baris data
function getOne($sql, $params = []) {
    $stmt = query($sql, $params);
    return $stmt->fetch();
}

// Fungsi untuk mendapatkan banyak data
function getAll($sql, $params = []) {
    $stmt = query($sql, $params);
    return $stmt->fetchAll();
}

// Fungsi untuk cek login
function isLoggedIn() {
    return isset($_SESSION['username']);
}

// Fungsi redirect
function redirect($url) {
    header("Location: $url");
    exit;
}

// Fungsi untuk login user
function login($username, $password) {
    $user = getOne("SELECT * FROM user WHERE username = ?", [$username]);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['jabatan'] = $user['jabatan'];
        return true;
    }
    return false;
}

// Fungsi logout
function logout() {
    session_destroy();
}

// Fungsi untuk mendapatkan hak akses user
function getHakAkses($jabatan) {
    return getOne("SELECT * FROM hak_akses WHERE userjabatan = ?", [$jabatan]);
}

// Fungsi untuk sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Fungsi CSRF token
function generateToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
