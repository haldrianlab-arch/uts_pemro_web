<?php
// index.php -> tampil data ke tabel utama
include "cek_login.php";
include "koneksi.php";

$result = $conn->query("SELECT * FROM mahasiswa ORDER BY nim");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Data Mahasiswa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <p>Halo, <?php echo $_SESSION["username"]; ?> | <a href="logout.php">Logout</a></p>

    <h2>Data Mahasiswa</h2>
    <a href="tambah.php">+ Tambah Data</a>
    <br><br>

    <table>
        <tr>
            <th>No</th>
            <th>NIM</th>
            <th>Nama</th>
            <th>Jenis Kelamin</th>
            <th>Jurusan</th>
            <th>Tanggal Lahir</th>
            <th>Alamat</th>
            <th>Aksi</th>
        </tr>
        <?php
        $no = 1;
        while ($row = $result->fetch_assoc()) {
        ?>
        <tr>
            <td><?php echo $no++; ?></td>
            <td><?php echo $row["nim"]; ?></td>
            <td><?php echo $row["nama"]; ?></td>
            <td><?php echo ($row["jenis_kelamin"] == "L") ? "Laki-laki" : "Perempuan"; ?></td>
            <td><?php echo $row["jurusan"]; ?></td>
            <td><?php echo $row["tgl_lahir"]; ?></td>
            <td><?php echo $row["alamat"]; ?></td>
            <td>
                <a href="edit.php?id=<?php echo $row["id"]; ?>">Edit</a> |
                <a href="hapus.php?id=<?php echo $row["id"]; ?>"
                   onclick="return confirm('Yakin hapus data ini?')">Hapus</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
