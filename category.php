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
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'delete') {
        // Delete category
        $category_id = intval($_POST['category_id']);
        $sql = "DELETE FROM category WHERE category_id=$category_id";
        if (mysqli_query($conn, $sql)) {
            $success_msg = "Category deleted successfully!";
        } else {
            $error_msg = "Error deleting category: " . mysqli_error($conn);
        }
    } else {
        // Add or update
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        if (!empty($_POST['category_id'])) {
            // Edit existing
            $category_id = intval($_POST['category_id']);
            $sql = "UPDATE category SET name='$category' WHERE category_id=$category_id";
            if (mysqli_query($conn, $sql)) {
                $success_msg = "Category updated successfully!";
            } else {
                $error_msg = "Error updating category: " . mysqli_error($conn);
            }
        } else {
            // Add new
            $sql = "INSERT INTO category (name) VALUES ('$category')";
            if (mysqli_query($conn, $sql)) {
                $success_msg = "Category added successfully!";
            } else {
                $error_msg = "Error adding category: " . mysqli_error($conn);
            }
        }
    }
    
    if (!$error_msg) {
        header('Location: category.php?success=1');
        exit();
    }
}

if (!empty($_GET['success'])) {
    $success_msg = "Operation completed successfully!";
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
$categories = [];
if ($cat_result) {
    while ($row = mysqli_fetch_assoc($cat_result)) {
        $categories[] = $row;
    }
} else {
    $error_msg = 'Error: ' . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Category Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .category-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-header h2 {
            font-weight: 600;
            color: var(--text-main);
            margin: 0;
        }

        .page-header .badge {
            background-color: var(--safe);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 50px;
        }

        .form-section {
            background: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .form-section h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-main);
        }

        .form-row {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .form-row input {
            flex: 1;
            min-width: 200px;
            padding: 0.5rem 1rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            font-size: 0.95rem;
            transition: border-color 0.2s;
        }

        .form-row input:focus {
            outline: none;
            border-color: var(--safe);
            box-shadow: 0 0 0 3px rgba(136, 211, 140, 0.1);
        }

        .form-row .btn {
            white-space: nowrap;
            font-weight: 500;
        }

        .search-section {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .search-section input {
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            width: 100%;
            max-width: 300px;
            font-size: 0.95rem;
        }

        .search-section i {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .categories-grid {
            width: 100%;
            margin-bottom: 2rem;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        .grid-item {
            background: white;
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            padding: 1.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .grid-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.1);
            border-color: var(--safe);
        }

        .category-item-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-height: 50px;
        }

        .category-item-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--safe), #6bc97f);
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .category-item-name {
            font-weight: 600;
            color: var(--text-main);
            font-size: 1rem;
            word-break: break-word;
            line-height: 1.3;
        }

        .category-item-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-top: auto;
        }

        .category-item-actions .btn-grid {
            padding: 0.6rem 1rem;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
        }

        .btn-grid.btn-edit {
            background-color: var(--safe);
            color: white;
        }

        .btn-grid.btn-edit:hover {
            background-color: #6bc97f;
        }

        .btn-grid.btn-delete {
            background-color: var(--danger);
            color: white;
        }

        .btn-grid.btn-delete:hover {
            background-color: #a01206;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #999;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #ddd;
        }

        .empty-state p {
            font-size: 1rem;
            margin-bottom: 0;
        }

        .alert {
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .btn-primary {
            background-color: var(--safe);
            border: none;
            font-weight: 500;
        }

        .btn-primary:hover {
            background-color: #6bc97f;
        }

        .btn-secondary {
            background-color: #999;
            border: none;
            font-weight: 500;
        }

        .btn-secondary:hover {
            background-color: #777;
        }

        @media (max-width: 1200px) {
            .grid-container {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .grid-container {
                grid-template-columns: repeat(2, 1fr);
            }

            .category-item-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-row {
                flex-direction: column;
            }

            .form-row input,
            .form-row .btn {
                width: 100%;
            }

            .search-section input {
                max-width: 100%;
            }
        }

        @media (max-width: 480px) {
            .grid-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div id="wrapper" class="d-flex vh-100 w-100" style="overflow: hidden;">
    <?php include 'sidebar.php'; ?>

    <div id="page-content-wrapper" class="flex-grow-1 d-flex flex-column p-4" style="overflow-y: auto;">
        <div class="category-container">
            <!-- Alerts -->
            <?php if ($success_msg): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_msg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_msg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h2><i class="fas fa-tags"></i> Category Manager</h2>
                    <small class="text-muted">Manage Global Categories</small>
                </div>
                <span class="badge"><?php echo count($categories); ?> Categories</span>
            </div>

            <!-- Form Section -->
            <div class="form-section">
                <h4><?php echo $edit_mode ? '<i class="fas fa-edit"></i> Edit Category' : '<i class="fas fa-plus"></i> Add New Category'; ?></h4>
                <form method="POST" action="">
                    <div class="form-row">
                        <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($edit_category_id); ?>">
                        <input 
                            type="text" 
                            name="category" 
                            id="category" 
                            placeholder="Enter category name..." 
                            value="<?php echo htmlspecialchars($edit_category_name); ?>"
                            required
                            autofocus
                        >
                        <button type="submit" class="btn btn-primary">
                            <i class="fas <?php echo $edit_mode ? 'fa-save' : 'fa-plus'; ?>"></i>
                            <?php echo $edit_mode ? 'Update' : 'Add'; ?>
                        </button>
                        <?php if ($edit_mode): ?>
                            <a href="category.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Search Section -->
            <?php if (count($categories) > 0): ?>
                <div class="search-section">
                    <i class="fas fa-search"></i>
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="Search categories..."
                        onkeyup="filterCategories()"
                    >
                </div>
            <?php endif; ?>

            <!-- Categories Grid -->
            <?php if (count($categories) > 0): ?>
                <div class="categories-grid">
                    <div class="grid-container" id="gridContainer">
                        <?php foreach ($categories as $cat): ?>
                            <div class="grid-item" data-category="<?php echo strtolower(htmlspecialchars($cat['name'])); ?>">
                                <div class="category-item-header">
                                    <div class="category-item-icon">
                                        <i class="fas fa-folder"></i>
                                    </div>
                                    <div class="category-item-name">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </div>
                                </div>
                                <div class="category-item-actions">
                                    <a href="category.php?edit=<?php echo $cat['category_id']; ?>" class="btn-grid btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="category_id" value="<?php echo $cat['category_id']; ?>">
                                        <button type="submit" class="btn-grid btn-delete" style="width: 100%;">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No categories yet. Create one to get started!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function filterCategories() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const items = document.querySelectorAll('.grid-item');
    let visibleCount = 0;

    items.forEach(item => {
        const categoryName = item.getAttribute('data-category');
        if (categoryName.includes(searchInput)) {
            item.style.display = '';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    // Show empty state if no results
    const gridContainer = document.getElementById('gridContainer');
    if (visibleCount === 0 && searchInput) {
        if (!document.getElementById('noResults')) {
            const noResults = document.createElement('div');
            noResults.id = 'noResults';
            noResults.className = 'empty-state';
            noResults.style.gridColumn = '1 / -1';
            noResults.innerHTML = '<i class="fas fa-search"></i><p>No categories found</p>';
            gridContainer.parentNode.insertBefore(noResults, gridContainer.nextSibling);
        }
    } else {
        const noResults = document.getElementById('noResults');
        if (noResults) noResults.remove();
    }
}

// Auto-dismiss alerts after 4 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 4000);
    });
});
</script>
</body>
</html>
