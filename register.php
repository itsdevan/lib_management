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
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";
    $confirm = $_POST['confirm_password'] ?? "";

    if ($username === "" || $email === "" || $password === "") {
        $error = "Please fill in every field.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must contain at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($exists) {
            $error = "That username or email is already registered.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->bind_param("sss", $username, $email, $hash);
            if ($stmt->execute()) {
                $stmt->close();
                flash("success", "Account created", "Your student account is ready. You may now enter the library.");
                header("Location: user_login.php");
                exit;
            }
            $error = "The account could not be created. Please try again.";
            $stmt->close();
        }
    }
}
$page_title = "Create Student Account";
include "includes/header.php";
?>
<div class="form-card">
    <div class="eyebrow">NEW READER</div>
    <h1>Create Account</h1>
    <p class="form-subtitle">Create a student account. New accounts never receive administrator privileges.</p>

    <?php if ($error): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        showMagicToast('error', 'Account not created', <?= json_encode($error) ?>);
    });
    </script>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group"><label>Username</label><input type="text" name="username" maxlength="50" required></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" maxlength="120" required></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" minlength="6" required></div>
        <div class="form-group"><label>Confirm password</label><input type="password" name="confirm_password" minlength="6" required></div>
        <button class="btn btn-primary" type="submit">Create Student Account</button>
    </form>
    <p class="muted" style="text-align:center;margin-top:18px;font-size:12px;">Already registered? <a href="user_login.php" style="color:var(--gold-bright);font-weight:700;">Student Login</a></p>
</div>
<?php include "includes/footer.php"; ?>
