<?php

require_once "config/db.php";
require_once "config/auth.php";

require_admin();

$q = trim($_GET['q'] ?? "");
$message = $_GET['message'] ?? "";

if ($q !== "") {

    $like = "%$q%";

    $stmt = $conn->prepare("
        SELECT
            id,
            accession_number,
            title,
            author,
            edition,
            publisher,
            available

        FROM books

        WHERE
            title LIKE ?
            OR author LIKE ?
            OR accession_number LIKE ?

        ORDER BY title
    ");

    $stmt->bind_param(
        "sss",
        $like,
        $like,
        $like
    );

    $stmt->execute();

    $books = $stmt->get_result();

} else {

    $books = $conn->query("
        SELECT
            id,
            accession_number,
            title,
            author,
            edition,
            publisher,
            available

        FROM books

        ORDER BY title
    ");

    $stmt = null;
}

$page_title = "Book Collection";

include "includes/header.php";

?>

<div class="section-head">

<div>

<h1>
Book Collection
</h1>

<p>
Admin can add, edit and delete books.
</p>

</div>

<a
    class="btn btn-primary"
    href="add_book.php"
>
+ Add Book
</a>

</div>

<?php if ($message): ?>

<div class="alert alert-info">
<?= e($message) ?>
</div>

<?php endif; ?>

<form
    class="search-bar"
    method="GET"
>

<input
    type="text"
    name="q"
    value="<?= e($q) ?>"
    placeholder="Search title, author or accession number..."
>

<button
    class="btn btn-primary"
    type="submit"
>
Search
</button>

</form>

<div class="table-card">

<div class="table-wrap">

<table>

<tr>

<th>Accession</th>
<th>Title</th>
<th>Author</th>
<th>Edition</th>
<th>Publisher</th>
<th>Status</th>
<th>Actions</th>

</tr>

<?php if ($books->num_rows): ?>

<?php while ($book = $books->fetch_assoc()): ?>

<tr>

<td>
<?= e($book['accession_number']) ?>
</td>

<td>
<strong>
<?= e($book['title']) ?>
</strong>
</td>

<td>
<?= e($book['author']) ?>
</td>

<td>
<?= e($book['edition']) ?>
</td>

<td>
<?= e($book['publisher']) ?>
</td>

<td>

<?php if ($book['available']): ?>

<span class="badge badge-success">
Available
</span>

<?php else: ?>

<span class="badge badge-danger">
Borrowed
</span>

<?php endif; ?>

</td>

<td>

<a
    class="btn btn-secondary"
    href="edit_book.php?id=<?= (int)$book['id'] ?>"
>
Edit
</a>

<form
    method="POST"
    action="delete_book.php"
    style="display:inline;"
    onsubmit="return confirmRemoval(this);"
>

<input
    type="hidden"
    name="id"
    value="<?= (int)$book['id'] ?>"
>

<button
    class="btn btn-danger"
    type="submit"
>
Delete
</button>

</form>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

<td
    colspan="7"
    class="empty"
>
No books found.
</td>

</tr>

<?php endif; ?>

</table>

</div>

</div>

<?php

if ($stmt) {
    $stmt->close();
}

include "includes/footer.php";

?>