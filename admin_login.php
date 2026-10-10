<?php
require_once "config/db.php";
require_once "config/auth.php";

if (isset($_SESSION['user_id'])) {
    header("Location: " . (($_SESSION['role'] ?? '') === 'admin' ? 'admin.php' : 'dashboard.php'));
    exit;
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? "");
    $password = $_POST['password'] ?? "";

    $stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && $user['role'] === 'admin' && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        flash("success", "Welcome to the Archive", "Authentication successful. The admin chamber is now open.");
        header("Location: admin.php");
        exit;
    }

    $error = "Those credentials do not belong to this admin entrance.";
}

$page_title = "Administrator Entrance";
include "includes/header.php";
?>
<div class="form-card">
    <div class="eyebrow">ADMIN ENTRANCE</div>
    <h1>Administrator Entrance</h1>
    <p class="form-subtitle">Restricted chamber. Only administrators may enter.</p>

    <?php if ($error): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        showMagicToast('error', 'Access denied', "The credentials are incorrect or this account is not an admin account.");
    });
    </script>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" maxlength="50" autocomplete="username" required autofocus>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" autocomplete="current-password" required>
        </div>
        <button class="btn btn-primary" type="submit">Enter the Archive</button>
    </form>
    
    <p class="muted" style="text-align:center;margin-top:10px;font-size:12px;"><a href="login.php" style="color:var(--gold-bright);">Return to entrance hall</a></p>
</div>
<?php include "includes/footer.php"; ?>
