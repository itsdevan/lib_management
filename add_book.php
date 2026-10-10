<?php
require_once "config/db.php";
require_once "config/auth.php";
require_admin();

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accession = trim($_POST['accession_number'] ?? "");
    $title = trim($_POST['title'] ?? "");
    $author = trim($_POST['author'] ?? "");
    $edition = trim($_POST['edition'] ?? "");
    $publisher = trim($_POST['publisher'] ?? "");

    if (!$accession || !$title || !$author || !$edition || !$publisher) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO books (accession_number, title, author, edition, publisher) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $accession, $title, $author, $edition, $publisher);
        if ($stmt->execute()) {
            $stmt->close();
            flash("success", "Book added", "The new volume has been placed in the collection.");
            header("Location: admin_books.php");
            exit;
        }
        $error = ($stmt->errno == 1062) ? "That accession number already exists." : "Could not add the book.";
        $stmt->close();
    }
}
$page_title = "Add Book";
include "includes/header.php";
?>
<div class="form-card wide">
    <div class="eyebrow">KEEPER'S DESK</div>
    <h1>Add a New Book</h1>
    <p class="form-subtitle">Only administrators can change the collection.</p>
    <?php if ($error): ?>
    <script>document.addEventListener('DOMContentLoaded',()=>showMagicToast('error','Book not added',<?= json_encode($error) ?>));</script>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group"><label>Accession number</label><input type="text" name="accession_number" maxlength="50" required></div>
        <div class="form-group"><label>Book title</label><input type="text" name="title" maxlength="200" required></div>
        <div class="form-group"><label>Author</label><input type="text" name="author" maxlength="150" required></div>
        <div class="form-group"><label>Edition</label><input type="text" name="edition" maxlength="50" required></div>
        <div class="form-group"><label>Publisher</label><input type="text" name="publisher" maxlength="150" required></div>
        <button class="btn btn-primary" type="submit">Add to Collection</button>
        <a class="btn btn-secondary" href="admin_books.php">Cancel</a>
    </form>
</div>
<?php include "includes/footer.php"; ?>
