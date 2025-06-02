<?php
// ไฟล์นี้จะถูกเรียกจาก index.php และมี $conn และ functions.php โหลดมาแล้ว

// ตรวจสอบว่าเป็นการเรียกผ่าน POST method เท่านั้น
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php?action=admin&msg=' . urlencode('การเข้าถึงไม่ถูกต้อง!'));
    exit();
}

// ตรวจสอบ CSRF Token ก่อน (ถ้าคุณได้ใส่ CSRF Token ในฟอร์ม admin)
// หากฟอร์ม Admin Update Status ของคุณไม่มี CSRF Token ให้คอมเมนต์บรรทัดนี้ออกก่อน แต่แนะนำให้เพิ่มเพื่อความปลอดภัย
// if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
//     error_log("CSRF token verification failed for admin_update_status.");
//     redirect('index.php?action=admin&msg=' . urlencode('เกิดข้อผิดพลาดด้านความปลอดภัย (CSRF Token ไม่ถูกต้อง) กรุณาลองใหม่อีกครั้ง'));
//     exit();
// }


// ตรวจสอบสิทธิ์ผู้ดูแลระบบ
if (!is_admin()) {
    redirect('index.php?action=home&msg=' . urlencode('คุณไม่มีสิทธิ์เข้าถึงหน้านี้!'));
    exit();
}

// ดึงข้อมูลจาก POST
$feedback_id = $_POST['feedback_id'] ?? 0;
$new_status = $_POST['new_status'] ?? '';
$message = ''; // กำหนดตัวแปร message ว่างเปล่าเริ่มต้น

global $conn; // เข้าถึงตัวแปรการเชื่อมต่อฐานข้อมูล $conn

// ตรวจสอบความถูกต้องของข้อมูลที่ได้รับ
if (empty($feedback_id) || !filter_var($feedback_id, FILTER_VALIDATE_INT)) {
    $message = 'รหัสคำร้องไม่ถูกต้อง!';
} elseif (empty($new_status) || !in_array($new_status, ['pending', 'approved', 'rejected'])) {
    $message = 'สถานะไม่ถูกต้อง!';
} else {
    // เตรียมคำสั่ง SQL สำหรับอัปเดตสถานะ
    $stmt = $conn->prepare("UPDATE feedback SET status = ? WHERE id = ?");

    // ตรวจสอบว่า prepare statement สำเร็จหรือไม่
    if ($stmt === false) {
        error_log("Prepare statement failed for admin_update_status: " . $conn->error);
        $message = 'เกิดข้อผิดพลาดภายในระบบ (ไม่สามารถเตรียมคำสั่งฐานข้อมูลได้)';
    } else {
        // ผูกค่าพารามิเตอร์
        // 'si' -> s สำหรับ string (new_status), i สำหรับ integer (feedback_id)
        $stmt->bind_param("si", $new_status, $feedback_id);

        // ประมวลผลคำสั่ง SQL
        if ($stmt->execute()) {
            $message = 'อัปเดตสถานะคำร้องสำเร็จ!';
        } else {
            // บันทึกข้อผิดพลาดหาก execute ล้มเหลว
            error_log("Execute statement failed for admin_update_status: " . $stmt->error);
            $message = 'เกิดข้อผิดพลาดในการอัปเดตสถานะ: ' . $stmt->error;
        }
        $stmt->close(); // ปิด statement
    }
}

// Redirect กลับไปยังหน้า Admin Dashboard พร้อมข้อความแจ้งเตือน
redirect('index.php?action=admin&msg=' . urlencode($message));
exit(); // สำคัญ: ต้อง exit() เพื่อให้แน่ใจว่าการ redirect ทำงานถูกต้องและไม่มีโค้ดอื่นรันต่อ
?>