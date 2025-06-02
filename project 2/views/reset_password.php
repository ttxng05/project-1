<div class="card p-4">
    <h2><i class="fas fa-key"></i>ตั้งรหัสผ่านใหม่</h2>

    <?php
    $token = $_GET['token'] ?? '';
    $email = $_GET['email'] ?? '';
    $valid_token = false;
    $user_id_to_reset = null;

    if (!empty($token) && !empty($email)) {
        $stmt = $conn->prepare("SELECT user_id FROM password_reset_tokens WHERE token = ? AND expires_at > NOW() LIMIT 1");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $valid_token = true;
            $reset_user = $result->fetch_assoc();
            $user_id_to_reset = $reset_user['user_id'];
        } else {
            $message = 'ลิงก์รีเซ็ตรหัสผ่านไม่ถูกต้องหรือไม่หมดอายุแล้ว';
        }
        $stmt->close();
    } else {
        $message = 'ลิงก์รีเซ็ตรหัสผ่านไม่สมบูรณ์';
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $valid_token && $user_id_to_reset !== null) {
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            $message = 'เกิดข้อผิดพลาดด้านความปลอดภัย (CSRF Token ไม่ถูกต้อง)';
        } else {
            $new_password = $_POST['new_password'];
            $confirm_new_password = $_POST['confirm_new_password'];

            if (empty($new_password) || empty($confirm_new_password)) {
                $message = 'กรุณากรอกรหัสผ่านใหม่ให้ครบถ้วน';
            } elseif ($new_password !== $confirm_new_password) {
                $message = 'รหัสผ่านใหม่ไม่ตรงกัน';
            } elseif (strlen($new_password) < 6) {
                $message = 'รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 6 ตัวอักษร';
            } else {
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

                $stmt_update_pw = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt_update_pw->bind_param("si", $hashed_new_password, $user_id_to_reset);

                if ($stmt_update_pw->execute()) {
                    $stmt_delete_token = $conn->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
                    $stmt_delete_token->bind_param("i", $user_id_to_reset);
                    $stmt_delete_token->execute();
                    $stmt_delete_token->close();

                    $message = 'เปลี่ยนรหัสผ่านสำเร็จแล้ว! คุณสามารถเข้าสู่ระบบได้เลย';
                    redirect('index.php?action=login&msg=' . urlencode($message));
                } else {
                    $message = 'เกิดข้อผิดพลาดในการอัปเดตรหัสผ่าน: ' . $stmt_update_pw->error;
                }
                $stmt_update_pw->close();
            }
        }
    }

    if (!empty($message) && strpos($message, 'สำเร็จ') === false) {
        echo '<div class="alert alert-danger mt-3">' . $message . '</div>';
    } else if (!empty($message)) {
        echo '<div class="alert alert-success mt-3">' . $message . '</div>';
    }

    if ($valid_token):
    ?>
    <form method="POST" action="index.php?action=reset_password&token=<?php echo urlencode($token); ?>&email=<?php echo urlencode($email); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="mb-3">
            <label for="new_password" class="form-label">รหัสผ่านใหม่:</label>
            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
            <div class="form-text">รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร</div>
        </div>
        <div class="mb-3">
            <label for="confirm_new_password" class="form-label">ยืนยันรหัสผ่านใหม่:</label>
            <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" required minlength="6">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i>ตั้งรหัสผ่านใหม่</button>
    </form>
    <?php endif; ?>
</div>