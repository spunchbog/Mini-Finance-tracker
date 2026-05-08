<?php
$deadline = "2026-10-10 23:59:59"; 
?>

<h1 align='center'>FinTrack - Personal Finance & Expense Tracker</h1>
<hr>

<?php if (!empty($_SESSION['role'])) { ?>
    <?php if ($_SESSION['role'] == "admin") { ?>
        | <a href='index.php'>Homepage</a>
        | <a href='user-management.php'>User Management</a>
        | <a href='category.php'>Category Manager</a>
    <?php } elseif ($_SESSION['role'] == "user") { ?>
        | <a href='index.php'>Homepage</a>
        | <a href='dashboard.php'>Dashboard</a>
        | <a href='transaction.php'>Transactions</a>
        | <a href='budget.php'>Budgets</a>
        | <a href='reports.php'>Reports</a>
    <?php } ?>
    | <a href='logout.php'>Logout</a>
<?php } else { ?>
    | <a href='index.php'>Home</a>
    | <a href='login.php'>Login</a>
    | <a href='signup.php'>Sign Up</a>
    | <a href='transaction.php'>Transactions</a>
<?php } ?>
<hr>
