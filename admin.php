<?php

require_once "config/db.php";
require_once "config/auth.php";

require_admin();

/* Statistics */

$total_books =
    $conn->query(
        "SELECT COUNT(*) AS total FROM books"
    )->fetch_assoc()['total'];

$available =
    $conn->query(
        "SELECT COUNT(*) AS total
         FROM books
         WHERE available = 1"
    )->fetch_assoc()['total'];

$members =
    $conn->query(
        "SELECT COUNT(*) AS total
         FROM users
         WHERE role = 'user'"
    )->fetch_assoc()['total'];

$active_loans =
    $conn->query(
        "SELECT COUNT(*) AS total FROM borrowings WHERE status = 'borrowed'"
    )->fetch_assoc()['total'];

$pending_requests =
    $conn->query(
        "SELECT COUNT(*) AS total FROM borrowings WHERE status = 'pending'"
    )->fetch_assoc()['total'];

/* Current borrowers */

$loans = $conn->query("
    SELECT
        u.username,
        b.title,
        b.accession_number,
        br.borrowed_at,
        br.due_date

    FROM borrowings br

    JOIN users u
        ON u.id = br.user_id

    JOIN books b
        ON b.id = br.book_id

    WHERE br.status = 'borrowed'

    ORDER BY br.due_date ASC
");

$page_title = "Admin Dashboard";

include "includes/header.php";

?>

<div class="dashboard-head">

<div>

<h1>
Admin Dashboard
</h1>

<p class="muted">
Manage the library collection and monitor borrowing.
</p>

</div>

<span class="badge badge-warning">
ADMIN
</span>

</div>

<div class="stats">

<div class="stat">

<div class="label">
Total books
</div>

<div class="number">
<?= $total_books ?>
</div>

</div>

<div class="stat">

<div class="label">
Available books
</div>

<div class="number">
<?= $available ?>
</div>

</div>

<div class="stat">

<div class="label">
Registered users
</div>

<div class="number">
<?= $members ?>
</div>

</div>

<div class="stat">

<div class="label">Currently borrowed</div>
<div class="number"><?= $active_loans ?></div>

</div>

<div class="stat"><div class="label">Pending requests</div><div class="number"><?= $pending_requests ?></div></div>

</div>

<div class="cards" style="margin-top:25px;">

<a
    class="card"
    href="admin_books.php"
>



<h3>
Book Collection
</h3>

<p>
Add, update and delete books.
</p>

</a>

<a
    class="card"
    href="admin_loans.php"
>



<h3>
Borrowed Books
</h3>

<p>
Approve requests and complete returns.
</p>

</a>

</div>

<div class="section">

<div class="section-head">

<div>

<h2>
Current Borrowers
</h2>

<p>
Approved loans currently held by users.
</p>

</div>

<a
    class="btn btn-secondary"
    href="admin_loans.php"
>
View all
</a>

</div>

<div class="table-card">

<div class="table-wrap">

<table>

<tr>

<th>User</th>
<th>Book</th>
<th>Accession</th>
<th>Borrowed</th>
<th>Due date</th>

</tr>

<?php if ($loans->num_rows): ?>

<?php while ($row = $loans->fetch_assoc()): ?>

<tr>

<td>
<strong>
<?= e($row['username']) ?>
</strong>
</td>

<td>
<?= e($row['title']) ?>
</td>

<td>
<?= e($row['accession_number']) ?>
</td>

<td>
<?= e(
    date(
        'd M Y',
        strtotime($row['borrowed_at'])
    )
) ?>
</td>

<td>

<?php if (
    strtotime($row['due_date']) < time()
): ?>

<span class="badge badge-danger">
Overdue
</span>

<?php else: ?>

<span class="badge badge-success">
<?= e(
    date(
        'd M Y',
        strtotime($row['due_date'])
    )
) ?>
</span>

<?php endif; ?>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

<td
    colspan="5"
    class="empty"
>
No books are currently borrowed.
</td>

</tr>

<?php endif; ?>

</table>

</div>

</div>

</div>

<?php include "includes/footer.php"; ?>