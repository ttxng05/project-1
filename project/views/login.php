<div class="card p-4">
    <h2><i class="fas fa-sign-in-alt"></i>เข้าสู่ระบบ</h2>
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            $message = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
        } else {
            $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $db_username, $db_password_hash, $db_role);
                $stmt->fetch();

                if (password_verify($password, $db_password_hash)) {
                    $_SESSION['user_id'] = $id;
                    $_SESSION['username'] = $db_username;
                    $_SESSION['role'] = $db_role;
                    $message = 'เข้าสู่ระบบสำเร็จ!';
                    redirect('index.php?action=home&msg=' . urlencode($message));
                } else {
                    $message = 'รหัสผ่านไม่ถูกต้อง';
                }
            } else {
                $message = 'ไม่พบชื่อผู้ใช้';
            }
            $stmt->close();
        }
    }
    if (!empty($message) && $_SERVER['REQUEST_METHOD'] == 'POST' && strpos($message, 'สำเร็จ') === false) {
        echo '<div class="alert alert-danger mt-3">' . $message . '</div>';
    }
    ?>
    <form method="POST" action="index.php?action=login">
        <div class="mb-3">
            <label for="username" class="form-label">ชื่อผู้ใช้:</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">รหัสผ่าน:</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i>เข้าสู่ระบบ</button>
    </form>
    <p class="mt-3"><a href="index.php?action=forgot_password">ลืมรหัสผ่าน?</a></p>
</div>