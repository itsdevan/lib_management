<?php
require_once "config/db.php";
require_once "config/auth.php";
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_loans.php');
    exit;
}

$borrowing_id = (int)($_POST['borrowing_id'] ?? 0);
$fine_paid = isset($_POST['fine_paid']) ? 1 : 0;


$conn->begin_transaction();

try {
    $stmt = $conn->prepare("SELECT br.book_id, br.due_date, br.status, b.title FROM borrowings br JOIN books b ON b.id = br.book_id WHERE br.id = ? FOR UPDATE");
    $stmt->bind_param('i', $borrowing_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || $row['status'] !== 'borrowed') {
        throw new Exception('This borrowing is not currently active.');
    }

    $overdue_days = 0;
    $fine = 0.00;
    if (!empty($row['due_date']) && strtotime($row['due_date']) < time()) {
        $overdue_days = (int)floor((time() - strtotime($row['due_date'])) / 86400);
        if ($overdue_days < 1) $overdue_days = 1;
        $fine = $overdue_days * 10.00;
        if (!$fine_paid) {
            throw new Exception('This book is overdue. Confirm that the user has paid the fine before marking it returned. Fine due: ₹' . number_format($fine, 2));
        }
    }

    $stmt = $conn->prepare("UPDATE borrowings SET returned_at = NOW(), status = 'returned', fine_amount = ?, fine_paid = ? WHERE id = ? AND status = 'borrowed'");
    $stmt->bind_param('dii', $fine, $fine_paid, $borrowing_id);
    $stmt->execute();
    if ($stmt->affected_rows !== 1) throw new Exception('The return could not be recorded.');
    $stmt->close();

    $stmt = $conn->prepare("UPDATE books SET available = 1 WHERE id = ?");
    $stmt->bind_param('i', $row['book_id']);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    $message = '“' . $row['title'] . '” has been marked as returned.';
    if ($fine > 0) $message .= ' Fine paid: ₹' . number_format($fine, 2) . '.';
    flash('success', 'Book returned', $message);
} catch (Exception $e) {
    $conn->rollback();
    flash('error', 'Return blocked', $e->getMessage());
}

header('Location: admin_loans.php');
exit;
?>
