<?php
// [SKIP-LOGIN] SELURUH FILE INI tidak perlu ditulis kalau soal tidak minta login.
// =====================================================================
// login.php  ->  KISI-KISI (a): login TANPA database, cukup pakai IF
//
// Alur:
//   1. Form dikirim (POST) ke file ini sendiri
//   2. Cocokkan username & password dengan IF
//   3. Benar -> simpan tanda "sudah login" di $_SESSION, pindah ke index.php
//      Salah -> tampilkan pesan error
//
// $_SESSION = "ingatan" server tentang user. Tanpa session, halaman lain
// tidak tahu apakah user sudah login atau belum.
// session_start() WAJIB dipanggil paling atas, sebelum ada output HTML apa pun.
// =====================================================================
session_start();

// Kalau sudah login, tidak perlu login lagi
if (isset($_SESSION["login"])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    if (empty($username) || empty($password)) {
        $error = "Username dan password wajib diisi!";
    } else if ($username == "admin" && $password == "admin123") {
        // LOGIN BERHASIL
        $_SESSION["login"]    = true;
        $_SESSION["username"] = $username;

        header("Location: index.php"); // pindah halaman
        exit;                           // WAJIB: hentikan script setelah header Location
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Login</title>
    <link rel="stylesheet" href="style.css"> <!-- [SKIP-CSS] boleh tidak ditulis kalau tanpa CSS -->
</head>
<body>
    <div class="login-box">
        <h2>Login</h2>

        <?php if ($error != "") { ?>
            <p class="error"><?php echo $error; ?></p>
        <?php } ?>

        <form method="post" action="">
            <table>
                <tr>
                    <td>Username</td>
                    <td>:</td>
                    <td><input type="text" name="username" required></td>
                </tr>
                <tr>
                    <td>Password</td>
                    <td>:</td>
                    <td><input type="password" name="password" required></td>
                </tr>
            </table>
            <button type="submit">Login</button>
        </form>
        <p class="hint">Username: <b>admin</b> &nbsp; Password: <b>admin123</b></p>
    </div>
</body>
</html>
