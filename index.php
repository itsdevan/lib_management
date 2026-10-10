<?php
require_once "config/auth.php";
$page_title = "Home";
include "includes/header.php";
?>
<section class="landing">
    <div>
        <div class="eyebrow">A DIGITAL LIBRARY WITH A WIZARDING SOUL</div>
        <h1 class="hero-title">Welcome to the<br>Arcane Archive.</h1>
        <p class="hero-copy">
            A simple PHP and MySQL library system dressed like an old magical academy:
            students request books while administrators approve loans, manage the collection and complete returns.
        </p>
        <div class="hero-actions">
            <a class="btn btn-primary" href="<?= isset($_SESSION['user_id']) ? (($_SESSION['role'] === 'admin') ? 'admin.php' : 'dashboard.php') : 'login.php' ?>">
                Enter the Archive
            </a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a class="btn btn-secondary" href="register.php">Create Student Account</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="portal">
        <div class="eyebrow">THE RULES OF THE ARCHIVE</div>
        <h2>A small project, with clear roles.</h2>
        <a class="portal-link" href="user_login.php"><strong>Students</strong><span>Search the collection, request up to two books and inspect request/loan history.</span></a>
        <a class="portal-link" href="admin_login.php"><strong>Administrators</strong><span>Approve or decline requests, add/edit/remove books and complete returns.</span></a>
        <a class="portal-link" href="login.php"><strong>Separate entrances</strong><span>Student and administrator authentication pages are intentionally separated.</span></a>
    </div>
</section>
<?php include "includes/footer.php"; ?>
