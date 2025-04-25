<?php
$servername = "localhost";
$username = "root";
$password = ""; // với XAMPP mặc định là chuỗi rỗng
$dbname = "combini_pos";

// Tạo kết nối
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if (!$conn) {
    die("接続失敗: " . mysqli_connect_error());
}

// Thiết lập charset
mysqli_set_charset($conn, "utf8");
?>

