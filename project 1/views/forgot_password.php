<div class="card p-4">
    <h2><i class="fas fa-question-circle"></i>ลืมรหัสผ่าน</h2>
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            $message = 'เกิดข้อผิดพลาดด้านความปลอดภัย (CSRF Token ไม่ถูกต้อง)';
        } else {
            $email = trim($_POST['email']);

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = 'กรุณากรอกอีเมลที่ถูกต้อง';
            } else {
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows == 1) {
                    $user = $result->fetch_assoc();
                    $user_id = $user['id'];

                    $stmt_delete = $conn->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
                    $stmt_delete->bind_param("i", $user_id);
                    $stmt_delete->execute();
                    $stmt_delete->close();

                    $token = bin2hex(random_bytes(32));
                    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

                    $stmt_insert = $conn->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                    $stmt_insert->bind_param("iss", $user_id, $token, $expires_at);

                    if ($stmt_insert->execute()) {
                        // **** สำคัญ: เปลี่ยน URL นี้ให้เป็น URL จริงของเว็บไซต์คุณเมื่อใช้งานจริง ****
                        $reset_link = "http://localhost/a/index.php?action=reset_password&token=" . urlencode($token) . "&email=" . urlencode($email);

                        $mail_subject = 'รีเซ็ตรหัสผ่านสำหรับระบบจัดการคำร้อง';
                        $mail_body = '
                            <p>คุณได้ร้องขอการรีเซ็ตรหัสผ่านสำหรับบัญชีของคุณบนระบบจัดการคำร้อง/ข้อเสนอแนะ</p>
                            <p>โปรดคลิกลิงก์ด้านล่างนี้เพื่อดำเนินการรีเซ็ตรหัสผ่าน:</p>
                            <p><a href="' . htmlspecialchars($reset_link) . '">' . htmlspecialchars($reset_link) . '</a></p>
                            <p>ลิงก์นี้จะหมดอายุภายใน 1 ชั่วโมง</p>
                            <p>หากคุณไม่ได้ร้องขอการรีเซ็ตรหัสผ่านนี้ โปรดเพิกเฉยอีเมลนี้</p>
                            <p>ขอแสดงความนับถือ,</p>
                            <p>ทีมงานระบบจัดการคำร้อง</p>
                        ';
                        $mail_alt_body = 'คุณได้ร้องขอการรีเซ็ตรหัสผ่าน โปรดคัดลอกลิงก์นี้ไปวางในเบราว์เซอร์ของคุณเพื่อดำเนินการรีเซ็ตรหัสผ่าน: ' . $reset_link . ' ลิงก์นี้จะหมดอายุภายใน 1 ชั่วโมง หากคุณไม่ได้ร้องขอการรีเซ็ตรหัสผ่านนี้ โปรดเพิกเฉยอีเมลนี้';

                        if (send_email($email, $mail_subject, $mail_body, $mail_alt_body)) {
                            $message = 'เราได้ส่งลิงก์สำหรับรีเซ็ตรหัสผ่านไปยังอีเมลของคุณแล้ว (โปรดตรวจสอบ Junk/Spam folder ด้วย)';
                        } else {
                            $message = "ไม่สามารถส่งอีเมลได้ กรุณาลองอีกครั้ง";
                        }
                    } else {
                        $message = 'เกิดข้อผิดพลาดในการสร้างโทเค็นรีเซ็ต: ' . $stmt_insert->error;
                    }
                    $stmt_insert->close();
                } else {
                    $message = 'เราได้ส่งลิงก์สำหรับรีเซ็ตรหัสผ่านไปยังอีเมลของคุณแล้ว (โปรดตรวจสอบ Junk/Spam folder ด้วย)';
                }
                $stmt->close();
            }
        }
    }
    if (!empty($message) && $_SERVER['REQUEST_METHOD'] == 'POST' && strpos($message, 'สำเร็จ') === false) {
         echo '<div class="alert alert-danger mt-3">' . $message . '</div>';
    } else if (!empty($message) && $_SERVER['REQUEST_METHOD'] == 'POST') {
        echo '<div class="alert alert-success mt-3">' . $message . '</div>';
    }
    ?>
    <form method="POST" action="index.php?action=forgot_password">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="mb-3">
            <label for="email" class="form-label">อีเมลที่ลงทะเบียน:</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i>ส่งลิงก์รีเซ็ตรหัสผ่าน</button>
    </form>
</div>