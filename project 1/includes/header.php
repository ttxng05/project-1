<?php
// ไฟล์นี้จะถูก include โดย index.php ที่มีการเริ่มต้น session และเชื่อมต่อ DB ไว้แล้ว
// ดังนั้นไม่จำเป็นต้อง session_start() หรือสร้าง $conn ซ้ำ
$message = ''; // ต้องกำหนดตัวแปรนี้ไว้สำหรับ alert box
if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการคำร้อง/ข้อเสนอแนะ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-image: url('Gemini_Generated_Image_45owp845owp845ow.jpg'); /* */
            background-repeat: repeat;
            background-size: cover;
            background-attachment: fixed;
            background-color: #f8f9fa;
            color: #343a40;
        }
        /* ... (CSS ส่วนอื่นๆ ที่อยู่ใน <style> ของคุณ) ... */
        .navbar-brand {
            font-weight: bold;
        }
        .card {
            margin-bottom: 20px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .feedback-item {
            background-color: rgba(233, 236, 239, 0.9);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border: 1px solid #dee2e6;
        }
        .navbar-dark .navbar-nav .nav-link {
            color: #fff;
            transition: color 0.3s ease;
        }
        .navbar-dark .navbar-nav .nav-link.active,
        .navbar-dark .navbar-nav .nav-link:hover {
            color: #fed8b1;
        }
        .dropdown-menu {
            background-color: #212529;
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .dropdown-item {
            color: #fff;
            padding: 10px 20px;
        }
        .dropdown-item:hover {
            background-color: #343a40;
            color: #fff;
        }
        .dropdown-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .btn .fas, .btn .far, .btn .fab, .nav-link .fas, .navbar-brand .fas, h1 .fas, h2 .fas {
            margin-right: 8px;
        }
        h1, h2, h3, h4, h5, h6 {
            color: #007bff;
            font-weight: 600;
        }
        .alert {
            border-radius: 8px;
        }
        .btn-info-outline {
            color: #007bff;
            border-color: #007bff;
            background-color: transparent;
        }
        .btn-info-outline:hover {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php?action=home"><i class="fas fa-comment-dots"></i>ระบบจัดการคำร้อง/ข้อเสนอแนะ</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($action == 'home') ? 'active' : ''; ?>" href="index.php?action=home"><i class="fas fa-home"></i>หน้าหลัก</a>
                    </li>
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($action == 'submit_feedback') ? 'active' : ''; ?>" href="index.php?action=submit_feedback"><i class="fas fa-paper-plane"></i>ส่งคำร้อง/ข้อเสนอแนะ</a>
                        </li>
                        <?php if (is_admin()): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($action == 'admin') ? 'active' : ''; ?>" href="index.php?action=admin"><i class="fas fa-user-shield"></i>แผงผู้ดูแลระบบ</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav mb-2 mb-lg-0">
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle"></i> ยินดีต้อนรับ, <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="index.php?action=change_password"><i class="fas fa-key"></i>เปลี่ยนรหัสผ่าน</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="index.php?action=logout"><i class="fas fa-sign-out-alt"></i>ออกจากระบบ</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($action == 'login') ? 'active' : ''; ?>" href="index.php?action=login"><i class="fas fa-sign-in-alt"></i>เข้าสู่ระบบ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($action == 'register') ? 'active' : ''; ?>" href="index.php?action=register"><i class="fas fa-user-plus"></i>สมัครสมาชิก</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo strpos($message, 'สำเร็จ') !== false ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>