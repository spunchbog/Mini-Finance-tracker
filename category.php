<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$edit_mode = false;
$edit_category_id = '';
$edit_category_name = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    if (!empty($_POST['category_id'])) {
        // Edit existing
        $category_id = intval($_POST['category_id']);
        $sql = "UPDATE category SET name='$category' WHERE category_id=$category_id";
        mysqli_query($conn, $sql);
    } else {
        // Add new
        $sql = "INSERT INTO category (name) VALUES ('$category')";
        mysqli_query($conn, $sql);
    }
    header('Location: category.php');
    exit();
}

if (!empty($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_category_id = intval($_GET['edit']);
    $result = mysqli_query($conn, "SELECT name FROM category WHERE category_id = $edit_category_id LIMIT 1");
    if ($result && mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        $edit_category_name = $row['name'];
        $edit_mode = true;
    }
}

// Fetch all categories
$cat_result = mysqli_query($conn, "SELECT * FROM category ORDER BY name ASC");
if (!$cat_result) {
    echo '<tr><td colspan="2">Error: ' . mysqli_error($conn) . '</td></tr>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Category Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div id="wrapper" class="d-flex vh-100 w-100" style="overflow: hidden;">
    <?php include 'sidebar.php'; ?>

    <div id="page-content-wrapper" class="flex-grow-1 d-flex flex-column p-4" style="overflow: hidden;">

        <h2>Category Manager</h2>
        <p>Add or edit "Global Categories" that appear for all users.</p>
<form method="POST" action="">
    <input type="hidden" name="category_id" id="category_id" value="<?php echo htmlspecialchars($edit_category_id); ?>">
    <input type="text" name="category" id="category" required placeholder="Category Name" value="<?php echo htmlspecialchars($edit_category_name); ?>">
    <button type="submit"><?php echo $edit_mode ? 'Update Category' : 'Save Category'; ?></button>
    <?php if ($edit_mode): ?>
        <a href="category.php" class="btn btn-secondary">Cancel</a>
    <?php endif; ?>
</form>

<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>Category</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($cat_result) {
            while($cat = mysqli_fetch_assoc($cat_result)): ?>
        <tr>
            <td><?php echo htmlspecialchars($cat['name']); ?></td>
            <td>
                <a href="category.php?edit=<?php echo $cat['category_id']; ?>" class="btn btn-outline-primary btn-sm">Edit</a>
            </td>
        </tr>
        <?php endwhile; 
        } ?>
    </tbody>
</table>

<?php include('footer.php'); ?>


