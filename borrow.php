<?php
require_once "config/db.php";
require_once "config/auth.php";
require_user();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: books.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$book_id = (int)($_POST['book_id'] ?? 0);
$conn->begin_transaction();

try {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM borrowings WHERE user_id = ? AND status IN ('pending','borrowed') FOR UPDATE");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $active = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    if ($active >= 2) {
        throw new Exception("You already have two active requests or books.");
    }

    $stmt = $conn->prepare("SELECT id, title, available FROM books WHERE id = ? FOR UPDATE");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$book) throw new Exception("Book not found.");
    if ((int)$book['available'] !== 1) throw new Exception("This book is currently unavailable.");

    $stmt = $conn->prepare("SELECT id FROM borrowings WHERE user_id = ? AND book_id = ? AND status IN ('pending','borrowed') LIMIT 1");
    $stmt->bind_param("ii", $user_id, $book_id);
    $stmt->execute();
    $already = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if ($already) throw new Exception("You already have a request or active loan for this book.");

    // A user only requests the book here. The admin must approve it before it becomes borrowed.
    $stmt = $conn->prepare("INSERT INTO borrowings (user_id, book_id, status) VALUES (?, ?, 'pending')");
    $stmt->bind_param("ii", $user_id, $book_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    flash("success", "Request sent", "Your request for “" . $book['title'] . "” was sent to the administrator. It is not borrowed yet.");
} catch (Exception $e) {
    $conn->rollback();
    flash("error", "Request failed", $e->getMessage());
}
header("Location: books.php");
exit;
?>
