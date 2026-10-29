<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    if (!empty($id) && !empty($pass)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $id]);
        $userData = $stmt->fetch();

        if ($userData) {
            // Cek akun terkunci -> jika terkunci langsung exit
            if ($userData['locked_until'] && strtotime($userData['locked_until']) > time()) {
                $_SESSION['error'] = "Akun Anda terkunci hingga " . $userData['locked_until'];
                header("Location: login.php");
                exit;
            }

            // Validasi input password
            if (password_verify($pass, $userData['password_hash']) || $pass === $userData['password_hash']) { // pakai data mockup tanpa di hash dulu soalnya halaman registrasi belum ada. Jadi akun di insert manual lewat psql
                // login berhasil
                $stmtSuccess = $pdo->prepare("SELECT fn_login_berhasil(:user_id)");
                $stmtSuccess->execute(['user_id' => $userData['id']]);

                // Mengamankan session ID
                session_regenerate_id(true);

                // Menyimpan informasi ke session
                $_SESSION['user_id'] = $userData['id'];
                $_SESSION['username'] = $userData['username'];
                $_SESSION['role'] = $userData['role'];

                // Redirect ke dashboard by role
                switch ($_SESSION['role']) {
                    case 'admin':
                        header("Location: ../admin/dashboard.php"); // isi kalo udah ada dashboard untuk setiap role
                        break;
                    case 'dokter':
                        header("Location: dokter/dashboard.php");
                        break;
                    case 'pasien':
                        header("Location: pasien/dashboard.php");
                        break;
                    default:
                        //Role error handler
                        header("Location: index.php");
                        break;
                    }
                exit;
            } else {
                // Login gagal throw error -> call function rn_login_gagal
                $stmtFail = $pdo->prepare("SELECT fn_login_gagal(:user_id)");
                $stmtFail->execute(['user_id' => $userData['id']]);

                $_SESSION['error'] = "Username dan Password tidak valid.";
            }
        } else {
            $_SESSION['error'] = "Username dan Password tidak valid.";
        }
    } else {
        $_SESSION['error'] = "Semua field harus diisi.";
    }

    // Login gagal -> kembali ke login
    header("Location: login.php");
    exit;
} else {
    // Kembali ke login jika method !post
    header("Location: login.php");
    exit;
}
