<?php
// FinTrack Project: Global Header

?>

<h1 align='center'>FinTrack 2026 - Personal Finance Manager</h1>
<hr>

<?php if (!empty($_SESSION['role'])) { ?>
    <?php if ($_SESSION['role'] == "admin") { ?>
        | <a href='user.php'> User Management </a>
        | <a href='category.php'>Category Manager</a>
        
    <?php } elseif ($_SESSION['role'] == "user") { ?>
        | <a href='index.php'>Dashboard</a>
        | <a href='Transaction.php'>Transactions</a>
        | <a href='budget.php'>Budgets</a>
        | <a href='report.php'>Reports</a>
    <?php } ?>

    | <a href='logout.php'>Logout</a>

<?php } else { ?>
    | <a href='index.php'>Home</a>
    | <a href='login.php'>Login</a>
    | <a href='signup.php'>Register Account</a>

<?php } ?>
<hr>
