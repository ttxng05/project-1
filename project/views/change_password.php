<div class="card p-4">
    <h2><i class="fas fa-key"></i>เปลี่ยนรหัสผ่าน</h2>
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            $message = 'เกิดข้อผิดพลาดด้านความปลอดภัย (CSRF Token ไม่ถูกต้อง)';
        } else {
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
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $stmt->bind_result($db_password_hash);
                $stmt->fetch();
                $stmt->close();

                if (password_verify($current_password, $db_password_hash)) {
                    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->bind_param("si", $new_hashed_password, $user_id);

                    if ($stmt->execute()) {
                        $message = 'เปลี่ยนรหัสผ่านสำเร็จ! กรุณาเข้าสู่ระบบใหม่ด้วยรหัสผ่านใหม่';
                        redirect('index.php?action=logout&msg=' . urlencode($message));
                    } else {
                        $message = 'เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน: ' . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    generate_csrf_token();
                    $message = 'รหัสผ่านปัจจุบันไม่ถูกต้อง';
                }
            }
        }
    }
    if (!empty($message) && $_SERVER['REQUEST_METHOD'] == 'POST' && strpos($message, 'สำเร็จ') === false) {
         echo '<div class="alert alert-danger mt-3">' . $message . '</div>';
    }
    ?>
    <form method="POST" action="index.php?action=change_password">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
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