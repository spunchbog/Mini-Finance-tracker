<?php
session_start();
include('db_connect.php');

// Redirect protection guardrail
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
// TEMPORARY: Force User 1
$user_id = 1111;
$msg = "";

// 1. HANDLE BUDGET SUBMISSION
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['set_budget'])) {
    $category_id = (int)$_POST['category_id'];
    $limit_amount = (float)$_POST['limit_amount'];

    // Check if a budget limit already exists for this user and category combination
    $check_exist = mysqli_query($conn, "SELECT budget_id FROM budget WHERE user_id = $user_id AND category_id = $category_id LIMIT 1");
    
    if (mysqli_num_rows($check_exist) > 0) {
        // Update the existing limit configuration
        $save_query = "UPDATE budget SET limit_amount = $limit_amount WHERE user_id = $user_id AND category_id = $category_id";
    } else {
        // Insert a clean new budget threshold row
        $save_query = "INSERT INTO budget (user_id, category_id, limit_amount) VALUES ($user_id, $category_id, $limit_amount)";
    }
    
    if (mysqli_query($conn, $save_query)) {
        $msg = "<p style='color:green; font-weight:bold;'>Budget updated successfully!</p>";
    } else {
        $msg = "<p style='color:red; font-weight:bold;'>Error saving budget: " . mysqli_error($conn) . "</p>";
    }
}

// 2. FETCH ALL AVAILABLE CATEGORIES FOR THE DROPDOWN SELECTOR (FIXED: Using 'name')
$categories_res = mysqli_query($conn, "SELECT category_id, name FROM category"); 

// 3. FETCH CURRENT MONTH'S EXPENSES BY CATEGORY ID
$current_month_start = date('Y-m-01 00:00:00');
$current_month_end = date('Y-m-t 23:59:59');

$expenses = [];

// Determine if the table is singular or plural
$table_name = "transaction"; 
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'transaction'");
if (mysqli_num_rows($table_check) == 0) {
    $table_name = "transactions"; 
}

// DYNAMIC COLUMN DETECTOR: Find out what your date column is actually named
$date_column = "date"; // Default guess
$column_check = mysqli_query($conn, "SHOW COLUMNS FROM `$table_name` LIKE 'transaction_date'");
if (mysqli_num_rows($column_check) > 0) {
    $date_column = "transaction_date"; // Switch to transaction_date if it exists
}

// DYNAMIC COLUMN DETECTOR: Find out what your amount column is named (amount vs amt)
$amount_column = "amount"; // Default guess
$amt_check = mysqli_query($conn, "SHOW COLUMNS FROM `$table_name` LIKE 'amt'");
if (mysqli_num_rows($amt_check) > 0) {
    $amount_column = "amt";
}

// Build the query dynamically using the columns we discovered
$expense_query = "SELECT category_id, SUM(ABS(`$amount_column`)) as total_spent 
                  FROM `$table_name` 
                  WHERE user_id = $user_id 
                    AND type = 'expense' 
                    AND `$date_column` BETWEEN '$current_month_start' AND '$current_month_end'
                  GROUP BY category_id";

$expense_res = mysqli_query($conn, $expense_query);

if ($expense_res) {
    while ($row = mysqli_fetch_assoc($expense_res)) {
        $expenses[(int)$row['category_id']] = (float)$row['total_spent'];
    }
} else {
    // Elegant fallback hook if something else breaks
    echo "<p style='color:orange;'>Notice: Could not track real-time monthly expenses: " . mysqli_error($conn) . "</p>";
}

// 4. FETCH CONFIGURED BUDGET STATUS WITH JOINED CATEGORY NAMES (FIXED: Using c.name)
$budget_status_query = "SELECT b.category_id, b.limit_amount, c.name 
                        FROM budget b 
                        JOIN category c ON b.category_id = c.category_id 
                        WHERE b.user_id = $user_id";
$budget_status_res = mysqli_query($conn, $budget_status_query);
?>

<!DOCTYPE html>
<html lang="en">    
<head>  
    <meta charset="UTF-8">
    <title>FinTrack - Monthly Budgeting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                            <?php if ($categories_res): ?>
                                <?php while($cat = mysqli_fetch_assoc($categories_res)): ?>
                                    <option value="<?php echo $cat['category_id']; ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
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
                <?php if (!$budget_status_res || mysqli_num_rows($budget_status_res) === 0): ?>
                    <tr><td colspan="4" align="center">No active budget allocations created yet.</td></tr>
                <?php else: ?>
                    <?php while ($row = mysqli_fetch_assoc($budget_status_res)): 
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
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>