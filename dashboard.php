<?php

session_start();
// Top of dashboard.php
$currentMonth = "May 2026";
// Your data "Source of Truth"
$monthlySpending = [
    'labels' => ['Food', 'Rent', 'Bills'],
    'data' => [300, 1200, 500]
];

$weeklySpending = [
    'labels' => ['Groceries', 'Coffee', 'Fuel'],
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
        </header>

        <!-- Top Row: Metric Cards (Stays at the top) -->
        <div class="row mb-3" style="flex: 0 1 auto;">
            <div class="col-md-4">
                <div class="card p-3 shadow-sm border-0" style="border-left: 5px solid var(--border) !important;">
                    <h6 class="text-muted">Total Balance</h6>
                    <h3>RM 670</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 shadow-sm border-0" style="border-left: 5px solid var(--safe) !important;">
                    <h6 class="text-muted">Total Income</h6>
                    <h3 style="color: var(--safe);">RM 1,200</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 shadow-sm border-0" style="border-left: 5px solid var(--danger) !important;">
                    <h6 class="text-muted">Total Expenses</h6>
                    <h3 style="color: var(--danger);">RM 530</h3>
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
						<button class="btn btn-light btn-sm rounded-pill px-3 dropdown-toggle shadow-sm"
								type="button" id="spendingToggle" data-bs-toggle="dropdown" aria-expanded="false">
							Monthly
						</button>
						<ul class="dropdown-menu dropdown-menu-end border-0 shadow" aria-labelledby="spendingToggle">
							<li>
								<a class="dropdown-item" href="#" onclick="updateDoughnut('weekly', 'Weekly')">Weekly View</a>
							</li>
							<li>
								<a class="dropdown-item" href="#" onclick="updateDoughnut('monthly', 'Monthly')">Monthly View</a>
							</li>
						</ul>
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
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>2026-04-20</td><td>Part-time Job</td><td style="color: var(--safe);">+ RM 1,200</td></tr>
                                <tr><td>2026-04-21</td><td>Starbucks</td><td style="color: var(--danger);">- RM 25</td></tr>
                                <tr><td>2026-04-21</td><td>Grocery</td><td style="color: var(--danger);">- RM 150</td></tr>
                                <!-- Add more rows here to test the scroll -->
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
<script>
// PHP injects the data here
    const spendingData = {
        monthly: {
            labels: <?php echo json_encode($monthlySpending['labels']); ?>,
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

</body>