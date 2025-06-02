<div class="card p-4">
    <h2><i class="fas fa-user-plus"></i>สมัครสมาชิก</h2>
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            $email = trim($_POST['email']);

            if (empty($username) || empty($password) || empty($confirm_password) || empty($email)) {
                $message = 'กรุณากรอกข้อมูลให้ครบถ้วน';
            } elseif ($password !== $confirm_password) {
                $message = 'รหัสผ่านไม่ตรงกัน';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = 'รูปแบบอีเมลไม่ถูกต้อง';
            } elseif (strlen($password) < 6) {
                $message = 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร';
            } else {
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->bind_param("ss", $username, $email);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $message = 'ชื่อผู้ใช้หรืออีเมลนี้มีผู้ใช้งานแล้ว';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $default_role = 'user';
                    $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $username, $hashed_password, $email, $default_role);

                    if ($stmt->execute()) {
                        $message = 'สมัครสมาชิกสำเร็จ! คุณสามารถเข้าสู่ระบบได้เลย';
                        redirect('index.php?action=login&msg=' . urlencode($message));
                    } else {
                        $message = 'เกิดข้อผิดพลาดในการสมัครสมาชิก: ' . $stmt->error;
                    }
                }
                $stmt->close();
            }
        }
    if (!empty($message) && $_SERVER['REQUEST_METHOD'] == 'POST' && strpos($message, 'สำเร็จ') === false) {
         echo '<div class="alert alert-danger mt-3">' . $message . '</div>';
    }
    ?>
    <form method="POST" action="index.php?action=register">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="mb-3">
            <label for="username" class="form-label">ชื่อผู้ใช้:</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">อีเมล:</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">รหัสผ่าน:</label>
            <input type="password" class="form-control" id="password" name="password" required minlength="6">
            <div class="form-text">รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร</div>
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน:</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i>สมัครสมาชิก</button>
    </form>
</div>