<?php
// ไฟล์นี้จะถูก include โดย index.php
// ดังนั้น $conn, is_admin(), htmlspecialchars() จะใช้งานได้โดยตรง

if (!is_admin()) {
    redirect('index.php?action=home&msg=' . urlencode('คุณไม่มีสิทธิ์เข้าถึงหน้านี้!'));
}
echo '<div class="card p-4">';
echo '<h2 class="mb-4"><i class="fas fa-user-shield"></i>แผงผู้ดูแลระบบ: จัดการคำร้อง/ข้อเสนอแนะ</h2>';
$stmt = $conn->prepare("SELECT f.id, u.username, f.subject, f.message, f.status, f.created_at, f.attachment
                            FROM feedback f JOIN users u ON f.user_id = u.id
                            ORDER BY f.created_at DESC");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo '<div class="table-responsive">';
    echo '<table class="table table-striped table-hover">';
    echo '<thead><tr><th>ID</th><th>ผู้ใช้</th><th>หัวข้อ</th><th>ข้อความ</th><th>ไฟล์แนบ</th><th>สถานะ</th><th>วันที่ส่ง</th><th>จัดการ</th></tr></thead>';
    echo '<tbody>';
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['id']) . '</td>';
        echo '<td>' . htmlspecialchars($row['username']) . '</td>';
        echo '<td>' . htmlspecialchars($row['subject']) . '</td>';
        echo '<td>' . nl2br(htmlspecialchars(substr($row['message'], 0, 70))) . (strlen($row['message']) > 70 ? '...' : '') . '</td>';
        echo '<td>';
    if (!empty($row['attachment'])) {
        $file_path = 'uploads/' . htmlspecialchars($row['attachment']);
        $file_ext = strtolower(pathinfo($row['attachment'], PATHINFO_EXTENSION));
        $image_exts = ['jpg', 'jpeg', 'png', 'gif']; // ประเภทไฟล์รูปภาพที่ต้องการแสดง

    if (in_array($file_ext, $image_exts)) {
        // ถ้าเป็นไฟล์รูปภาพ ให้แสดงเป็นแท็ก img
        echo '<a href="' . $file_path . '" target="_blank">';
        echo '<img src="' . $file_path . '" alt="Attachment" style="max-width: 80px; max-height: 80px; border-radius: 4px;">'; // ปรับขนาดตามต้องการ
        echo '</a>';
    } else {
        // ถ้าไม่ใช่รูปภาพ ให้แสดงเป็นปุ่มดาวน์โหลดเหมือนเดิม
        echo '<a href="' . $file_path . '" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fas fa-download"></i> ดาวน์โหลด</a>';
    }
} else {
    echo '-';
}
echo '</td>';
        echo '</td>';
        echo '<td><span class="badge bg-' . ($row['status'] == 'pending' ? 'warning text-dark' : ($row['status'] == 'approved' ? 'success' : 'danger')) . '">' . htmlspecialchars(ucfirst($row['status'])) . '</span></td>';
        echo '<td>' . htmlspecialchars($row['created_at']) . '</td>';
        echo '<td>';
        echo '<form method="POST" action="index.php?action=admin_update_status" class="d-inline-block">';
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
        echo '<input type="hidden" name="feedback_id" value="' . htmlspecialchars($row['id']) . '">';
        echo '<select name="new_status" class="form-select form-select-sm" onchange="this.form.submit()">';
        echo '<option value="pending"' . ($row['status'] == 'pending' ? ' selected' : '') . '>รอดำเนินการ</option>';
        echo '<option value="approved"' . ($row['status'] == 'approved' ? ' selected' : '') . '>อนุมัติ</option>';
        echo '<option value="rejected"' . ($row['status'] == 'rejected' ? ' selected' : '') . '>ปฏิเสธ</option>';
        echo '</select>';
        echo '</form>';
        echo '<a href="index.php?action=view_feedback&id=' . $row['id'] . '" class="btn btn-sm btn-info-outline ms-2"><i class="fas fa-eye"></i> ดู</a>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
} else {
    echo '<div class="alert alert-info" role="alert">ยังไม่มีคำร้อง/ข้อเสนอแนะในระบบ</div>';
}
$stmt->close();
echo '</div>';
?>