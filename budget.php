<?php
session_start();
include('db_connect.php'); // Assumes this initializes your $pdo connection object

// 1. DYNAMIC CHECK: Ensure a user is actually logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// FIXED: Remove the hardcoded 1111 override and cast securely
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
        $msg = "<p style='color:green; font-weight:bold;'>Budget updated successfully!</p>";
    } else {
        $msg = "<p style='color:red; font-weight:bold;'>Error saving budget configuration parameters.</p>";
    }
}

// 3. FETCH ALL AVAILABLE CATEGORIES FOR THE DROPDOWN SELECTOR
$categories_stmt = $pdo->query("SELECT category_id, name FROM category ORDER BY name ASC");
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

// Securely pool sums up inside database engine before extraction array mapping
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
    <title>FinTrack - Monthly Budgeting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div id="wrapper" class="d-flex vh-100 w-100" style="overflow: hidden;"> 
    <?php include 'sidebar.php'; ?>

    <div id="page-content-wrapper" class="flex-grow-1 d-flex flex-column p-4" style="overflow-y: auto;">
        <header class="mb-3">
            <h3>Monthly Budgeting</h3>
        </header>
        <p>Establish specific spending limits for various categories to maintain control over finances.</p>

        <?php if (!empty($msg)) echo $msg; ?>

        <form action='' method='POST' class="mb-4">
            <table border='0'>
                <tr>
                    <td>Select Category:</td>
                    <td>
                        <select name="category_id" required>
                            <option value="">-- Choose Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Limit Amount (RM):</td>
                    <td><input type="number" step="0.01" name="limit_amount" required placeholder="e.g. 300.00"></td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <br>
                        <input type="submit" name="set_budget" value="Save Budget Setup">
                    </td>
                </tr>
            </table>
        </form>

        <hr>

        <h4>Current Budget Overviews</h4>
        <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse; margin-top:10px;">
            <thead style="background-color: #f2f2f2;">
                <tr>
                    <th>Category Name</th>
                    <th>Defined Budget Limit</th>
                    <th>Total Spent (This Month)</th>
                    <th>Status Assessment</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($budget_statuses)): ?>
                    <tr><td colspan="4" align="center">No active budget allocations created yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($budget_statuses as $row): 
                        $cat_id = (int)$row['category_id'];
                        $limit = (float)$row['limit_amount'];
                        $spent = isset($expenses[$cat_id]) ? $expenses[$cat_id] : 0.00;
                        $over_budget = $spent > $limit;
                    ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                            <td>RM <?php echo number_format($limit, 2); ?></td>
                            <td>RM <?php echo number_format($spent, 2); ?></td>
                            <td>
                                <?php if ($over_budget): ?>
                                    <span style="color:red; font-weight:bold;">⚠️ Over Budget Threshold</span>
                                <?php else: ?>
                                    <span style="color:green; font-weight:bold;">✅ Within Limit Safezone</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>