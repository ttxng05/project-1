<?php
// กำหนดค่าคงที่สำหรับการเชื่อมต่อฐานข้อมูล
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // เปลี่ยนเป็น username ฐานข้อมูลของคุณ
define('DB_PASSWORD', '');       // เปลี่ยนเป็น password ฐานข้อมูลของคุณ
define('DB_NAME', 'condb');       // เปลี่ยนเป็นชื่อฐานข้อมูลของคุณ

// สร้างการเชื่อมต่อฐานข้อมูล
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// PATH สำหรับโฟลเดอร์อัปโหลด
define('UPLOAD_DIR', 'uploads/');

?>