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
      <form method="GET" action="">
        <input type="hidden" name="view" value="custom">
        <div class="modal-body py-4">
            <div class="row">
                <div class="col-6">
                    <label class="form-label text-muted small fw-bold">START DATE</label>
                    <input type="date" name="start" class="form-control" value="<?= $_GET['start'] ?? date('Y-m-01') ?>" required>
                </div>
                <div class="col-6">
                    <label class="form-label text-muted small fw-bold">END DATE</label>
                    <input type="date" name="end" class="form-control" value="<?= $_GET['end'] ?? date('Y-m-d') ?>" required>
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

                    <!--
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg" style="max-height: 300px; overflow-y: auto;">
                            <li><h6 class="dropdown-header">Standard</h6></li>
                            <li><a class="dropdown-item" href="#" onclick="applyFilter('today', 'Today')">Today</a></li>
                            <li><a class="dropdown-item" href="#" onclick="applyFilter('this_week', 'This Week')">This Week</a></li>
                            <li><a class="dropdown-item" href="#" onclick="applyFilter('this_month', 'This Month')">This Month</a></li>
                            <li><a class="dropdown-item" href="#" onclick="applyFilter('this_year', 'This Year')">This Year</a></li>
                
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">Trailing Periods</h6></li>
                            <li><a class="dropdown-item" href="#" onclick="applyFilter('7_days', 'Last 7 Days')">Last 7 Days</a></li>
                            <li><a class="dropdown-item" href="#" onclick="applyFilter('30_days', 'Last 30 Days')">Last 30 Days</a></li>
                            <li><a class="dropdown-item" href="#" onclick="applyFilter('3_months', 'Last 3 Months')">Last 3 Months</a></li>
                            <li><a class="dropdown-item" href="#" onclick="applyFilter('6_months', 'Last 6 Months')">Last 6 Months</a></li>
                            <li><a class="dropdown-item" href="#" onclick="applyFilter('1_year', 'Last 1 Year')">Last 1 Year</a></li>
                            <li><a class="dropdown-item" href="#" onclick="applyFilter('5_years', 'Last 5 Years')">Last 5 Years</a></li>
                
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-primary fw-bold" href="#" onclick="showCustomPicker()">Custom Range...</a></li>
                        </ul> -->
                    <!--
                <div id="customDateContainer" class="d-none bg-light p-2 rounded mb-3 border">
                        <div class="d-flex gap-2 align-items-center">
                            <input type="date" id="startDate" class="form-control form-control-sm">
                            <span class="small text-muted">to</span>
                            <input type="date" id="endDate" class="form-control form-control-sm">
                            <button class="btn btn-primary btn-sm" onclick="applyCustomRange()">Go</button>
                        </div>
                    </div> -->
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
<!-- Right Side: Recent Transactions Table -->
<div class="col-12 col-md-7 d-flex flex-column">
    <div class="card p-3 shadow-sm h-100" style="overflow: hidden;">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
            <h5 class="mb-0">Recent Transactions</h5>
            
            <div class="d-flex align-items-center gap-2 style-wrapper" style="max-width: 420px; width: 100%;">
                
                <select id="tableTypeFilter" class="form-select form-select-sm fw-semibold text-muted shadow-sm" style="width: 130px;">
                    <option value="all">All Types</option>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                </select>
                <select id="tableCategoryFilter" class="form-select form-select-sm fw-semibold text-muted shadow-sm" style="width: 135px;">
                    <option value="all">All Categories</option>
                    <?php 
                    // Dynamically extract unique categories present in your transaction stack
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

                <div class="input-group shadow-sm flex-grow-1">
                    <span class="input-group-text bg-white border-end-0 text-muted py-1 px-2">🔍</span>
                    <input type="text" id="tableSearchInput" class="form-control border-start-0 ps-0 fw-medium form-control-sm" placeholder="Search...">
                </div>
                
            </div>
        </div>



        
        <!-- CHANGED: Added max-height (e.g., 350px or 400px) so it triggers scrolling -->
        <div class="table-responsive" style="overflow-y: auto; max-height: 500px;">
            <table class="table table-hover align-middle mb-0">
                <!-- Added a solid background color to the header so text doesn't bleed through when scrolling underneath -->
                <thead class="table-light sticky-top" style="z-index: 10; background-color: #f8f9fa;">
                    <tr>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody id="transactionTableBody">
                    <?php if (!empty($transactions)): ?>
                        <?php 
                            foreach ($transactions as $trx): 
                                // 1. Convert the database timestamp into a comparable UNIX number
                                $trxTime = strtotime($trx['date']);
                                
                                // 2. RUN THE SAME EXACT DATE VALIDATION RULES AS THE HEADER CARDS
                                if ($viewFilter === 'weekly') {
                                    if ($trxTime < $mondayThisWeek || $trxTime > $sundayThisWeek) {
                                        continue; // Skip this row if it's out of bounds
                                    }
                                } elseif ($viewFilter === '7_days') {
                                    if ($trxTime < $sevenDaysAgo || $trxTime > $todayCeiling) {
                                        continue;
                                    }
                                } elseif ($viewFilter === '30_days') {
                                    if ($trxTime < $thirtyDaysAgo || $trxTime > $todayCeiling) {
                                        continue;
                                    }
                                } elseif ($viewFilter === 'all') {
                                    if ($trxTime > $todayCeiling) {
                                        continue;
                                    }
                                } elseif ($viewFilter === 'custom') {
                                    if ($trxTime < $customStart || $trxTime > $customEnd) {
                                        continue;
                                    }
                                } else {
                                    // Default: Monthly View
                                    if ($trxTime < $thisMonthStart || $trxTime > $thisMonthEnd) {
                                        continue;
                                    }
                                }
                            ?>
                            <tr data-type="<?= htmlspecialchars($trx['type']) ?>" data-category="<?= htmlspecialchars(strtolower(trim($trx['cat']))) ?>">                                <td class="trx-category"><?= htmlspecialchars($trx['cat']) ?></td>
                                <td class="trx-description"><?= htmlspecialchars($trx['description']) ?></td>
                                <td class="trx-type <?php echo ($trx['type'] === 'income') ? 'text-success fw-semibold' : 'text-danger fw-semibold'; ?>">
                                    <?php 
                                        $sign = ($trx['type'] === 'income') ? '+' : '-';
                                        echo $sign . ' RM ' . number_format(abs($trx['amt']), 2); 
                                    ?>
                                </td>
                                <td class="text-muted small">
                                    <?= date('M d, Y', strtotime($trx['date'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No recent transactions tracked yet.</td>
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
            const selectedType = typeFilter.value.toLowerCase();
            const selectedCat  = catFilter.value.toLowerCase();

            for (let i = 0; i < tableRows.length; i++) {
                const row = tableRows[i];
                if (row.cells.length === 1 && row.cells[0].getAttribute('colspan')) continue;
                
                const rowType = row.getAttribute('data-type') ? row.getAttribute('data-type').toLowerCase().trim() : '';
                const rowCat  = row.getAttribute('data-category') ? row.getAttribute('data-category').toLowerCase().trim() : '';
                
                const descElement = row.querySelector('.trx-description');
                const catElement  = row.querySelector('.trx-category');
                
                const descText = descElement ? descElement.textContent.toLowerCase() : '';
                const catText  = catElement ? catElement.textContent.toLowerCase() : '';

                const matchesSearch = descText.includes(textKeyword) || catText.includes(textKeyword);
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