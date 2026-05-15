<?php
include('header.php');
include('db_connect.php');

// Handle add/edit category
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
}

// Fetch all categories
$cat_result = mysqli_query($conn, "SELECT * FROM category ORDER BY name ASC");
if (!$cat_result) {
    echo '<tr><td colspan="2">Error: ' . mysqli_error($conn) . '</td></tr>';
}
?>

<h2>Category Manager</h2>
<p>Add or edit "Global Categories" that appear for all users.</p>
<form method="POST" action="">
    <input type="hidden" name="category_id" id="category_id">
    <input type="text" name="category" id="category" required placeholder="Category Name">
    <button type="submit">Save Category</button>
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
                <button onclick="editCategory('<?php echo $cat['category_id']; ?>', '<?php echo htmlspecialchars($cat['name']); ?>')">Edit</button>
            </td>
        </tr>
        <?php endwhile; 
        } ?>
    </tbody>
</table>

<script>
function editCategory(id, name) {
    document.getElementById('category_id').value = id;
    document.getElementById('category').value = name;
}
</script>

<?php include('footer.php'); ?>

