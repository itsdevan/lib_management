<?php
require_once "config/db.php";
require_once "config/auth.php";
require_user();
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM borrowings WHERE user_id = ? AND status = 'borrowed'");
$stmt->bind_param('i',$user_id); $stmt->execute(); $active_count=(int)$stmt->get_result()->fetch_assoc()['total']; $stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM borrowings WHERE user_id = ? AND status = 'pending'");
$stmt->bind_param('i',$user_id); $stmt->execute(); $pending_count=(int)$stmt->get_result()->fetch_assoc()['total']; $stmt->close();

$total_books=(int)$conn->query("SELECT COUNT(*) AS total FROM books")->fetch_assoc()['total'];
$available_books=(int)$conn->query("SELECT COUNT(*) AS total FROM books WHERE available=1")->fetch_assoc()['total'];

$stmt=$conn->prepare("SELECT br.id AS borrowing_id,b.title,b.author,br.borrowed_at,br.due_date,CASE WHEN br.due_date < NOW() THEN 'Overdue' ELSE 'Active' END AS due_status FROM borrowings br JOIN books b ON b.id=br.book_id WHERE br.user_id=? AND br.status='borrowed' ORDER BY br.due_date ASC");
$stmt->bind_param('i',$user_id); $stmt->execute(); $borrowed=$stmt->get_result();

$stmt2=$conn->prepare("SELECT br.id,b.title,b.author,br.created_at FROM borrowings br JOIN books b ON b.id=br.book_id WHERE br.user_id=? AND br.status='pending' ORDER BY br.id DESC");
$stmt2->bind_param('i',$user_id); $stmt2->execute(); $requests=$stmt2->get_result();

$page_title='My Dashboard'; include 'includes/header.php';
?>
<div class="dashboard-head"><div><h1>Welcome, <?= e($_SESSION['username']) ?></h1><p class="muted">Track your requests and approved books.</p></div><span class="badge badge-neutral">MEMBER</span></div>
<div class="stats">
<div class="stat"><div class="label">My active books</div><div class="number"><?= $active_count ?> / 2</div></div>
<div class="stat"><div class="label">Pending requests</div><div class="number"><?= $pending_count ?></div></div>
<div class="stat"><div class="label">Available books</div><div class="number"><?= $available_books ?></div></div>
<div class="stat"><div class="label">Borrowing period</div><div class="number">7 <small>days</small></div></div>
</div>

<div class="section-head"><div><h2>My Requests</h2><p>Borrow requests waiting for administrator approval.</p></div><a class="btn btn-primary" href="books.php">Browse Books</a></div>
<div class="table-card"><div class="table-wrap"><table>
<tr><th>Book</th><th>Requested</th><th>Status</th></tr>
<?php if ($requests->num_rows): while($row=$requests->fetch_assoc()): ?>
<tr><td><strong><?= e($row['title']) ?></strong><br><span class="muted"><?= e($row['author']) ?></span></td><td>Request sent</td><td><span class="badge badge-warning">Waiting for admin</span></td></tr>
<?php endwhile; else: ?><tr><td colspan="3" class="empty">No pending requests.</td></tr><?php endif; ?>
</table></div></div>

<div class="section-head" style="margin-top:28px"><div><h2>My Approved Books</h2><p>Books officially issued to you by the administrator.</p></div></div>
<div class="table-card"><div class="table-wrap"><table>
<tr><th>Book</th><th>Borrowed</th><th>Due date</th><th>Status</th></tr>
<?php if ($borrowed->num_rows): while($row=$borrowed->fetch_assoc()): ?>
<tr><td><strong><?= e($row['title']) ?></strong><br><span class="muted"><?= e($row['author']) ?></span></td><td><?= e(date('d M Y',strtotime($row['borrowed_at']))) ?></td><td><?= e(date('d M Y',strtotime($row['due_date']))) ?></td><td><?php if($row['due_status']==='Overdue'): ?><span class="badge badge-danger">Overdue</span><?php else: ?><span class="badge badge-success">Borrowed</span><?php endif; ?></td></tr>
<?php endwhile; else: ?><tr><td colspan="4" class="empty">No books have been issued to you yet.</td></tr><?php endif; ?>
</table></div></div>
<?php $stmt->close(); $stmt2->close(); include 'includes/footer.php'; ?>
