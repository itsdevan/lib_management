<?php
require_once "config/db.php";
require_once "config/auth.php";
require_user();
$query=trim($_GET['q']??''); $user_id=$_SESSION['user_id'];
if($query!==''){
 $like="%$query%";
 $stmt=$conn->prepare("SELECT id,accession_number,title,author,edition,publisher,available FROM books WHERE title LIKE ? OR author LIKE ? OR accession_number LIKE ? ORDER BY title");
 $stmt->bind_param('sss',$like,$like,$like);
}else{
 $stmt=$conn->prepare("SELECT id,accession_number,title,author,edition,publisher,available FROM books ORDER BY title");
}
$stmt->execute(); $books=$stmt->get_result();
$stmt_count=$conn->prepare("SELECT COUNT(*) AS total FROM borrowings WHERE user_id=? AND status IN ('pending','borrowed')");
$stmt_count->bind_param('i',$user_id); $stmt_count->execute(); $my_active=(int)$stmt_count->get_result()->fetch_assoc()['total']; $stmt_count->close();
$stmt_pending=$conn->prepare("SELECT book_id FROM borrowings WHERE user_id=? AND status='pending'");
$stmt_pending->bind_param('i',$user_id); $stmt_pending->execute(); $pending_result=$stmt_pending->get_result(); $pending_books=[]; while($p=$pending_result->fetch_assoc()) $pending_books[(int)$p['book_id']]=true; $stmt_pending->close();
$page_title='Browse Books'; include 'includes/header.php';
?>
<div class="section-head"><div><h1>Browse Library</h1><p>Request a book. The administrator must approve the request before it becomes borrowed.</p></div><span class="badge badge-neutral">Requests + books: <?= $my_active ?> / 2</span></div>
<form class="search-bar" method="GET"><input type="text" name="q" value="<?= e($query) ?>" placeholder="Search title, author or accession number..."><button class="btn btn-primary" type="submit">Search</button></form>
<div class="book-grid">
<?php if($books->num_rows): while($book=$books->fetch_assoc()): ?>
<div class="book-card"><div class="book-spine"></div><h3><?= e($book['title']) ?></h3><div class="book-author">by <?= e($book['author']) ?></div><div class="book-meta"><span>Edition: <?= e($book['edition']) ?></span><span>#<?= e($book['accession_number']) ?></span></div><div style="margin-top:15px;">
<?php if(!$book['available']): ?><span class="badge badge-danger">Currently borrowed</span>
<?php elseif(isset($pending_books[(int)$book['id']])): ?><span class="badge badge-warning">Request pending</span>
<?php elseif($my_active>=2): ?><span class="badge badge-warning">Limit reached</span>
<?php else: ?><span class="badge badge-success">Available</span><form method="POST" action="borrow.php" style="display:inline"><input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>"><button class="btn btn-primary" style="margin-left:7px;padding:7px 11px" type="submit">Request Borrow</button></form><?php endif; ?>
</div></div>
<?php endwhile; else: ?><div class="card empty" style="grid-column:1/-1">No books found.</div><?php endif; ?>
</div>
<?php $stmt->close(); include 'includes/footer.php'; ?>
