<?php
session_start();
include('db_connect.php');

// TEMPORARY: Force User 1
$userid = $_SESSION['user_id'];

// Check if setup is done
$check = mysqli_query($conn, "SELECT setup_complete FROM user WHERE user_id = $user_id");
$user = mysqli_fetch_assoc($check);



require_once 'data.php'; // pulls in variables and calculations
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

    <!-- Main Content Area -->
    <div id="page-content-wrapper" class="flex-grow-1 d-flex flex-column p-4" style="overflow: hidden;">
        <header class="mb-3" style="flex: 0 1 auto;">
            <h2>Dashboard</h2>
            <p class="text-muted small mb-0"><?= date('l, d M Y') ?></p>
        </header>

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
                    <h6 class="mb-0">Spending Overview</h6>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 dropdown-toggle shadow-sm"
                                type="button" id="rangeToggle" data-bs-toggle="dropdown">
                            This Month
                        </button>
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
                        </ul>
					</div>
                
                <div id="customDateContainer" class="d-none bg-light p-2 rounded mb-3 border">
                        <div class="d-flex gap-2 align-items-center">
                            <input type="date" id="startDate" class="form-control form-control-sm">
                            <span class="small text-muted">to</span>
                            <input type="date" id="endDate" class="form-control form-control-sm">
                            <button class="btn btn-primary btn-sm" onclick="applyCustomRange()">Go</button>
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

            <!-- Right Side: Recent Transactions Table -->
            <div class="col-12 col-md-7 d-flex flex-column">
                <div class="card p-3 shadow-sm h-100" style="overflow: hidden;">
                    <h5 class="mb-3">Recent Transactions</h5>
                    <div class="table-responsive" style="overflow-y: auto;">
                        <table class="table table-hover align-middle">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $trx): ?>
                                    <tr>
                                        <td><?= $trx['cat'] ?></td>
                                        <td><?= $trx['description'] ?></td>
                                        <td class="<?php echo ($trx['type'] === 'income') ? 'text-success' : 'text-danger'; ?>">
                                            <?php 
                                                // Determine the sign based on type
                                                $sign = ($trx['type'] === 'income') ? '+' : '-';
                
                                                // Echo the sign followed by the absolute amount
                                                echo $sign . ' RM ' . number_format(abs($trx['amt']), 2); 
                                            ?>
                                        </td>
                                        <td class="text-muted small">
                                            <?= date('M d, Y', strtotime($trx['date'])) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div> 
</div>
<!-- 1. Bootstrap Bundle (Essential for the dropdown to function) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- 2. Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const trendCtx = document.getElementById('trendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'bar', 
    data: {
        // Labels from PHP: ['Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May']
        labels: <?php echo json_encode($trendLabels); ?>,
        datasets: [{
            label: 'Income',
            data: <?php echo json_encode($finalTrendIncome); ?>,
            backgroundColor: '#88D38C',
            borderRadius: 5 // Makes the bars look modern
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
            y: {
                beginAtZero: true,
                grid: { display: false }, // Cleaner look
                ticks: { font: { size: 10 } }
            },
            x: {
                grid: { display: false },
                ticks: { font: { size: 10 } }
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    boxWidth: 10,
                    font: { size: 10 }
                }
            }
        }
    }
});
</script>
<script>
    function applyFilter(range, label) {
        // Update the button text
        document.getElementById('rangeToggle').innerText = label;
    
        // Get the data for the range selected (e.g., '7_days')
        const newData = allSpendingData[range];

        if (newData) {
            // Update the labels and data points
            spendingChart.data.labels = newData.labels;
            spendingChart.data.datasets[0].data = newData.data;
        
            // Redraw the chart with a smooth animation
            spendingChart.update();
        }
    }
</script>
<script>
// 1. Pull the pre-calculated data from data.php
const allSpendingData = <?php echo json_encode($finalChartData); ?>;

// 2. Setup the Initial View (Default to 'this_month')
const initialData = allSpendingData['this_month'];

const ctx = document.getElementById('spendingChart').getContext('2d');

// 3. Create the Chart Instance
const spendingChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: initialData.labels,
        datasets: [{
            data: initialData.data,
            backgroundColor: [
                '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'
            ],
            hoverOffset: 10,
            borderWidth: 2,
            borderColor: '#ffffff'
        }]
    },
    options: {
        maintainAspectRatio: false,
        cutout: '75%', // This creates the "hole" in the middle
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    padding: 20,
                    font: { size: 12 }
                }
            }
        }
    }
});
</script>
</body>