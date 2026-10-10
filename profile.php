<?php
require_once "config/db.php";
require_once "config/auth.php";
require_login();

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $current = $_POST['current_password'] ?? "";
    $new = $_POST['new_password'] ?? "";
    $confirm = $_POST['confirm_password'] ?? "";

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stored = $stmt->get_result()->fetch_assoc()['password'];
    $stmt->close();

    if (!password_verify($current, $stored)) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new) < 6) {
        $error = "New password must contain at least 6 characters.";
    } elseif ($new !== $confirm) {
        $error = "New passwords do not match.";
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hash, $user_id);

        if ($stmt->execute()) {
            $success = "Password changed successfully.";
        } else {
            $error = "Could not change password.";
        }
        $stmt->close();
    }
}

$page_title = "My Profile";
include "includes/header.php";
?>

<div class="profile-grid">
    <div class="profile-card">
        <div class="avatar"><?= e(strtoupper(substr($_SESSION['username'], 0, 1))) ?></div>
        <h2><?= e($_SESSION['username']) ?></h2>
        <p class="muted"><?= e($_SESSION['role']) ?> account</p>

        <div style="margin-top:20px;">
            <div class="info-row">
                <span>Username</span>
                <span><?= e($_SESSION['username']) ?></span>
            </div>
            <div class="info-row">
                <span>Email</span>
                <span><?= e($_SESSION['email']) ?></span>
            </div>
            <div class="info-row">
                <span>Password</span>
                <span>••••••••</span>
            </div>
        </div>

        <p class="muted" style="font-size:12px;margin-top:16px;">
            For security, your real password is never displayed or stored as plain text.
        </p>
    </div>

    <div class="profile-card">
        <h2>Change password</h2>
        <p class="muted" style="margin-bottom:20px;">Update your account password securely.</p>

        <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Current password</label>
                <input type="password" name="current_password" required>
            </div>

            <div class="form-group">
                <label>New password</label>
                <input type="password" name="new_password" minlength="6" required>
            </div>

            <div class="form-group">
                <label>Confirm new password</label>
                <input type="password" name="confirm_password" minlength="6" required>
            </div>

            <button class="btn btn-primary" type="submit">Update Password</button>
        </form>
    </div>
</div>

<?php include "includes/footer.php"; ?>
