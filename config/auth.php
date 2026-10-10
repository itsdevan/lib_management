<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function require_admin()
{
    require_login();

    if (($_SESSION['role'] ?? '') !== 'admin') {
        header("Location: dashboard.php");
        exit;
    }
}

function require_user()
{
    require_login();

    if (($_SESSION['role'] ?? '') !== 'user') {
        header("Location: admin.php");
        exit;
    }
}

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function flash($type, $title, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,
        'title' => $title,
        'message' => $message
    ];
}

function get_flash()
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}
?>
