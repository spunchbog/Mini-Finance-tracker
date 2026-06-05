<!-- Permanent Sidebar -->
<div id="sidebar-wrapper" class="p-3 text-white shadow" style="width: 220px; flex-shrink: 0; background-color: var(--safe);">
    <h4 class="ps-2">FinTrack</h4>
    <hr>
    <div class="list-group list-group-flush">
        <?php if (!empty($_SESSION['role'])) { ?>
            <?php if ($_SESSION['role'] == "admin") { ?>
                <a href='index.php' class="nav-link text-white py-3">Homepage</a>
                <a href='user-management.php' class="nav-link text-white py-3">User Management</a>
                <a href='category.php' class="nav-link text-white py-3">Category Manager</a>
            <?php } elseif ($_SESSION['role'] == "user") { ?>
                <a href='index.php' class="nav-link text-white py-3">Homepage</a>
                <a href='dashboard.php' class="nav-link text-white py-3">Dashboard</a>
                <a href='transaction.php' class="nav-link text-white py-3">Transactions</a>
                <a href='budget.php' class="nav-link text-white py-3">Budgets</a>
                <a href='reports.php' class="nav-link text-white py-3">Reports</a>
            <?php } ?>
            <a href='logout.php' class="nav-link text-white py-3">Logout</a>
        <?php } else { ?>
            <a href='index.php' class="nav-link text-white py-3">Home</a>
            <a href='login.php' class="nav-link text-white py-3">Login</a>
            <a href='signup.php' class="nav-link text-white py-3">Sign Up</a>
            <a href='transaction.php' class="nav-link text-white py-3">Transactions</a>
            <a href='dashboard.php' class="nav-link text-white py-3">Dashboard</a>
            <a href='reports.php' class="nav-link text-white py-3">Reports</a>
        <?php } ?>
    </div>
</div> 
