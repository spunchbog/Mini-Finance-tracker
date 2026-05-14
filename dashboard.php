<?php

session_start();
require_once 'data.php'; // Pulls in all your variables and calculations

// Top of dashboard.php
$currentMonth = "May 2026";
// Your data "Source of Truth"
$monthlySpending = [
    'labels' => ['Food', 'Transport', 'Entertainment'],
    'data' => [300, 1200, 500]
];

$weeklySpending = [
    'labels' => ['Food', 'Transport', 'Entertainment'],
    'data' => [85, 40, 60]
];
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
    </div>

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
                    <h3>RM <?= number_format($totalIncome - $totalExpense, 2) ?></h3>
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
                    <h3 style="color: var(--danger);">RM <?= number_format($totalExpense, 2) ?></</h3>
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
                            <li><a class="dropdown-item" href="#" onclick="applyFilter('12_weeks', 'Last 12 Weeks')">Last 12 Weeks</a></li>
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
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $trx): ?>
                                    <tr>
                                        <td><?= $trx['name'] ?></td>
                                        <td class="<?= $trx['amt'] > 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= $trx['amt'] > 0 ? '+' : '-' ?> RM <?= number_format(abs($trx['amt']), 2) ?>
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
<!-- 3. Your Custom Dashboard Script -->
<!--
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('spendingChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'doughnut', 
        data: {
            labels: ['Food', 'Transport', 'Entertainment', 'Others'],
            datasets: [{
                data: [300, 150, 80, 50],
                backgroundColor: [
                    '#4e73df', // Blue
                    '#1cc88a', // Green
                    '#f6c23e', // Yellow
                    '#e74a3b'  // Red
                ],
                hoverOffset: 10,
                borderWidth: 3,
                borderColor: '#ffffff'
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
				    
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true // Makes legend icons circles instead of boxes
                    }
                }
            },
			
            cutout: '70%' // Creates the hollow center
        },
        // Add this plugin block
        plugins: [{
            id: 'centerText',
            beforeDraw: function(chart) {
                const width = chart.width,
                      height = chart.height,
                      ctx = chart.ctx;

                ctx.restore();
                const fontSize = (height / 160).toFixed(2);
                ctx.font = fontSize + "em sans-serif";
                ctx.textBaseline = "middle";
                ctx.fillStyle = "#888888"; // Make sure this matches your UI theme

                const text = "RM 530", // Your total calculation
                      textX = Math.round((width - ctx.measureText(text).width) / 2),
                      textY = height / 2-16;

                ctx.fillText(text, textX, textY);
                ctx.save();
            }
        }]
    });
});

</script>   
-->
<!--
<script>
// PHP injects the data here
    const spendingData = {
        monthly: {
            labels: //<?php echo json_encode($monthlySpending['labels']); ?>,
            data: <?php echo json_encode($monthlySpending['data']); ?>
        },
        weekly: {
            labels: <?php echo json_encode($weeklySpending['labels']); ?>,
            data: <?php echo json_encode($weeklySpending['data']); ?>
        }
    };

    // Initialize the Doughnut Chart
    const ctx = document.getElementById('spendingChart').getContext('2d');
    const spendingChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: spendingData.monthly.labels,
            datasets: [{
                data: spendingData.monthly.data,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
			    hoverOffset: 10,
                borderWidth: 3,
                borderColor: '#ffffff'
            }],
        },
        options: {
            maintainAspectRatio: false,
			cutout: '70%',
            plugins: {
                legend: {
                    onClick: (e) => e.stopPropagation(), // This "kills" the click event
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true // Makes legend icons circles instead of boxes
                    }
                }
            }
        },
		        // Add this plugin block
        plugins: [{
            id: 'centerText',
            beforeDraw: function(chart) {
                const width = chart.width,
                      height = chart.height,
                      ctx = chart.ctx;

                ctx.restore();
        
                // 1. Calculate the total dynamically from the current dataset
                const activeData = chart.config.data.datasets[0].data;
                const total = activeData.reduce((a, b) => a + b, 0);
                const text = "RM " + total.toLocaleString(); // Formats 1000 as 1,000

                const fontSize = (height / 160).toFixed(2);
                ctx.font = `${fontSize}em sans-serif`; // Added bold for a more modern look
                ctx.textBaseline = "middle";
                ctx.fillStyle = "#676767"; 

                const textX = Math.round((width - ctx.measureText(text).width) / 2),
                      textY = height / 2-16;
                ctx.fillText(text, textX, textY);
                ctx.save();
            }
        }]
            });


    // Function called by your dropdown buttons
    function updateDoughnut(view, labelText) {
        // Update data and labels
        spendingChart.data.labels = spendingData[view].labels;
        spendingChart.data.datasets[0].data = spendingData[view].data;
            
        // Re-render chart with animation
        spendingChart.update();

        // Update the dropdown button text
        const btn = document.getElementById('spendingToggle');
        btn.innerHTML = `${labelText} <i class="bi bi-chevron-down" style="font-size: 0.6rem;"></i>`;
    }
</script>
-->
<script>
const trendCtx = document.getElementById('trendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'bar', // Or 'line'
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr'],
        datasets: [{
            label: 'Income',
            data: [1100, 1200, 1000, 1200],
            backgroundColor: '#88D38C' // --safe color 
        }, {
            label: 'Expenses',
            data: [600, 530, 800, 450],
            backgroundColor: '#B71C1C' // --danger color
        }]
    },
    options: {
        maintainAspectRatio: false,
        layout: {
            padding: 5 // Reduces the "white space" inside the canvas
        },
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    boxWidth: 10, // Smaller legend icons
                    font: { size: 10 } // Smaller legend text
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