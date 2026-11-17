<?php
// logout.php -> hapus semua data session, kembali ke login
session_start();
session_destroy();

header("Location: login.php");
exit;
?>
