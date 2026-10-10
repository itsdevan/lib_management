<?php
require_once "config/db.php";
require_once "config/auth.php";
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_loans.php');
    exit;
}

$borrowing_id = (int)($_POST['borrowing_id'] ?? 0);
$conn->begin_transaction();

try {
    $stmt = $conn->prepare("SELECT br.id, br.book_id, b.title, b.available FROM borrowings br JOIN books b ON b.id = br.book_id WHERE br.id = ? AND br.status = 'pending' FOR UPDATE");
    $stmt->bind_param('i', $borrowing_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) throw new Exception('Borrow request not found or already processed.');
    if ((int)$row['available'] !== 1) throw new Exception('This book is no longer available.');

    $due_date = date('Y-m-d H:i:s', strtotime('+7 days'));
    $stmt = $conn->prepare("UPDATE borrowings SET borrowed_at = NOW(), approved_at = NOW(), due_date = ?, status = 'borrowed' WHERE id = ? AND status = 'pending'");
    $stmt->bind_param('si', $due_date, $borrowing_id);
    $stmt->execute();
    if ($stmt->affected_rows !== 1) throw new Exception('The request could not be approved.');
    $stmt->close();

    $stmt = $conn->prepare("UPDATE books SET available = 0 WHERE id = ? AND available = 1");
    $stmt->bind_param('i', $row['book_id']);
    $stmt->execute();
    if ($stmt->affected_rows !== 1) throw new Exception('The book could not be marked as borrowed.');
    $stmt->close();

    $conn->commit();
    flash('success', 'Request approved', '“' . $row['title'] . '” is now officially borrowed.');
} catch (Exception $e) {
    $conn->rollback();
    flash('error', 'Approval failed', $e->getMessage());
}

header('Location: admin_loans.php');
exit;
?>
