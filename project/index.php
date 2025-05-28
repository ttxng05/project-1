<?php
// **** สำคัญมาก: ตรวจสอบให้แน่ใจว่าไม่มีช่องว่าง, บรรทัดใหม่ หรืออักขระใดๆ ก่อน <?php นี้เลย ****
// **** ไฟล์ควรถูกบันทึกเป็น UTF-8 without BOM ด้วย ****

session_start(); // เริ่มต้น session

// โหลดไฟล์ตั้งค่าและฟังก์ชัน
require_once 'config/db.php';
require_once 'config/functions.php';

// สร้าง CSRF token สำหรับการใช้งานในฟอร์ม (สร้างใหม่ทุกครั้งที่โหลดหน้า)
generate_csrf_token();


// --- การจัดการเส้นทาง (Routing) อย่างง่าย ---
$action = $_GET['action'] ?? 'home'; // กำหนด action เริ่มต้นเป็น 'home'

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่ สำหรับบางหน้า
if (!is_logged_in() && !in_array($action, ['home', 'register', 'login', 'forgot_password', 'reset_password'])) {
    redirect('index.php?action=login&msg=' . urlencode('กรุณาเข้าสู่ระบบก่อนดำเนินการ!'));
}
// สำหรับแอดมินเท่านั้น
if ($action == 'admin' || $action == 'admin_update_status') { // รวม admin_update_status
    if (!is_admin()) {
        redirect('index.php?action=home&msg=' . urlencode('คุณไม่มีสิทธิ์เข้าถึงหน้านี้!'));
    }
}

// ถ้ามีการส่งค่า message มาจาก redirect (เช่นหลังสมัครสมาชิก, เข้าสู่ระบบ, เปลี่ยนรหัสผ่าน, เปลี่ยนสถานะ)
$message = '';
if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

// โหลดโครงสร้าง Layout (Header, Navbar, Footer)
// ส่วนนี้จะรวม <head>, <body> เปิด และ navbar
require_once 'views/layout.php';

// --- ส่วนของการแสดงผลตาม Action ---
echo '<div class="container mt-4">';

// แสดงข้อความแจ้งเตือน (ถ้ามี)
if (!empty($message)): ?>
    <div class="alert <?php echo strpos($message, 'สำเร็จ') !== false ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif;

switch ($action) {
    case 'home':
        require_once 'views/home.php';
        break;
    case 'view_feedback':
        require_once 'views/view_feedback.php';
        break;
    case 'register':
        require_once 'views/register.php';
        break;
    case 'login':
        require_once 'views/login.php';
        break;
    case 'logout':
        session_destroy();
        $logout_message = isset($_GET['msg']) ? $_GET['msg'] : 'ออกจากระบบสำเร็จ!';
        redirect('index.php?action=login&msg=' . urlencode($logout_message));
        break;
    case 'forgot_password':
        require_once 'views/forgot_password.php';
        break;
    case 'reset_password':
        require_once 'views/reset_password.php';
        break;
    case 'submit_feedback':
        require_once 'views/submit_feedback.php';
        break;
    case 'change_password':
        require_once 'views/change_password.php';
        break;
    case 'admin':
        require_once 'views/admin.php';
        break;
    case 'admin_update_status':
        require_once 'views/admin_update_status.php'; // ไฟล์นี้จะมีการ redirect หลังจากประมวลผล
        break;
    default:
        redirect('index.php?action=home');
        break;
}

echo '</div>'; // ปิด div.container

// โหลดโครงสร้าง Layout (Footer)
// ส่วนนี้จะรวม </body> และ </html>
require_once 'includes/footer.php';

$conn->close(); // ปิดการเชื่อมต่อฐานข้อมูล
?>