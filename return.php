<?php
require_once "config/db.php";
require_once "config/auth.php";
require_user();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$borrowing_id = (int)($_POST['borrowing_id'] ?? 0);
$conn->begin_transaction();

try {
    $stmt = $conn->prepare("SELECT book_id, b.title FROM borrowings br JOIN books b ON b.id = br.book_id WHERE br.id = ? AND br.user_id = ? AND br.status = 'borrowed' FOR UPDATE");
    $stmt->bind_param("ii", $borrowing_id, $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) throw new Exception("Borrowing record not found.");

    $stmt = $conn->prepare("UPDATE borrowings SET returned_at = NOW(), status = 'returned' WHERE id = ?");
    $stmt->bind_param("i", $borrowing_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE books SET available = 1 WHERE id = ?");
    $stmt->bind_param("i", $row['book_id']);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    flash("success", "Book returned", "“" . $row['title'] . "” has been returned to the Archive.");
} catch (Exception $e) {
    $conn->rollback();
    flash("error", "Return failed", $e->getMessage());
}
header("Location: dashboard.php");
exit;
?>
