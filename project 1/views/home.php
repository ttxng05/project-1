<div class="card p-4">
    <h1 class="mb-4"><i class="fas fa-home"></i>ยินดีต้อนรับสู่ระบบจัดการคำร้อง/ข้อเสนอแนะ</h1>
    <p class="lead">คุณสามารถส่งคำร้องหรือข้อเสนอแนะต่างๆ ได้ที่นี่</p>
</div>

<?php if (is_logged_in()): ?>
    <div class="card p-4 mt-4">
        <h2 class="mb-3"><i class="fas fa-clipboard-list"></i>คำร้อง/ข้อเสนอแนะของคุณ</h2>
        <?php
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT id, subject, message, status, created_at, attachment FROM feedback WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo '<div class="table-responsive">';
            echo '<table class="table table-striped table-hover">';
            echo '<thead><tr><th>หัวข้อ</th><th>สถานะ</th><th>ข้อความ</th><th>ไฟล์แนบ</th><th>วันที่ส่ง</th><th>จัดการ</th></tr></thead>';
            echo '<tbody>';
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['subject']) . '</td>';
                echo '<td><span class="badge bg-' . ($row['status'] == 'pending' ? 'warning text-dark' : ($row['status'] == 'approved' ? 'success' : 'danger')) . '">' . htmlspecialchars(ucfirst($row['status'])) . '</span></td>';
                echo '<td>' . nl2br(htmlspecialchars(substr($row['message'], 0, 100))) . (strlen($row['message']) > 100 ? '...' : '') . '</td>';
                echo '<td>';
                if (!empty($row['attachment'])) {
                    $file_path = UPLOAD_DIR . htmlspecialchars($row['attachment']);
                    $file_ext = strtolower(pathinfo($row['attachment'], PATHINFO_EXTENSION));
                    $image_exts = ['jpg', 'jpeg', 'png', 'gif'];
                    if (in_array($file_ext, $image_exts)) {
                        echo '<a href="' . $file_path . '" target="_blank">';
                        echo '<img src="' . $file_path . '" alt="Attachment" class="thumbnail-attachment">';
                        echo '</a>';
                    } else {
                        echo '<a href="' . $file_path . '" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fas fa-download"></i></a>';
                    }
                } else {
                    echo '-';
                }
                echo '</td>';
                echo '<td>' . htmlspecialchars($row['created_at']) . '</td>';
                echo '<td><a href="index.php?action=view_feedback&id=' . $row['id'] . '" class="btn btn-sm btn-info-outline"><i class="fas fa-eye"></i> ดูรายละเอียด</a></td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
        } else {
            echo '<div class="alert alert-info" role="alert">คุณยังไม่มีคำร้องหรือข้อเสนอแนะ</div>';
        }
        $stmt->close();
        ?>
    </div>
<?php endif; ?>