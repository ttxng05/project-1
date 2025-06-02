<div class="card p-4">
    <h2><i class="fas fa-paper-plane"></i>ส่งคำร้องหรือข้อเสนอแนะ</h2>
<?php
    // โหลดไฟล์ตั้งค่าและฟังก์ชัน
    require_once 'config/db.php';
    require_once 'config/functions.php';

    $message = ''; // กำหนดค่าเริ่มต้นของ $message ก่อนการประมวลผลฟอร์ม

    // ตรวจสอบว่าผู้ใช้เข้าสู่ระบบแล้วหรือไม่ (สำคัญ)
    if (!is_logged_in()) {
        redirect('index.php?action=login&msg=' . urlencode('กรุณาเข้าสู่ระบบก่อนส่งคำร้อง!'));
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // *** เพิ่มส่วนนี้เพื่อดักจับไฟล์ที่ใหญ่เกินกว่า post_max_size หรือ upload_max_filesize ***
        // UPLOAD_ERR_INI_SIZE: ค่าที่ส่งผ่าน php.ini (upload_max_filesize)
        // UPLOAD_ERR_FORM_SIZE: ค่าที่ส่งผ่าน MAX_FILE_SIZE ในฟอร์ม HTML (ถ้ามี)

        // ตรวจสอบว่ามีไฟล์ถูกส่งมาหรือไม่ และมี Error ระดับ INI_SIZE หรือ FORM_SIZE
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_INI_SIZE) {
            $message = 'ขนาดไฟล์ที่อัปโหลดมีขนาดใหญ่เกินกว่าที่เซิร์ฟเวอร์กำหนด (สูงสุด ' . ini_get('upload_max_filesize') . ')';
        } 
        // หรือถ้าใช้ MAX_FILE_SIZE ในฟอร์ม HTML
        else if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_FORM_SIZE) {
            $message = 'ขนาดไฟล์ที่อัปโหลดมีขนาดใหญ่เกินกว่าที่กำหนดในฟอร์ม';
        }
        // ตรวจสอบกรณีที่ไม่มีไฟล์ถูกส่งมาเลย หรือไฟล์ใหญ่เกิน post_max_size (PHP ไม่สามารถรับข้อมูล POST ทั้งหมดได้)
        // $_FILES อาจว่างเปล่าถ้าขนาดรวมของ POST เกิน post_max_size
        else if (empty($_FILES) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
            // นี่คือเคสที่ POST Content-Length เกิน post_max_size
            $message = 'ขนาดรวมของข้อมูลที่ส่งมามีขนาดใหญ่เกินกว่าที่เซิร์ฟเวอร์กำหนด (สูงสุด ' . ini_get('post_max_size') . ')';
        }
        // *** สิ้นสุดส่วนการดักจับไฟล์ใหญ่เกิน ***

        // ตรวจสอบ CSRF Token เป็นอันดับแรก (ถ้าไม่มี Error เรื่องไฟล์ใหญ่เกิน)
        if (empty($message)) { // ถ้ายังไม่มี message (หมายถึงไม่มี error เรื่องไฟล์ใหญ่เกิน)
            if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                $message = 'เกิดข้อผิดพลาดด้านความปลอดภัย (CSRF Token ไม่ถูกต้อง)';
            } else {
                global $conn; // เข้าถึงการเชื่อมต่อฐานข้อมูล

                $subject = trim($_POST['subject']);
                $message_text = trim($_POST['message']); // เปลี่ยนชื่อตัวแปรเพื่อไม่ให้ชนกับ $message แจ้งเตือน
                $user_id = $_SESSION['user_id'];
                $uploaded_file_name = null;

                // ตรวจสอบข้อมูลเบื้องต้น (ถ้าไม่มี error ก่อนหน้านี้)
                if (empty($subject) || empty($message_text)) {
                    $message = 'กรุณากรอกข้อมูลให้ครบถ้วน (หัวข้อ, ข้อความ)';
                }

                // ถ้าไม่มี error ก่อนหน้านี้ ให้ดำเนินการต่อกับไฟล์
                if (empty($message)) {
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
                            } elseif ($file_size > 5 * 1024 * 1024) { // 5 MB (นี่คือข้อจำกัดที่คุณกำหนดเอง)
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
                }

                // หากไม่มีข้อผิดพลาดในการตรวจสอบไฟล์และข้อมูลเบื้องต้น
                if (empty($message)) {
                    $stmt = $conn->prepare("INSERT INTO feedback (user_id, subject, message, attachment) VALUES (?, ?, ?, ?)");
                    if ($stmt === false) {
                        error_log("Failed to prepare statement for feedback insertion: " . $conn->error);
                        $message = 'เกิดข้อผิดพลาดภายในระบบ (DB Prepare Error)';
                    } else {
                        $stmt->bind_param("isss", $user_id, $subject, $message_text, $uploaded_file_name);

                        if ($stmt->execute()) {
                            $message = 'ส่งคำร้อง/ข้อเสนอแนะสำเร็จ!';
                            redirect('index.php?action=home&msg=' . urlencode($message));
                            exit();
                        } else {
                            $message = 'เกิดข้อผิดพลาดในการส่งคำร้อง/ข้อเสนอแนะ: ' . $stmt->error;
                        }
                        $stmt->close();
                    }
                }
            }
        }
    }

    // ดึงข้อความแจ้งเตือนที่อาจถูกส่งมาจากการ Redirect ก่อนหน้านี้ (GET parameter)
    if (empty($message) && isset($_GET['msg'])) { // ดึงจาก GET ก็ต่อเมื่อ $message ยังว่างเปล่า
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
<form method="POST" action="index.php?action=submit_feedback" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
    <input type="hidden" name="MAX_FILE_SIZE" value="5242880"> <div class="mb-3">
        <label for="subject" class="form-label">หัวข้อ:</label>
        <input type="text" class="form-control" id="subject" name="subject" required>
    </div>
    <div class="mb-3">
        <label for="message" class="form-label">ข้อความ:</label>
        <textarea class="form-control" id="message" name="message" rows="8" required></textarea>
    </div>
    <div class="mb-3">
        <label for="attachment" class="form-label">ไฟล์แนบ (สูงสุด 5MB, JPG, PNG, GIF, PDF, DOC, DOCX, XLS, XLSX):</label>
        <input type="file" class="form-control" id="attachment" name="attachment">
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i>ส่งคำร้อง/ข้อเสนอแนะ</button>
</form>
</div>