<div class="card p-4">
    <h2><i class="fas fa-paper-plane"></i>ส่งคำร้องหรือข้อเสนอแนะ</h2>
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            $message = 'เกิดข้อผิดพลาดด้านความปลอดภัย (CSRF Token ไม่ถูกต้อง)';
        } else {
            $subject = trim($_POST['subject']);
            $message_text = trim($_POST['message']);
            $user_id = $_SESSION['user_id'];
            $uploaded_file_name = null;

            if (!create_upload_directory(UPLOAD_DIR)) {
                $message = 'ไม่สามารถสร้างโฟลเดอร์อัปโหลดได้ กรุณาตรวจสอบสิทธิ์ของโฟลเดอร์';
            } else {
                if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == UPLOAD_ERR_OK) {
                    $file_tmp_name = $_FILES['attachment']['tmp_name'];
                    $file_name = basename($_FILES['attachment']['name']);
                    $file_size = $_FILES['attachment']['size'];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx');
                    if (!in_array($file_ext, $allowed_ext)) {
                        $message = 'ประเภทไฟล์ไม่ได้รับอนุญาต (อนุญาตเฉพาะ JPG, JPEG, PNG, GIF, PDF, DOC, DOCX, XLS, XLSX)';
                    } elseif ($file_size > 5 * 1024 * 1024) { // 5 MB
                        $message = 'ขนาดไฟล์ต้องไม่เกิน 5 MB';
                    } else {
                        $new_file_name = uniqid('file_') . '.' . $file_ext;
                        $target_file = UPLOAD_DIR . $new_file_name;

                        if (move_uploaded_file($file_tmp_name, $target_file)) {
                            $uploaded_file_name = $new_file_name;
                        } else {
                            $message = 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์ (ไม่สามารถย้ายไฟล์ได้)';
                        }
                    }
                }
            }

            if (empty($subject) || empty($message_text)) {
                $message = 'กรุณากรอกหัวข้อและข้อความคำร้อง/ข้อเสนอแนะ';
            }

            if (empty($message)) {
                $stmt = $conn->prepare("INSERT INTO feedback (user_id, subject, message, attachment) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $user_id, $subject, $message_text, $uploaded_file_name);

                if ($stmt->execute()) {
                    $message = 'ส่งคำร้อง/ข้อเสนอแนะสำเร็จ!';
                    redirect('index.php?action=home&msg=' . urlencode($message));
                } else {
                    $message = 'เกิดข้อผิดพลาดในการส่งคำร้อง/ข้อเสนอแนะ: ' . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
    if (!empty($message) && $_SERVER['REQUEST_METHOD'] == 'POST' && strpos($message, 'สำเร็จ') === false) {
         echo '<div class="alert alert-danger mt-3">' . $message . '</div>';
    }
    ?>
    <form method="POST" action="index.php?action=submit_feedback" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="mb-3">
            <label for="subject" class="form-label">หัวข้อ:</label>
            <input type="text" class="form-control" id="subject" name="subject" required>
        </div>
        <div class="mb-3">
            <label for="message" class="form-label">ข้อความ:</label>
            <textarea class="form-control" id="message" name="message" rows="8" required></textarea>
        </div>
        <div class="mb-3">
            <label for="attachment" class="form-label">ไฟล์แนบ (ไม่เกิน 5MB, JPG, PNG, GIF, PDF, DOC, DOCX, XLS, XLSX):</label>
            <input type="file" class="form-control" id="attachment" name="attachment">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i>ส่งคำร้อง/ข้อเสนอแนะ</button>
    </form>
</div>