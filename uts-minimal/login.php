<?php
// login.php -> login TANPA database, cukup pakai IF
session_start(); // paling atas, sebelum HTML

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    if ($username == "admin" && $password == "admin123") {
        $_SESSION["login"]    = true;
        $_SESSION["username"] = $username;
        header("Location: index.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Login</h2>
    <p style="color: red;"><?php echo $error; ?></p>

    <form method="post" action="">
        <table>
            <tr>
                <td>Username</td>
                <td><input type="text" name="username" required></td>
            </tr>
            <tr>
                <td>Password</td>
                <td><input type="password" name="password" required></td>
            </tr>
        </table>
        <br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
