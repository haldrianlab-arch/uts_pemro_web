<?php
// [SKIP-LOGIN] SELURUH FILE INI tidak perlu ditulis kalau soal tidak minta login.
// logout.php -> hapus semua data session, kembali ke login
session_start();
session_destroy();

header("Location: login.php");
exit;
?>
