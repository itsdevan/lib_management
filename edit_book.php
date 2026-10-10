<?php
require_once "config/db.php";
require_once "config/auth.php";
require_admin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT id, accession_number, title, author, edition, publisher, available FROM books WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$book) {
    flash("error", "Book not found", "That volume does not exist in the collection.");
    header("Location: admin_books.php");
    exit;
}

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
        $stmt = $conn->prepare("UPDATE books SET accession_number=?, title=?, author=?, edition=?, publisher=? WHERE id=?");
        $stmt->bind_param("sssssi", $accession, $title, $author, $edition, $publisher, $id);
        if ($stmt->execute()) {
            $stmt->close();
            flash("success", "Book updated", "The volume's details have been revised.");
            header("Location: admin_books.php");
            exit;
        }
        $error = ($stmt->errno == 1062) ? "That accession number already exists." : "Could not update the book.";
        $stmt->close();
    }
}
$page_title = "Edit Book";
include "includes/header.php";
?>
<div class="form-card wide">
    <div class="eyebrow">KEEPER'S DESK</div>
    <h1>Edit Book</h1>
    <p class="form-subtitle">Update the catalogue details for this volume.</p>
    <?php if ($error): ?>
    <script>document.addEventListener('DOMContentLoaded',()=>showMagicToast('error','Book not updated',<?= json_encode($error) ?>));</script>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group"><label>Accession number</label><input type="text" name="accession_number" value="<?= e($book['accession_number']) ?>" required></div>
        <div class="form-group"><label>Book title</label><input type="text" name="title" value="<?= e($book['title']) ?>" required></div>
        <div class="form-group"><label>Author</label><input type="text" name="author" value="<?= e($book['author']) ?>" required></div>
        <div class="form-group"><label>Edition</label><input type="text" name="edition" value="<?= e($book['edition']) ?>" required></div>
        <div class="form-group"><label>Publisher</label><input type="text" name="publisher" value="<?= e($book['publisher']) ?>" required></div>
        <button class="btn btn-primary" type="submit">Save Changes</button>
        <a class="btn btn-secondary" href="admin_books.php">Cancel</a>
    </form>
</div>
<?php include "includes/footer.php"; ?>
