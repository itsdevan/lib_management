<?php
require_once "config/db.php";
require_once "config/auth.php";
require_admin();

$q = trim($_GET['q'] ?? '');
$like = '%' . $q . '%';

$stmt = $conn->prepare("SELECT br.id, u.username, u.email, b.accession_number, b.title, b.author, br.borrowed_at, br.due_date, br.status, br.returned_at, br.fine_amount, br.fine_paid FROM borrowings br JOIN users u ON u.id = br.user_id JOIN books b ON b.id = br.book_id WHERE (? = '' OR u.username LIKE ? OR u.email LIKE ? OR b.title LIKE ? OR b.accession_number LIKE ?) ORDER BY CASE WHEN br.status = 'pending' THEN 0 WHEN br.status = 'borrowed' THEN 1 ELSE 2 END, br.due_date ASC, br.id DESC");
$stmt->bind_param('sssss', $q, $like, $like, $like, $like);
$stmt->execute();
$loans = $stmt->get_result();

$pending_count = (int)$conn->query("SELECT COUNT(*) AS total FROM borrowings WHERE status = 'pending'")->fetch_assoc()['total'];
$page_title = 'Borrowing Ledger';
include 'includes/header.php';
?>

<div class="section-head">
<div>
<h1>Borrowing Ledger</h1>
<p>Approve requests, find a borrower, and complete returns.</p>
</div>
<span class="badge badge-warning">Pending requests: <?= $pending_count ?></span>
</div>

<form class="search-bar" method="GET">
<input type="text" name="q" value="<?= e($q) ?>" placeholder="Search user, email, book or accession number...">
<button class="btn btn-primary" type="submit">Search</button>
</form>

<div class="table-card"><div class="table-wrap"><table>
<tr>
<th>User</th><th>Book</th><th>Borrowed</th><th>Due</th><th>Status</th><th>Action</th>
</tr>
<?php if ($loans->num_rows): ?>
<?php while ($row = $loans->fetch_assoc()): ?>
<tr>
<td><strong><?= e($row['username']) ?></strong><br><span class="muted"><?= e($row['email']) ?></span></td>
<td><?= e($row['title']) ?><br><span class="muted">#<?= e($row['accession_number']) ?></span></td>
<td><?= $row['borrowed_at'] ? e(date('d M Y', strtotime($row['borrowed_at']))) : '—' ?></td>
<td><?= $row['due_date'] ? e(date('d M Y', strtotime($row['due_date']))) : '—' ?></td>
<td>
<?php if ($row['status'] === 'pending'): ?>
<span class="badge badge-warning">Request Pending</span>
<?php elseif ($row['status'] === 'returned'): ?>
<span class="badge badge-neutral">Returned</span>
<?php elseif (strtotime($row['due_date']) < time()): ?>
<span class="badge badge-danger">Overdue</span>
<?php else: ?>
<span class="badge badge-success">Borrowed</span>
<?php endif; ?>
</td>
<td>
<?php if ($row['status'] === 'pending'): ?>
<form method="POST" action="approve_borrow.php" style="display:inline">
<input type="hidden" name="borrowing_id" value="<?= (int)$row['id'] ?>">
<button class="btn btn-success" type="submit">Approve</button>
</form>
<form method="POST" action="reject_borrow.php" style="display:inline;margin-left:5px">
<input type="hidden" name="borrowing_id" value="<?= (int)$row['id'] ?>">
<button class="btn btn-danger" type="submit">Decline</button>
</form>
<?php elseif ($row['status'] === 'borrowed'): ?>
<?php $is_overdue = strtotime($row['due_date']) < time(); $days = $is_overdue ? max(1, (int)floor((time()-strtotime($row['due_date']))/86400)) : 0; $fine = $days * 10; ?>
<?php if ($is_overdue): ?>
<div style="margin-bottom:7px"><span class="badge badge-danger">Fine: ₹<?= number_format($fine,2) ?></span></div>
<form method="POST" action="admin_return.php">
<input type="hidden" name="borrowing_id" value="<?= (int)$row['id'] ?>">
<label style="display:block;font-size:11px;margin-bottom:7px">
<input type="checkbox" name="fine_paid" value="1" required>
User has paid the fine
</label>
<button class="btn btn-success" type="submit">Done — Returned</button>
</form>
<?php else: ?>
<form method="POST" action="admin_return.php">
<input type="hidden" name="borrowing_id" value="<?= (int)$row['id'] ?>">
<button class="btn btn-success" type="submit">Done — Returned</button>
</form>
<?php endif; ?>
<?php else: ?>
<span class="muted">Completed<?= $row['fine_paid'] ? ' • Fine paid' : '' ?></span>
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="6" class="empty">No matching borrowing records.</td></tr>
<?php endif; ?>
</table></div></div>

<?php $stmt->close(); include 'includes/footer.php'; ?>
