<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../middleware/csrf.php';
require_once __DIR__ . '/../config/database.php';

function register_gagal(string $pesan, array $old): void
{
    $_SESSION['error'] = $pesan;
    $_SESSION['old']   = $old;
    redirect('auth/register.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('auth/register.php');
}

csrf_verify();

$username = trim((string) ($_POST['username'] ?? ''));
$nimNip   = trim((string) ($_POST['nim_nip'] ?? ''));
$tanggal  = trim((string) ($_POST['tanggal_lahir'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$confirm  = (string) ($_POST['confirm_password'] ?? '');

$old = ['username' => $username, 'nim_nip' => $nimNip, 'tanggal_lahir' => $tanggal];

$errors = [];

if ($username === '' || $nimNip === '' || $tanggal === '' || $password === '' || $confirm === '') {
    $errors[] = 'Semua field harus diisi.';
} else {
    if (!preg_match('/^[A-Za-z0-9_.]{4,30}$/', $username)) {
        $errors[] = 'Username 4-30 karakter: huruf, angka, titik, atau underscore.';
    }
    if (!preg_match('/^[0-9]{8,20}$/', $nimNip)) {
        $errors[] = 'NIM/NIP harus berupa angka 8-20 digit.';
    }
    $tgl = DateTime::createFromFormat('Y-m-d', $tanggal);
    if (!$tgl || $tgl->format('Y-m-d') !== $tanggal) {
        $errors[] = 'Tanggal lahir tidak valid.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    }
    if (strlen($password) > 72) {
        $errors[] = 'Password maksimal 72 karakter.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Konfirmasi password tidak sama.';
    }
}

if ($errors) {
    register_gagal(implode("\n", $errors), $old);
}

try {
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $st = $pdo->prepare('SELECT fn_registrasi_pasien(:nim, :tgl, :username, :hash)');
    $st->execute([
        'nim'      => $nimNip,
        'tgl'      => $tanggal,
        'username' => $username,
        'hash'     => $hash,
    ]);

    $_SESSION['success'] = 'Pendaftaran berhasil. Silakan login.';
    redirect('auth/login.php');
} catch (PDOException $e) {
    $kode   = (string) ($e->errorInfo[0] ?? '');
    $detail = (string) ($e->errorInfo[2] ?? '');

    if ($kode === 'P0001') {
        $pesan = trim((string) preg_replace('/^ERROR:\s+/', '', (string) strtok($detail, "\n")));
        register_gagal($pesan !== '' ? $pesan : 'Data tidak dapat diproses.', $old);
    }

    if ($kode === '23505') {
        register_gagal(
            stripos($detail, 'username') !== false
                ? 'Username sudah dipakai. Pilih username lain.'
                : 'Data sudah terdaftar. Silakan login atau hubungi admin.',
            $old
        );
    }

    error_log('[register] ' . $e->getMessage());
    register_gagal('Terjadi kesalahan pada sistem. Silakan coba kembali.', $old);
}