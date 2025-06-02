<?php
// ไฟล์นี้จะถูก include โดย index.php
// ดังนั้น $conn, $message, is_logged_in(), is_admin(), redirect(), htmlspecialchars() จะใช้งานได้โดยตรง

if (!is_logged_in()) {
    redirect('index.php?action=login&msg=' . urlencode('กรุณาเข้าสู่ระบบ!'));
}
$feedback_id = $_GET['id'] ?? 0;
if ($feedback_id > 0) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT f.id, u.username, f.subject, f.message, f.status, f.created_at, f.attachment FROM feedback f JOIN users u ON f.user_id = u.id WHERE f.id = ? AND (f.user_id = ? OR ?) LIMIT 1");
    $is_admin_check = is_admin() ? 1 : 0;
    $stmt->bind_param("iii", $feedback_id, $user_id, $is_admin_check);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $feedback = $result->fetch_assoc();
        echo '<div class="card p-4">';
        echo '<h2><i class="fas fa-info-circle"></i>รายละเอียดคำร้อง/ข้อเสนอแนะ #'. htmlspecialchars($feedback['id']) . '</h2>';
        echo '<p><strong>ผู้ส่ง:</strong> ' . htmlspecialchars($feedback['username']) . '</p>';
        echo '<p><strong>หัวข้อ:</strong> ' . htmlspecialchars($feedback['subject']) . '</p>';
        echo '<p><strong>สถานะ:</strong> <span class="badge bg-' . ($feedback['status'] == 'pending' ? 'warning text-dark' : ($feedback['status'] == 'approved' ? 'success' : 'danger')) . '">' . htmlspecialchars(ucfirst($feedback['status'])) . '</span></p>';
        echo '<p><strong>วันที่ส่ง:</strong> ' . htmlspecialchars($feedback['created_at']) . '</p>';
        echo '<h4>ข้อความ:</h4>';
        echo '<div class="alert alert-light border">' . nl2br(htmlspecialchars($feedback['message'])) . '</div>';
        if (!empty($feedback['attachment'])) {
            echo '<p><strong>ไฟล์แนบ:</strong> <a href="uploads/' . htmlspecialchars($feedback['attachment']) . '" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-download"></i> ดาวน์โหลดไฟล์แนบ</a></p>';
        } else {
            echo '<p><strong>ไฟล์แนบ:</strong> -ไม่มี-</p>';
        }
        echo '<a href="javascript:history.back()" class="btn btn-secondary mt-3"><i class="fas fa-arrow-left"></i> ย้อนกลับ</a>';
        echo '</div>';
    } else {
        echo '<div class="alert alert-danger" role="alert">ไม่พบคำร้อง/ข้อเสนอแนะที่คุณต้องการ หรือคุณไม่มีสิทธิ์เข้าถึง</div>';
    }
    $stmt->close();
} else {
    redirect('index.php?action=home&msg=' . urlencode('ไม่พบ ID คำร้อง/ข้อเสนอแนะ!'));
}
?>