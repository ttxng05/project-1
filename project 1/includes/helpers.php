<?php

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
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

function create_upload_directory($path_to_create) {
    if (!is_dir($path_to_create)) {
        if (!mkdir($path_to_create, 0755, true)) {
            error_log("Failed to create directory: " . $path_to_create);
            return false;
        }
    }
    return true;
}