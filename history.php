<?php
require_once "config/db.php"; require_once "config/auth.php"; require_user();
$stmt=$conn->prepare("SELECT b.title,b.author,br.borrowed_at,br.due_date,br.returned_at,br.status,br.fine_amount,br.fine_paid FROM borrowings br JOIN books b ON b.id=br.book_id WHERE br.user_id=? ORDER BY br.id DESC");
$stmt->bind_param('i',$_SESSION['user_id']); $stmt->execute(); $history=$stmt->get_result();
$page_title='Borrowing History'; include 'includes/header.php';
?>
<div class="section-head"><div><h1>Borrowing History</h1><p>Your requests, approved loans and completed returns.</p></div></div>
<div class="table-card"><div class="table-wrap"><table><tr><th>Book</th><th>Requested / Borrowed</th><th>Due</th><th>Returned</th><th>Status</th></tr>
<?php if($history->num_rows): while($row=$history->fetch_assoc()): ?>
<tr><td><strong><?= e($row['title']) ?></strong><br><span class="muted"><?= e($row['author']) ?></span></td><td><?= $row['borrowed_at']?e(date('d M Y',strtotime($row['borrowed_at']))):'Request sent' ?></td><td><?= $row['due_date']?e(date('d M Y',strtotime($row['due_date']))):'—' ?></td><td><?= $row['returned_at']?e(date('d M Y',strtotime($row['returned_at']))):'—' ?></td><td><?php if($row['status']==='pending'): ?><span class="badge badge-warning">Request Pending</span><?php elseif($row['status']==='returned'): ?><span class="badge badge-neutral">Returned<?= $row['fine_paid']?' • Fine paid':'' ?></span><?php elseif(strtotime($row['due_date'])<time()): ?><span class="badge badge-danger">Overdue</span><?php else: ?><span class="badge badge-success">Borrowed</span><?php endif; ?></td></tr>
<?php endwhile; else: ?><tr><td colspan="5" class="empty">No borrowing history yet.</td></tr><?php endif; ?></table></div></div>
<?php $stmt->close(); include 'includes/footer.php'; ?>
