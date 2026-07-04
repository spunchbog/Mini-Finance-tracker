<?php
session_start();
include('db_connect.php');

// 1. DYNAMIC CHECK: Check if a user is actually logged in
if (!isset($_SESSION['user_id'])) {
    // If no user session exists, kick them back to the login page so they can't sneak in
    header("Location: login.php");
    exit;
}

// 2. Grab the true, active logged-in User ID from the session matrix
$user_id = $_SESSION['user_id'];

// 3. Make sure the role matches their database entry (fallback to user if empty)
if (empty($_SESSION['role'])) {
    $_SESSION['role'] = 'user'; 
}


// ==========================================
// SECURE TRANSACTION DELETION HANDLER
// ==========================================
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Auto-detect table structure dynamically from your schema rules
    $table_name = $pdo->query("SHOW TABLES LIKE 'transaction'")->fetch() ? "transaction" : "transactions";
    $id_col     = $pdo->query("SHOW COLUMNS FROM `$table_name` LIKE 'transaction_id'")->fetch() ? "transaction_id" : "id";
    
    // Double check that the transaction actually belongs to the logged-in user before deleting!
    $stmt = $pdo->prepare("DELETE FROM `$table_name` WHERE `$id_col` = :id AND user_id = :user_id");
    $executed = $stmt->execute([
        ':id' => $delete_id,
        ':user_id' => $user_id
        ]);
        
        if ($executed) {
            // Refresh the page instantly to clear the query parameters from the URL string
            header("Location: dashboard.php?delete_success=1");
            exit;
            }
            }
require_once 'data.php'; // Pulls in timeline arrays, dropdown math, and card totals
?>





<!DOCTYPE html>
<html lang="en">    
<head>  
    <meta charset="UTF-8">
    <title>FinTrack Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div id="wrapper" class="d-flex vh-100 w-100" style="overflow: hidden;"> 
    <?php include 'sidebar.php'; ?>

    <div id="page-content-wrapper" class="flex-grow-1 d-flex flex-column p-4" style="overflow: hidden;">

        
        <header class="mb-4 d-flex justify-content-between align-items-center" style="flex: 0 1 auto;">
            
            <div>
                <h2 class="mb-0 fw">Dashboard</h2>
                <p class="text-muted small mb-0"><?= date('l, d M Y') ?></p>
            </div>
        
            <div class="dropdown">
                <button class="btn btn-white border shadow-sm dropdown-toggle fw-semibold" type="button" id="metricsFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    View: <?php 
                        $currentView = $_GET['view'] ?? 'monthly';
                        if ($currentView === 'weekly') echo 'This Week (Mon-Sun)';
                        elseif ($currentView === '7_days') echo 'Last 7 Days';
                        elseif ($currentView === '30_days') echo 'Last 30 Days';
                        elseif ($currentView === 'all') echo 'All-Time';
                        elseif ($currentView === 'custom') echo 'Custom Range';
                        else echo 'This Month';
                    ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="metricsFilterDropdown">
                    <li><a class="dropdown-menu-item dropdown-item <?= (!isset($_GET['view']) || $_GET['view'] === 'monthly') ? 'active' : '' ?>" href="?view=monthly">This Month</a></li>
                    <li><a class="dropdown-menu-item dropdown-item <?= ($_GET['view'] ?? '') === 'weekly' ? 'active' : '' ?>" href="?view=weekly">This Week (Mon-Sun)</a></li>
                    <li><a class="dropdown-menu-item dropdown-item <?= ($_GET['view'] ?? '') === '7_days' ? 'active' : '' ?>" href="?view=7_days">Last 7 Days</a></li>
                    <li><a class="dropdown-menu-item dropdown-item <?= ($_GET['view'] ?? '') === '30_days' ? 'active' : '' ?>" href="?view=30_days">Last 30 Days</a></li>
                    <li><a class="dropdown-menu-item dropdown-item <?= ($_GET['view'] ?? '') === 'all' ? 'active' : '' ?>" href="?view=all">All-Time</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-menu-item dropdown-item <?= ($_GET['view'] ?? '') === 'custom' ? 'active' : '' ?>" href="#" data-bs-toggle="modal" data-bs-target="#customDateModal">Custom Range...</a></li>
                </ul>
            </div>
        </header>
