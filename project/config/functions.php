<?php
// โหลด PHPMailer library (จำเป็นสำหรับบางฟังก์ชันที่นี่)
// ส่วนนี้จะถูกโหลดมาจาก config.php แล้ว ไม่ต้อง require ซ้ำ
// require_once 'PHPMailer/src/Exception.php';
// require_once 'PHPMailer/src/PHPMailer.php';
// require_once 'PHPMailer/src/SMTP.php';

// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;
// use PHPMailer\PHPMailer\SMTP;

// ฟังก์ชันช่วยเหลือ
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// สร้าง CSRF token
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// ตรวจสอบ CSRF token
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    // ลบ token ออกหลังจากใช้งาน เพื่อป้องกัน Replay Attack
    unset($_SESSION['csrf_token']);
    return true;
}

function redirect($url) {
    if (!headers_sent()) {
        header("Location: " . $url);
        exit();
    } else {
        echo "<script>window.location.href='" . addslashes($url) . "';</script>";
        exit();
    }
}

/**
 * สร้างโฟลเดอร์หากยังไม่มี
 * @param string $path_to_create Path ของโฟลเดอร์ที่ต้องการสร้าง
 * @return bool true ถ้าโฟลเดอร์มีอยู่หรือสร้างสำเร็จ, false ถ้าสร้างไม่ได้
 */
function create_upload_directory($path_to_create) {
    if (!is_dir($path_to_create)) {
        if (!mkdir($path_to_create, 0755, true)) {
            error_log("Failed to create directory: " . $path_to_create);
            return false;
        }
    }
    return true;
}

/**
 * ส่งอีเมล
 * @param string $to_email อีเมลผู้รับ
 * @param string $subject หัวข้ออีเมล
 * @param string $body เนื้อหาอีเมล (HTML)
 * @param string $alt_body เนื้อหาอีเมลสำรอง (Plain text)
 * @return bool true ถ้าส่งสำเร็จ, false ถ้าส่งไม่สำเร็จ
 */
function send_email($to_email, $subject, $body, $alt_body) {
    $mail = new PHPMailer();
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = MAIL_SMTP_AUTH;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_SMTP_SECURE;
        $mail->Port       = MAIL_PORT;

        // Recipients
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($to_email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $alt_body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

?>