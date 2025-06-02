<div class="card p-4">
    <h2><i class="fas fa-key"></i>เปลี่ยนรหัสผ่าน</h2>
    <?php
    // C:\xampp\htdocs\a\project\views\change_password.php

    // โหลดไฟล์ตั้งค่าและฟังก์ชัน
    require_once 'config/db.php';
    require_once 'config/functions.php';

    // ตรวจสอบว่าผู้ใช้เข้าสู่ระบบแล้วหรือไม่ (สำคัญ)
    if (!is_logged_in()) {
        redirect('index.php?action=login&msg=' . urlencode('กรุณาเข้าสู่ระบบก่อนเปลี่ยนรหัสผ่าน!'));
        exit();
    }

    $message = ''; // กำหนดค่าเริ่มต้นของ $message

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            $message = 'เกิดข้อผิดพลาดด้านความปลอดภัย (CSRF Token ไม่ถูกต้อง)';
        } else {
            global $conn; // ต้องมี global $conn; เพื่อให้เข้าถึง $conn ได้
            
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_new_password = $_POST['confirm_new_password'];
            $user_id = $_SESSION['user_id'];

            if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
                $message = 'กรุณากรอกข้อมูลให้ครบถ้วน';
            } elseif ($new_password !== $confirm_new_password) {
                $message = 'รหัสผ่านใหม่ไม่ตรงกัน';
            } elseif (strlen($new_password) < 6) {
                $message = 'รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 6 ตัวอักษร';
            } else {
                $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
                if ($stmt === false) {
                    error_log("Failed to prepare statement for fetching password: " . $conn->error);
                    $message = 'เกิดข้อผิดพลาดภายในระบบ (DB Prepare Error)';
                } else {
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $stmt->bind_result($db_password_hash);
                    $stmt->fetch();
                    $stmt->close();

                    if ($db_password_hash === null || !password_verify($current_password, $db_password_hash)) {
                        // generate_csrf_token(); // ไม่จำเป็นต้องสร้างใหม่ตรงนี้ เพราะหน้าที่ถูกเรียกจาก index.php อยู่แล้ว และ generate token ที่นั่น
                        $message = 'รหัสผ่านปัจจุบันไม่ถูกต้อง';
                    } else {
                        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                        if ($stmt === false) {
                            error_log("Failed to prepare statement for updating password: " . $conn->error);
                            $message = 'เกิดข้อผิดพลาดภายในระบบ (DB Prepare Error)';
                        } else {
                            $stmt->bind_param("si", $new_hashed_password, $user_id);

                            if ($stmt->execute()) {
                                $message = 'เปลี่ยนรหัสผ่านสำเร็จ! กรุณาเข้าสู่ระบบใหม่ด้วยรหัสผ่านใหม่';
                                // หลังจากเปลี่ยนรหัสผ่าน ควรบังคับให้ผู้ใช้ออกจากระบบเพื่อความปลอดภัย
                                redirect('index.php?action=logout&msg=' . urlencode($message));
                                exit();
                            } else {
                                $message = 'เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน: ' . $stmt->error;
                            }
                            $stmt->close();
                        }
                    }
                }
            }
        }
    }
    
    // ดึงข้อความแจ้งเตือนที่อาจถูกส่งมาจากการ Redirect ก่อนหน้านี้ (GET parameter)
    if (isset($_GET['msg'])) {
        $message = urldecode($_GET['msg']);
    }

    // แสดงข้อความแจ้งเตือน
    if (!empty($message)) {
        $alert_class = (strpos($message, 'สำเร็จ') !== false) ? 'alert-success' : 'alert-danger';
        echo '<div class="alert ' . $alert_class . ' mt-3 alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($message);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
    ?>
    <form method="POST" action="index.php?action=change_password">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="mb-3">
            <label for="current_password" class="form-label">รหัสผ่านปัจจุบัน:</label>
            <input type="password" class="form-control" id="current_password" name="current_password" required>
        </div>
        <div class="mb-3">
            <label for="new_password" class="form-label">รหัสผ่านใหม่:</label>
            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
            <div class="form-text">รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร</div>
        </div>
        <div class="mb-3">
            <label for="confirm_new_password" class="form-label">ยืนยันรหัสผ่านใหม่:</label>
            <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" required minlength="6">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i>เปลี่ยนรหัสผ่าน</button>
    </form>
</div>