<div class="modal fade" id="customDateModal" tabindex="-1" aria-labelledby="customDateModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="customDateModalLabel">Select Custom Range</h5>
        <button type="button" class="btn-close" data-bs-shadow="none" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <form method="GET" action="" id="customDateForm">
        <input type="hidden" name="view" value="custom">
        <div class="modal-body py-4">
            
            <div id="dateErrorAlert" class="alert alert-danger d-none py-2 small fw-semibold mb-3">
                ⚠️ Start date must be earlier than or equal to the end date.
            </div>

            <div class="row">
                <div class="col-6">
                    <label class="form-label text-muted small fw-bold">START DATE</label>
                    <input type="date" id="startDateInput" name="start" class="form-control" value="<?= $_GET['start'] ?? date('Y-m-01') ?>" required>
                </div>
                <div class="col-6">
                    <label class="form-label text-muted small fw-bold">END DATE</label>
                    <input type="date" id="endDateInput" name="end" class="form-control" value="<?= $_GET['end'] ?? date('Y-m-d') ?>" required>
                </div>
            </div>
        </div>
        <div class="modal-footer bg-light border-top-0">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold">Apply Range</button>
        </div>
      </form>
    </div>
  </div>
</div>
        <!-- Top Row: Metric Cards (Stays at the top) -->
        
        <div class="row mb-3" style="flex: 0 1 auto;">
            <div class="col-md-4">
                <div class="card p-3 shadow-sm border-0" style="border-left: 5px solid var(--border) !important;">
                    <h6 class="text-muted">Total Balance</h6>
                    <h3>RM <?= number_format($currentBalance, 2) ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 shadow-sm border-0" style="border-left: 5px solid var(--safe) !important;">
                    <h6 class="text-muted">Total Income</h6>
                    <h3 style="color: var(--safe);">RM <?= number_format($totalIncome, 2) ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 shadow-sm border-0" style="border-left: 5px solid var(--danger) !important;">
                    <h6 class="text-muted">Total Expenses</h6>
                    <h3 style="color: var(--danger);">RM <?= number_format($totalExpense, 2) ?></h3>
                </div>
            </div>
        </div>

        <!-- Bottom Row: Split Screen (Fills the rest of the height) -->
        <div class="row flex-grow-1" style="min-height: 0;">
            <!-- Left Side: Two Charts stacked -->
            <div class="col-12 col-md-5 d-flex flex-column" style="gap: 15px;">
                <div class="card p-3 flex-grow-1 shadow-sm">
                <div class="d-flex justify-content-between">
                    <h6 class="mb-0">Cash Flow Overview</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-white border shadow-sm dropdown-toggle fw-semibold text-muted" type="button" id="chartTypeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            View: <span id="chartTypeLabel">Expense Only</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="chartTypeDropdown">
                            <li><a class="dropdown-item text-danger active chart-filter-item" href="#" data-chart-type="expense">Expense Only</a></li>
                            <li><a class="dropdown-item text-success chart-filter-item" href="#" data-chart-type="income">Income Only</a></li>
                            <li><a class="dropdown-item chart-filter-item" href="#" data-chart-type="all">All Types</a></li>
                        </ul>
                    </div>
                </div>
                    <div style="height: 200px; width: 100%; margin: 0 auto; position: relative; flex-grow: 1;">
                        <canvas id="spendingChart"></canvas>
                    </div>
                </div>
                <div class="card p-3 flex-grow-1 shadow-sm">
                    <h6>Monthly Trend</h6>
                    <div style="height: 200px; width: 100%; margin: 0 auto; position: relative; flex-grow: 1;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
