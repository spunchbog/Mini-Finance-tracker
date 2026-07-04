<?php
session_start();
include('db_connect.php'); // Assumes this initializes your $pdo connection object

// 1. DYNAMIC CHECK: Ensure a user is actually logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$msg = "";

// 2. HANDLE BUDGET SUBMISSION (PDO Prepared Transition)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['set_budget'])) {
    $category_id = (int)$_POST['category_id'];
    $limit_amount = (float)$_POST['limit_amount'];

    // Check if a budget limit configuration already exists for this user/category combo
    $check_exist = $pdo->prepare("SELECT budget_id FROM budget WHERE user_id = :user_id AND category_id = :category_id LIMIT 1");
    $check_exist->execute([
        ':user_id' => $user_id,
        ':category_id' => $category_id
    ]);
    
    if ($check_exist->fetch()) {
        // Update the existing limit configuration safely
        $save_stmt = $pdo->prepare("UPDATE budget SET limit_amount = :limit_amount WHERE user_id = :user_id AND category_id = :category_id");
    } else {
        // Insert a clean new budget threshold row
        $save_stmt = $pdo->prepare("INSERT INTO budget (user_id, category_id, limit_amount) VALUES (:user_id, :category_id, :limit_amount)");
    }
    
    $success = $save_stmt->execute([
        ':user_id' => $user_id,
        ':category_id' => $category_id,
        ':limit_amount' => $limit_amount
    ]);

    if ($success) {
        $msg = '
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-3" role="alert" style="border-radius: 8px; border-color: rgba(43, 138, 62, 0.2); background-color: #EBFBEE;">
            <span class="me-2">✅</span>
            <div class="fw-medium text-success" style="font-size: 0.9rem;">Budget threshold parameters configured successfully!</div>
            <button type="button" class="btn-close ms-auto py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    } else {
        $msg = '
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-3" role="alert" style="border-radius: 8px; border-color: rgba(201, 42, 42, 0.2); background-color: #FFEBEB;">
            <span class="me-2">⚠️</span>
            <div class="fw-medium text-danger" style="font-size: 0.9rem;">Error saving budget configuration parameters.</div>
            <button type="button" class="btn-close ms-auto py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

// 3. FETCH ONLY CATEGORIES THAT HAVE ACTUALLY BEEN USED FOR EXPENSES
$table_name = $pdo->query("SHOW TABLES LIKE 'transaction'")->fetch() ? "transaction" : "transactions";

$categories_stmt = $pdo->prepare("
    SELECT DISTINCT c.category_id, c.name 
    FROM category c
    JOIN `$table_name` t ON c.category_id = t.category_id
    WHERE t.type = 'expense' 
    ORDER BY c.name ASC
");
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll();

// 4. FETCH CURRENT MONTH'S EXPENSES BY CATEGORY ID
$current_month_start = date('Y-m-01 00:00:00');
$current_month_end = date('Y-m-t 23:59:59');

$expenses = [];

// Determine if the table is singular or plural dynamically
$table_name = "transaction"; 
$table_check = $pdo->query("SHOW TABLES LIKE 'transaction'")->fetch();
if (!$table_check) {
    $table_name = "transactions";
}

// DYNAMIC COLUMN DETECTOR: Date Field
$date_column = "date";
$column_check = $pdo->query("SHOW COLUMNS FROM `$table_name` LIKE 'transaction_date'")->fetch();
if ($column_check) {
    $date_column = "transaction_date";
}

// DYNAMIC COLUMN DETECTOR: Amount Field
$amount_column = "amount";
$amt_check = $pdo->query("SHOW COLUMNS FROM `$table_name` LIKE 'amt'")->fetch();
if ($amt_check) {
    $amount_column = "amt";
}

// === No more category table JOIN. Everything filters on the transaction table columns directly ===
$expense_query = "SELECT category_id, SUM(ABS(`$amount_column`)) as total_spent 
                  FROM `$table_name` 
                  WHERE user_id = :user_id 
                    AND type = 'expense' 
                    AND `$date_column` BETWEEN :start_date AND :end_date
                  GROUP BY category_id";

$expense_stmt = $pdo->prepare($expense_query);
$expense_stmt->execute([
    ':user_id' => $user_id,
    ':start_date' => $current_month_start,
    ':end_date' => $current_month_end
]);

while ($row = $expense_stmt->fetch()) {
    $expenses[(int)$row['category_id']] = (float)$row['total_spent'];
}

// 5. FETCH CONFIGURED BUDGET STATUS WITH JOINED CATEGORY NAMES
$budget_status_stmt = $pdo->prepare("
    SELECT b.category_id, b.limit_amount, c.name 
    FROM budget b 
    JOIN category c ON b.category_id = c.category_id 
    WHERE b.user_id = :user_id
");
$budget_status_stmt->execute([':user_id' => $user_id]);
$budget_statuses = $budget_status_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">    
<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinTrack - Monthly Budgeting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div id="wrapper" class="d-flex vh-100 w-100" style="overflow: hidden;"> 
    <?php include 'sidebar.php'; ?>

    <div id="page-content-wrapper" class="flex-grow-1 d-flex flex-column p-4">
        <header class="mb-4">
            <h2 class="mb-1 fw-bold">Monthly Budgeting</h2>
            <p class="text-muted mb-0" style="font-size: 0.95rem;">Establish specific spending limits for various categories to maintain control over finances.</p>
        </header>

        <?php if (!empty($msg)) echo $msg; ?>

        <div class="row g-4 flex-grow-1 min-height-0">
            
            <!-- Left Config Form Column -->
            <div class="col-12 col-md-4 d-flex flex-column">
                <?php 
                $editing_cat = isset($_GET['edit_category_id']) ? (int)$_GET['edit_category_id'] : 0;
                $editing_limit = isset($_GET['edit_limit']) ? (float)$_GET['edit_limit'] : '';
                ?>
                
                <div class="card p-3 shadow-sm d-flex flex-column" style="overflow: hidden; height: auto !important;">
                    <h5 class="mb-3 fw-bold tracking-tight" style="letter-spacing: -0.3px; font-size: 1.15rem;">
                        <?= $editing_cat > 0 ? "✏️ Edit Budget Limit" : "➕ Set Category Budget" ?>
                    </h5>

                    <form action='budget.php' method='POST'>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted text-uppercase tracking-wider" style="font-size: 0.8rem;">Select Category</label>
                            <select name="category_id" class="form-select fw-medium text-main ps-2 py-2" style="border-color: var(--border-subtle); border-radius: 8px; font-size: 0.9rem;" required <?= $editing_cat > 0 ? 'style="pointer-events: none; background-color: #f1f3f5;"' : '' ?>>
                                <option value="">-- Choose Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['category_id']; ?>" <?= $cat['category_id'] == $editing_cat ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if($editing_cat > 0): ?>
                                <input type="hidden" name="category_id" value="<?= $editing_cat ?>">
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-muted text-uppercase tracking-wider" style="font-size: 0.8rem;">Limit Amount (RM)</label>
                            <div class="input-group shadow-sm" style="border: 1px solid var(--border-subtle); border-radius: 8px; overflow: hidden;">
                                <span class="input-group-text bg-light text-muted border-0 fw-bold" style="font-size: 0.9rem;">RM</span>
                                <input type="number" step="0.01" name="limit_amount" class="form-control border-0 ps-2 fw-semibold" style="font-size: 0.95rem; height: 38px;" required 
                                       placeholder="e.g. 300.00" value="<?= htmlspecialchars($editing_limit) ?>">
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            <button type="submit" name="set_budget" class="btn btn-primary fw-semibold shadow-sm py-2" style="border-radius: 8px; font-size: 0.9rem;">
                                <?= $editing_cat > 0 ? "Update Budget Limit" : "Save Budget Setup" ?>
                            </button>
                            <?php if ($editing_cat > 0): ?>
                                <a href="budget.php" class="btn btn-outline-secondary text-decoration-none text-center fw-medium py-2" style="border-radius: 8px; font-size: 0.9rem;">Cancel Edit</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Interactive Dynamic Table Grid Panel (Upscaled Font Sizing) -->
            <div class="col-12 col-md-8 d-flex flex-column" style="height: 100%;">
                <div class="card p-3 shadow-sm d-flex flex-column" style="overflow: hidden;">
                    <h5 class="mb-3 fw-bold tracking-tight" style="letter-spacing: -0.3px; font-size: 1.25rem;">Current Budget Overviews</h5>
                    
                    <div class="adaptive-table-container flex-grow-1 min-height-0">
                        <table class="table table-hover align-middle mb-0 custom-dashboard-table">
                            <thead>
                                <tr>
                                    <th style="width: 25%; font-size: 0.95rem; font-weight: 700;">Category Name</th>
                                    <th style="width: 18%; font-size: 0.95rem; font-weight: 700;">Defined Limit</th>
                                    <th style="width: 18%; font-size: 0.95rem; font-weight: 700;">Total Spent</th>
                                    <th style="width: 24%; font-size: 0.95rem; font-weight: 700;">Budget Utilization</th>
                                    <th style="width: 15%; font-size: 0.95rem; font-weight: 700;">Status</th>
                                    <th class="text-end pe-3" style="width: 5%;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($budget_statuses)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <span class="d-block mb-1" style="font-size: 1.75rem;">📄</span>
                                            <small class="fw-medium" style="font-size: 1rem;">No active budget allocations created yet.</small>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($budget_statuses as $row): 
                                        $cat_id = (int)$row['category_id'];
                                        $limit = (float)$row['limit_amount'];
                                        $spent = isset($expenses[$cat_id]) ? $expenses[$cat_id] : 0.00;

                                        $percent = $limit > 0 ? ($spent / $limit) * 100 : 0;
                                        $bar_width = min($percent, 100); 

                                        if ($percent >= 100) {
                                            $bar_color = 'bg-danger';
                                            $spent_text_color = 'text-danger';
                                            $status_badge = '<span class="badge-custom badge-expense py-1 px-3 fw-bold" style="font-size: 0.85rem; border-radius: 6px;">Over Limit</span>';
                                        } elseif ($percent >= 75) {
                                            $bar_color = 'bg-warning';
                                            $spent_text_color = 'text-warning';
                                            $status_badge = '<span class="badge-custom py-1 px-3 fw-bold" style="background-color: var(--warning-bg); color: var(--warning); font-size: 0.85rem; border-radius: 6px;">Warning</span>';
                                        } else {
                                            $bar_color = 'bg-success';
                                            $spent_text_color = 'text-success';
                                            $status_badge = '<span class="badge-custom badge-income py-1 px-3 fw-bold" style="font-size: 0.85rem; border-radius: 6px;">On Track</span>';
                                        }
                                    ?>
                                        <tr>
                                            <td class="text-main fw-bold" style="font-size: 1.05rem;">
                                                <?= htmlspecialchars($row['name']); ?>
                                            </td>
                                            <td class="text-nowrap fw-bold text-dark" style="font-size: 1.05rem;">
                                                RM <?= number_format($limit, 2); ?>
                                            </td>
                                            <td class="text-nowrap fw-bold <?= $spent_text_color; ?>" style="font-size: 1.05rem;">
                                                RM <?= number_format($spent, 2); ?>
                                            </td>
                                            
                                            <td>
                                                <div class="d-flex align-items-center justify-content-between mb-1 text-muted" style="font-size: 0.85rem;">
                                                    <span class="fw-bold"><?= number_format($percent, 1); ?>% spent</span>
                                                </div>
                                                <div class="progress" style="height: 10px; background-color: #f1f3f5; border-radius: 100px;">
                                                    <div class="progress-bar <?= $bar_color; ?>" 
                                                         role="progressbar" 
                                                         style="width: <?= $bar_width; ?>%; border-radius: 100px;" 
                                                         aria-valuenow="<?= $bar_width; ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                <?= $status_badge; ?>
                                            </td>
                                            <td class="text-end pe-3">
                                                <a href="?edit_category_id=<?= $cat_id ?>&edit_limit=<?= $limit ?>" 
                                                   class="btn btn-sm btn-outline-primary py-1 px-3 fw-bold" style="font-size: 0.9rem; border-radius: 6px;">
                                                    Edit
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div> 
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>