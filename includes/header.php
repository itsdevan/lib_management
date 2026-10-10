<?php
require_once __DIR__ . "/../config/auth.php";

$page_title = $page_title ?? "The Arcane Archive";
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$flash = get_flash();

$home = !isset($_SESSION['user_id'])
    ? 'index.php'
    : ($is_admin ? 'admin.php' : 'dashboard.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> | The Arcane Archive</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="magic-bg"></div>

<header class="topbar">
    <a class="brand" href="<?= $home ?>">
        <span class="brand-kicker">THE</span>
        <span class="brand-name">ARCANE ARCHIVE</span>
        <span class="brand-rule"></span>
        <span class="brand-subtitle">LIBRARY OF WIZARDING LORE</span>
    </a>

    <nav>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="index.php">Home</a>
            <a href="user_login.php">Student Login</a>
            <a href="admin_login.php">Admin Login</a>
            <a class="nav-cta" href="register.php">Create Account</a>
        <?php elseif ($is_admin): ?>
            <a href="admin.php">Dashboard</a>
            <a href="admin_books.php">Collection</a>
            <a href="admin_loans.php">Borrowing Ledger</a>
            <a href="profile.php">Profile</a>
            <a class="logout-link" href="logout.php">Leave the Castle</a>
        <?php else: ?>
            <a href="dashboard.php">My Chamber</a>
            <a href="books.php">Library</a>
            <a href="history.php">My History</a>
            <a href="profile.php">Profile</a>
            <a class="logout-link" href="logout.php">Leave the Castle</a>
        <?php endif; ?>
    </nav>
</header>

<main class="page">

<?php if ($flash): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    showMagicToast(
        <?= json_encode($flash['type']) ?>,
        <?= json_encode($flash['title']) ?>,
        <?= json_encode($flash['message']) ?>
    );
});
</script>
<?php endif; ?>