<div class="col-12 col-md-7 d-flex flex-column" style="height: 100%;">
    <div class="card p-3 shadow-sm h-100 d-flex flex-column" style="overflow: hidden;">
        
        <?php if (isset($_GET['delete_success']) && $_GET['delete_success'] == 1): ?>
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                <span class="me-2">✅</span>
                <div class="fw-medium text-success" style="font-size: 0.85rem;">Transaction permanently deleted successfully!</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3 flex-shrink-0">
            <h5 class="mb-0 fw-bold tracking-tight" style="letter-spacing: -0.3px;">Recent Transactions</h5>
            
            <div class="d-flex align-items-center gap-2 style-wrapper" style="max-width: 440px; width: 100%;">
                <select id="tableTypeFilter" class="form-select form-select-sm fw-semibold text-muted shadow-sm ps-2" style="width: 130px; border-color: var(--border-subtle); border-radius: 8px;">
                    <option value="all">All Types</option>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                </select>
                <select id="tableCategoryFilter" class="form-select form-select-sm fw-semibold text-muted shadow-sm ps-2" style="width: 155px; border-color: var(--border-subtle); border-radius: 8px;">
                    <option value="all">All Categories</option>
                    <?php 
                    $uniqueCats = array_unique(array_column($transactions, 'cat'));
                    sort($uniqueCats);
                    foreach ($uniqueCats as $catName): 
                        if (!empty($catName)):
                    ?>
                        <option value="<?= htmlspecialchars(strtolower(trim($catName))) ?>"><?= htmlspecialchars($catName) ?></option>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </select>
                <div class="input-group shadow-sm flex-grow-1 custom-search-group">
                    <span class="input-group-text bg-white text-muted py-1 px-2">🔍</span>
                    <input type="text" id="tableSearchInput" class="form-control ps-0 fw-medium form-control-sm" placeholder="Search...">
                </div>
            </div>
        </div>

        <div class="adaptive-table-container flex-grow-1 min-height-0">
            <table class="table table-hover align-middle mb-0 custom-dashboard-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Category</th>
                        <th style="width: 35%;">Description</th>
                        <th style="width: 20%;">Amount</th>
                        <th style="width: 15%;">Date</th>
                        <th class="text-end pe-3" style="width: 5%;"></th>
                    </tr>
                </thead>
                <tbody id="transactionTableBody">
                    <?php if (!empty($transactions)): ?>
                        <?php 
                            foreach ($transactions as $trx): 
                                $trxTime = strtotime($trx['date']);
                                
                                if ($viewFilter === 'weekly') {
                                    if ($trxTime < $mondayThisWeek || $trxTime > $sundayThisWeek) continue;
                                } elseif ($viewFilter === '7_days') {
                                    if ($trxTime < $sevenDaysAgo || $trxTime > $todayCeiling) continue;
                                } elseif ($viewFilter === '30_days') {
                                    if ($trxTime < $thirtyDaysAgo || $trxTime > $todayCeiling) continue;
                                } elseif ($viewFilter === 'all') {
                                    if ($trxTime > $todayCeiling) continue;
                                } elseif ($viewFilter === 'custom') {
                                    if ($trxTime < $customStart || $trxTime > $customEnd) continue;
                                } else {
                                    if ($trxTime < $thisMonthStart || $trxTime > $thisMonthEnd) continue;
                                }
                                
                                $isIncome = ($trx['type'] === 'income');
                        ?>
                            <tr data-type="<?= htmlspecialchars($trx['type']) ?>" data-category="<?= htmlspecialchars(strtolower(trim($trx['cat']))) ?>">
                                <td class="trx-category">
                                    <span class="badge-custom <?= $isIncome ? 'badge-income' : 'badge-expense' ?>">
                                        <?= htmlspecialchars($trx['cat']) ?>
                                    </span>
                                </td>
                                <td class="trx-description text-main fw-medium text-truncate" style="max-width: 180px;">
                                    <?= htmlspecialchars($trx['description']) ?>
                                </td>
                                <td class="trx-type <?= $isIncome ? 'text-success' : 'text-danger' ?> fw-bold">
                                    <?= ($isIncome ? '+ ' : '- ') . 'RM ' . number_format(abs($trx['amt']), 2) ?>
                                </td>
                                <td class="text-muted small fw-medium">
                                    <?= date('M d, Y', strtotime($trx['date'])) ?>
                                </td>
                                <td class="text-end pe-3">
                                    <?php if (isset($trx['transaction_id'])): ?>
                                        <a href="?view=<?= htmlspecialchars($viewFilter ?? 'monthly') ?>&delete_id=<?= $trx['transaction_id'] ?>" 
                                           class="btn-delete-action" 
                                           onclick="return confirm('Are you sure you want to permanently delete this transaction?');" 
                                           title="Delete transaction">
                                            ✕
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <span class="d-block mb-1" style="font-size: 1.25rem;">📄</span>
                                <small class="fw-medium">No recent transactions tracked yet.</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
        </div>
    </div> 
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const trendCtx = document.getElementById('trendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'bar', 
    data: {
        labels: <?php echo json_encode($trendLabels); ?>,
        datasets: [{
            label: 'Income',
            data: <?php echo json_encode($finalTrendIncome); ?>,
            backgroundColor: '#88D38C',
            borderRadius: 5
        }, {
            label: 'Expenses',
            data: <?php echo json_encode($finalTrendExpense); ?>,
            backgroundColor: '#B71C1C',
            borderRadius: 5
        }]
    },
    options: {
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true, grid: { display: false }, ticks: { font: { size: 10 } } },
            x: { grid: { display: false }, ticks: { font: { size: 10 } } }
        },
        plugins: {
            legend: { display: true, position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
        }
    }
});
</script>

