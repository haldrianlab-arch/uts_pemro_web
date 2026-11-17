<?php
// =====================================================================
// index.php  ->  KISI-KISI (b1): MENAMPILKAN DATA KE TABEL UTAMA (Read)
// Pola: SELECT -> cek num_rows -> while fetch_assoc -> <tr> per baris
// =====================================================================
include "cek_login.php"; // harus login dulu   <-- [SKIP-LOGIN] hapus baris ini kalau tanpa login
include "koneksi.php";

$sql    = "SELECT * FROM mahasiswa ORDER BY nim ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Data Mahasiswa</title>
    <link rel="stylesheet" href="style.css"> <!-- [SKIP-CSS] boleh tidak ditulis kalau tanpa CSS -->
</head>
<body>
    <!-- [SKIP-LOGIN] MULAI: blok header ini hanya untuk versi login.
         WAJIB ikut dihapus kalau cek_login.php tidak dipakai,
         karena $_SESSION["username"] akan error (Undefined variable $_SESSION). -->
    <div class="header">
        <span>Halo, <b><?php echo $_SESSION["username"]; ?></b></span>
        <a href="logout.php" class="btn btn-merah">Logout</a>
    </div>
    <!-- [SKIP-LOGIN] SELESAI -->

    <h1>Data Mahasiswa</h1>
    <a href="tambah.php" class="btn">+ Tambah Data</a>
    <br><br>

    <!-- Tanpa CSS sama sekali? tulis saja <table border="1"> seperti contoh dosen -->
    <table class="data" border="1">
        <tr>
            <th>No</th>
            <th>Foto</th>
            <th>NIM</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Jenis Kelamin</th>
            <th>Jurusan</th>
            <th>Tanggal Lahir</th>
            <th>Alamat</th>
            <th>Aksi</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            $no = 1;
            while ($row = $result->fetch_assoc()) {
        ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td>
                    <?php if (!empty($row["foto"])) { ?>
                        <img src="uploads/<?php echo htmlspecialchars($row["foto"]); ?>" width="50">
                    <?php } else { echo "-"; } ?>
                </td>
                <td><?php echo htmlspecialchars($row["nim"]); ?></td>
                <td><?php echo htmlspecialchars($row["nama"]); ?></td>
                <td><?php echo htmlspecialchars($row["email"]); ?></td>
                <td><?php echo ($row["jenis_kelamin"] == "L") ? "Laki-laki" : "Perempuan"; ?></td>
                <td><?php echo htmlspecialchars($row["jurusan"]); ?></td>
                <td><?php echo date("d-m-Y", strtotime($row["tgl_lahir"])); ?></td>
                <td><?php echo htmlspecialchars($row["alamat"]); ?></td>
                <td>
                    <!-- id dikirim lewat URL (GET) ke halaman edit/hapus -->
                    <a href="edit.php?id=<?php echo $row["id"]; ?>" class="btn btn-kuning">Edit</a>
                    <a href="hapus.php?id=<?php echo $row["id"]; ?>" class="btn btn-merah">Hapus</a>
                </td>
            </tr>
        <?php
            }
        } else {
            echo "<tr><td colspan='10'>Belum ada data</td></tr>";
        }
        $conn->close();
        ?>
    </table>
</body>
</html>
