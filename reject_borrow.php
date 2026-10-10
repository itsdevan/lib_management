<?php
require_once "config/db.php";
require_once "config/auth.php";
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_loans.php');
    exit;
}

$borrowing_id = (int)($_POST['borrowing_id'] ?? 0);
$stmt = $conn->prepare("SELECT b.title FROM borrowings br JOIN books b ON b.id = br.book_id WHERE br.id = ? AND br.status = 'pending'");
$stmt->bind_param('i', $borrowing_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    flash('error', 'Request not found', 'That borrow request has already been processed.');
    header('Location: admin_loans.php');
    exit;
}

$stmt = $conn->prepare("DELETE FROM borrowings WHERE id = ? AND status = 'pending'");
$stmt->bind_param('i', $borrowing_id);
if ($stmt->execute() && $stmt->affected_rows === 1) {
    flash('success', 'Request declined', 'The request for “' . $row['title'] . '” was declined.');
} else {
    flash('error', 'Request failed', 'The request could not be declined.');
}
$stmt->close();
header('Location: admin_loans.php');
exit;
?>