<script>
const ctx = document.getElementById('spendingChart').getContext('2d');
// 1. Create the custom plugin definition
window.spendingChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($chartLabels); ?>,
        datasets: [{
            data: <?php echo json_encode($chartValues); ?>,
            backgroundColor: [
                            '#4e73df', // Original Royal Blue
                            '#1cc88a', // Original Mint Green
                            '#36b9cc', // Original Teal Blue
                            '#f6c23e', // Original Warm Yellow
                            '#e74a3b', // Original Coral Red
                            '#858796', // Original Slate Gray
                            '#9b5de5', // Rich Violet
                            '#f15bb5', // Hot Pink
                            '#ff9f1c', // Deep Orange
                            '#00b4d8', // Vivid Sky Blue
                            '#52b788', // Forest Green
                            '#748cab'  // Steel Blue
                        ],
            hoverOffset: 10,
            borderWidth: 2,
            borderColor: '#ffffff'
        }]
    },
    options: {
        responsive: true,
        animation: {
        animateRotate: true,  // Spins the doughnut slices on load
        animateScale: true,   // Fades them in from the center
        duration: 1000        // Smooth entry speed (in milliseconds)
    },
        maintainAspectRatio: false,
        cutout: '75%', 
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let rawValue = context.raw;
                        let total = context.dataset.data.reduce((sum, value) => sum + Number(value), 0);
                        let percentage = ((rawValue / total) * 100).toFixed(1);
                        return context.label + ': RM' + rawValue + ' (' + percentage + '%)';
                    }
                }
            },
            legend: {
                display: true,
                position: 'bottom',
                labels: { usePointStyle: true, padding: 20, font: { size: 12 } }
            }
        },
        animation: { duration: 1500, animateRotate: true, animateScale: false }
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('tableSearchInput');
    const typeFilter = document.getElementById('tableTypeFilter');
    const catFilter = document.getElementById('tableCategoryFilter');
    const tableBody = document.getElementById('transactionTableBody');
    
    // Check elements first
    if (tableBody && searchInput && typeFilter && catFilter) {
        const tableRows = tableBody.getElementsByTagName('tr');

        // FIXED PLACE: Safely initialized inside the functional matrix block
        let activeChartType = 'expense';

        // === ENGINE A: INDEPENDENT CHART UPDATER ===
        function updateChartOnly() {
            const filteredChartData = {};

            for (let i = 0; i < tableRows.length; i++) {
                const row = tableRows[i];
                if (row.cells.length === 1 && row.cells[0].getAttribute('colspan')) continue;
                
                const rowType = row.getAttribute('data-type') ? row.getAttribute('data-type').toLowerCase().trim() : '';
                const catElement = row.querySelector('.trx-category');
                const amtElement = row.querySelector('.trx-type');

                // Filter logic based ONLY on dropdown selection matrix
                const matchesChartType = (activeChartType === 'all') || (rowType === activeChartType);

                if (matchesChartType && amtElement && catElement) {
                    const rawCatName = catElement.textContent.trim();
                    const cleanAmt = amtElement.textContent.replace(/[+\-RM\s,]/g, '');
                    const parsedValue = parseFloat(cleanAmt) || 0;

                    if (!filteredChartData[rawCatName]) {
                        filteredChartData[rawCatName] = 0;
                    }
                    filteredChartData[rawCatName] += parsedValue;
                }
            }

            if (typeof spendingChart !== 'undefined') {
                spendingChart.data.labels = Object.keys(filteredChartData);
                spendingChart.data.datasets[0].data = Object.values(filteredChartData);
                spendingChart.update(); 
            }
        }

        // === ENGINE B: TABLE ONLY VIEW CONTROLLER ===
        function filterTable() {
            const textKeyword = searchInput.value.toLowerCase().trim();
            // Create a secondary keyword variation that strips symbols for effortless number hunting
            const numericKeyword = textKeyword.replace(/[+\-rm\s,]/g, ''); 

            const selectedType = typeFilter.value.toLowerCase();
            const selectedCat  = catFilter.value.toLowerCase();

            for (let i = 0; i < tableRows.length; i++) {
                const row = tableRows[i];
                if (row.cells.length === 1 && row.cells[0].getAttribute('colspan')) continue;
                
                const rowType = row.getAttribute('data-type') ? row.getAttribute('data-type').toLowerCase().trim() : '';
                const rowCat  = row.getAttribute('data-category') ? row.getAttribute('data-category').toLowerCase().trim() : '';
                
                const descElement = row.querySelector('.trx-description');
                const catElement  = row.querySelector('.trx-category');
                const amtElement  = row.querySelector('.trx-type');
                // Targets the small/muted text cell inside the 4th column slot
                const dateElement = row.cells[3]; 
                
                const descText = descElement ? descElement.textContent.toLowerCase() : '';
                const catText  = catElement ? catElement.textContent.toLowerCase() : '';
                const dateText = dateElement ? dateElement.textContent.toLowerCase() : '';
                
                // Get raw amount text and clean it for numeric-only matching
                const amtText  = amtElement ? amtElement.textContent.toLowerCase() : '';
                const cleanAmtText = amtText.replace(/[+\-rm\s,]/g, '');

                // Updated to evaluate description, category, formatted date, or amount fields
                const matchesSearch = descText.includes(textKeyword) || 
                                      catText.includes(textKeyword) || 
                                      dateText.includes(textKeyword) ||
                                      (numericKeyword !== '' && cleanAmtText.includes(numericKeyword));

                const matchesType = (selectedType === 'all') || (rowType === selectedType);
                const matchesCategory = (selectedCat === 'all') || (rowCat === selectedCat);

                if (matchesSearch && matchesType && matchesCategory) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }

        // === ENGINE C: CHART DROPDOWN EVENT HANDLER ===
        document.querySelectorAll('.chart-filter-item').forEach(item => {
            item.addEventListener('click', function (e) {
                e.preventDefault();

                document.querySelectorAll('.chart-filter-item').forEach(el => el.classList.remove('active'));
                this.classList.add('active');

                activeChartType = this.getAttribute('data-chart-type');
                document.getElementById('chartTypeLabel').innerText = this.textContent.trim();

                // Triggers structural calculation for the chart without touching table displays
                updateChartOnly();
            });
        });

        // Event listeners restricted purely to ledger DOM changes
        searchInput.addEventListener('keyup', filterTable);
        typeFilter.addEventListener('change', filterTable);
        catFilter.addEventListener('change', filterTable);
        
        // Run once on document ready to parse the initial 'expense' setting smoothly
        updateChartOnly();
    }
});
</script>
<script>
document.getElementById('customDateForm').addEventListener('submit', function(e) {
    const startDate = document.getElementById('startDateInput').value;
    const endDate = document.getElementById('endDateInput').value;
    const alertBox = document.getElementById('dateErrorAlert');

    // Compare date values sequentially
    if (startDate > endDate) {
        e.preventDefault(); // Stop form from submitting and refreshing the page
        alertBox.classList.remove('d-none'); // Reveal the Bootstrap alert box
    } else {
        alertBox.classList.add('d-none'); // Safe structure, keep hidden
    }
});
</script>
