<?php
require_once "config/auth.php";
if (isset($_SESSION['user_id'])) {
    header("Location: " . (($_SESSION['role'] ?? '') === 'admin' ? 'admin.php' : 'dashboard.php'));
    exit;
}
$page_title = "Enter the Archive";
include "includes/header.php";
?>
<section class="landing">
    <div>
        <div class="eyebrow">THE ARCANE ARCHIVE</div>
        <h1 class="hero-title">Enter the<br>Great Library.</h1>
        <p class="hero-copy">
            Choose your path through the castle. Students may explore and borrow books.
            Keepers of the Archive may manage the collection and watch the borrowing ledger.
        </p>
    </div>
    <div class="portal">
        <h2>Choose your entrance</h2>
        <a class="portal-link" href="user_login.php">
            <strong>Student / User Entrance</strong>
            <span>Browse the collection, borrow books, return books and view your history.</span>
        </a>
        <a class="portal-link" href="admin_login.php">
            <strong>Administrator Entrance</strong>
            <span>Manage the collection and inspect who has borrowed each book.</span>
        </a>
        <a class="portal-link" href="register.php">
            <strong>Create a Student Account</strong>
            <span>New readers begin here. Administrator accounts are created separately.</span>
        </a>
    </div>
</section>
<?php include "includes/footer.php"; ?>
