<?php
require_once "config/db.php";
require_once "config/auth.php";
require_admin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admin_books.php");
    exit;
}

$id = (int)($_POST['id'] ?? 0);

$stmt = $conn->prepare("SELECT title, available FROM books WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$book) {
    flash("error", "Removal failed", "That volume could not be found.");
    header("Location: admin_books.php");
    exit;
}

if ((int)$book['available'] !== 1) {
    flash("error", "Removal blocked", "A borrowed book cannot be removed until it is returned.");
    header("Location: admin_books.php");
    exit;
}

$stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    flash("success", "Book removed", "“" . $book['title'] . "” has been removed from the collection.");
} else {
    flash("error", "Removal failed", "The volume could not be removed.");
}
$stmt->close();

header("Location: admin_books.php");
exit;
?>
