<?php
/*
    RUN THIS FILE ONCE after importing schema.sql.

    Default admin:
    Username: admin
    Password: admin123

    IMPORTANT:
    Delete setup_admin.php from your project after running it.
*/
require_once "config/db.php";

$username = "admin";
$email = "admin@library.local";
$password = "admin123";

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("
    INSERT INTO users (username, email, password, role)
    VALUES (?, ?, ?, 'admin')
    ON DUPLICATE KEY UPDATE
        email = VALUES(email),
        password = VALUES(password),
        role = 'admin'
");
$stmt->bind_param("sss", $username, $email, $hash);

if ($stmt->execute()) {
    echo "<h2>Admin account created/updated.</h2>";
    echo "<p>Username: <b>admin</b></p>";
    echo "<p>Password: <b>admin123</b></p>";
    echo "<p><strong>Delete setup_admin.php now for security.</strong></p>";
} else {
    echo "Could not create admin: " . htmlspecialchars($conn->error);
}

$stmt->close();
$conn->close();
?>