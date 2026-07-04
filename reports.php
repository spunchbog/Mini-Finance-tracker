<?php
session_start();
include('db_connect.php');

// 1. DYNAMIC CHECK: Check if a user is actually logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);

if (empty($_SESSION['role'])) {
    $_SESSION['role'] = 'user';
}

// ==========================================
// 📊 STEP 1: TREND CALCULATION MODULE
// ==========================================
$this_month_start = date('Y-m-01 00:00:00');
$this_month_end   = date('Y-m-t 23:59:59');

$last_month_start = date('Y-m-d 00:00:00', strtotime('first day of last month'));
$last_month_end   = date('Y-m-d 23:59:59', strtotime('last day of last month'));

// Detect table and column structures dynamically to avoid crashes
$table_name = $pdo->query("SHOW TABLES LIKE 'transaction'")->fetch() ? "transaction" : "transactions";
$date_col   = $pdo->query("SHOW COLUMNS FROM `$table_name` LIKE 'transaction_date'")->fetch() ? "transaction_date" : "date";
$amt_col    = $pdo->query("SHOW COLUMNS FROM `$table_name` LIKE 'amt'")->fetch() ? "amt" : "amount";

function getNetBalance($pdo, $user_id, $start, $end, $table, $date, $amt) {
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN type = 'income' THEN `$amt` ELSE 0 END) - 
            SUM(CASE WHEN type = 'expense' THEN ABS(`$amt`) ELSE 0 END) as net_balance
        FROM `$table` 
        WHERE user_id = :user_id AND `$date` BETWEEN :start AND :end
    ");
    $stmt->execute([':user_id' => $user_id, ':start' => $start, ':end' => $end]);
    $row = $stmt->fetch();
    return (float)($row['net_balance'] ?? 0.00);
}

// Fetch balance calculations
$current_net  = getNetBalance($pdo, $user_id, $this_month_start, $this_month_end, $table_name, $date_col, $amt_col);
$previous_net = getNetBalance($pdo, $user_id, $last_month_start, $last_month_end, $table_name, $date_col, $amt_col);

// Calculate the differences
$diff = $current_net - $previous_net;

// === PERCENTAGE GROWTH ===
if ($previous_net != 0) {
    $percentage_change = (($current_net - $previous_net) / abs($previous_net)) * 100;
} else {
    $percentage_change = $current_net > 0 ? 100 : ($current_net < 0 ? -100 : 0);
}

// Generate the text string dynamically with structural highlights
if ($diff > 0) {
    $trend_color = 'text-success';
    $trend_bg = 'rgba(43, 138, 62, 0.07)';
    $trend_arrow = '▲'; 
    $trend_badge_text = 'Higher';
} elseif ($diff < 0) {
    $trend_color = 'text-danger';
    $trend_bg = 'rgba(201, 42, 42, 0.07)';
    $trend_arrow = '▼'; 
    $trend_badge_text = 'Lower';
} else {
    $trend_color = 'text-warning';
    $trend_bg = 'rgba(230, 73, 45, 0.07)';
    $trend_arrow = '■'; 
    $trend_badge_text = 'Flat';
}

// 4. Pull in variables, categories, and calculations scoped to this user
require_once 'data.php'; 
?>
<!DOCTYPE html>
<html lang="en">    
<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinTrack Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .custom-card {
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 12px;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .metric-label {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
        }
        .section-title {
            letter-spacing: -0.3px;
            font-weight: 700;
            color: #212529;
        }
        .trend-pill {
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 50px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
    </style>
</head>
<body>
    <div id="wrapper" class="d-flex vh-100 w-100" style="overflow: hidden;"> 
        <?php include 'sidebar.php'; ?>

        <div id="page-content-wrapper" class="flex-grow-1 d-flex flex-column p-4" style="overflow-y: auto;">
            <header class="mb-4" style="flex: 0 1 auto;">
                <h2 class="fw-bold mb-1">Reports</h2>
                <p class="text-muted mb-0 fw-medium" style="font-size: 0.95rem;"><?= date('l, d F Y') ?></p>
            </header>

            <div class="container-fluid px-0">
                <div class="row g-4">
                    
                    <div class="col-12">
                        <div class="card p-4 shadow-sm custom-card bg-white">
                            <h5 class="section-title mb-3" style="font-size: 1.2rem;">💡 Financial Insights</h5>
                            <div class="mb-3 fs-5 text-dark pb-3 border-bottom border-light-subtle d-flex flex-wrap align-items-center gap-2">
                                <span class="text-secondary fw-normal" style="font-size: 1.05rem;">Highest spending category this week:</span> 
                                <span class="badge bg-light text-dark border px-3 py-2 fw-bold" style="font-size: 1rem; border-radius: 8px;">
                                    <?= htmlspecialchars($highestSpendingCategory); ?>
                                </span>
                                <span class="text-danger fw-bold ms-auto px-1" style="font-size: 1.25rem;">
                                    RM <?= number_format($highestSpendingAmount, 2); ?>
                                </span>
                            </div>
                            <div class="text-secondary style-insight-line" style="line-height: 1.6; font-size: 0.95rem;">
                                <?= $insightMessage; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-md-6">
                        <div class="card h-100 p-4 shadow-sm custom-card bg-white d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h6 class="metric-label mb-0">Monthly Balance Trend</h6>
                                    <span class="trend-pill" style="background-color: <?= $trend_bg ?>; color: var(--bs-<?= str_replace('text-', '', $trend_color) ?>);">
                                        <?= $trend_arrow ?> <?= $trend_badge_text ?>
                                    </span>
                                </div>
                                <div class="display-5 fw-bold my-2 tracking-tight <?= $trend_color; ?>" style="letter-spacing: -1px;">
                                    <?= $trend_arrow ?> RM <?= number_format($current_net, 2); ?>
                                    <?php if($current_net < 0): ?><span class="fs-4 fw-medium text-muted ms-1">Net Deficit</span><?php endif; ?>
                                </div>
                            </div>
                            <p class="text-secondary mb-0 mt-3 pt-2 border-top border-light-subtle" style="font-size: 0.9rem; line-height: 1.5;">
                                <?php if ($diff >= 0): ?>
                                    You're keeping more cash! Your net savings are <strong class="<?= $trend_color; ?> fw-bold"><?= number_format(abs($percentage_change), 1); ?>% higher</strong> than last month's final total of <span class="fw-semibold text-dark">RM <?= number_format($previous_net, 2); ?></span>.
                                <?php else: ?>
                                    You're saving less this month! Your net savings are <strong class="<?= $trend_color; ?> fw-bold"><?= number_format(abs($percentage_change), 1); ?>% lower</strong> than last month's final total of <span class="fw-semibold text-dark">RM <?= number_format($previous_net, 2); ?></span>.
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="card h-100 p-4 shadow-sm custom-card bg-white d-flex flex-column justify-content-between">
                            <?php 
                                // Direct safety recalculation to bypass absolute hard-locks inside data.php
                                $income_stmt = $pdo->prepare("SELECT SUM(`$amt_col`) as total_income FROM `$table_name` WHERE user_id = :user_id AND type = 'income' AND `$date_col` BETWEEN :start AND :end");
                                $income_stmt->execute([':user_id' => $user_id, ':start' => $this_month_start, ':end' => $this_month_end]);
                                $this_month_income = (float)($income_stmt->fetch()['total_income'] ?? 0.00);

                                if ($this_month_income > 0) {
                                    $savingsRate = ($current_net / $this_month_income) * 100;
                                } else {
                                    $savingsRate = $current_net < 0 ? -100.0 : 0.0;
                                }

                                $rate_color = $savingsRate < 0 ? 'text-danger' : ($savingsRate >= 20 ? 'text-success' : 'text-warning');
                                $rate_bg = $savingsRate < 0 ? 'rgba(201, 42, 42, 0.05)' : ($savingsRate >= 20 ? 'rgba(43, 138, 62, 0.05)' : 'rgba(230, 73, 45, 0.05)');
                            ?>
                            <div>
                                <h6 class="metric-label mb-3">Monthly Net Savings Rate</h6>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="display-5 fw-bold tracking-tight <?= $rate_color ?>" style="letter-spacing: -1px;">
                                        <?= number_format($savingsRate, 1); ?>%
                                    </div>
                                    <div class="progress flex-grow-1" style="height: 10px; background-color: #f1f3f5; border-radius: 100px;">
                                        <div class="progress-bar <?= $savingsRate < 0 ? 'bg-danger' : ($savingsRate >= 20 ? 'bg-success' : 'bg-warning') ?>" 
                                             role="progressbar" 
                                             style="width: <?= max(0, min($savingsRate, 100)) ?>%; border-radius: 100px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-3 mt-3 rounded-3" style="background-color: <?= $rate_bg ?>; border: 1px solid rgba(0,0,0,0.02);">
                                <p class="mb-0 fw-medium <?= $rate_color ?>" style="font-size: 0.9rem;">
                                    <?php 
                                    if ($savingsRate >= 20) {
                                        echo "✨ Great job! You're saving more than 20% of your income this month.";
                                    } elseif ($savingsRate > 0) {
                                        echo "⚠️ You're saving money, but a few small cuts could help you hit your 20% goal.";
                                    } elseif ($savingsRate < 0) {
                                        echo "⚠️ You're spending more than you earn! Time to check your recent expenses.";
                                    } else {
                                        echo "Notice: You broke exactly even this month. No money saved or lost.";
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card p-4 shadow-sm custom-card bg-white">
                            <h5 class="section-title border-bottom pb-3 mb-4" style="font-size: 1.2rem;">📊 Monthly Burn Assessment</h5>
                            <div class="row align-items-stretch g-4">
                                <div class="col-md-4 text-center d-flex flex-column justify-content-center border-end-md">
                                    <div class="metric-label mb-1">Est. Runway Cushion</div>
                                    <h1 class="fw-bold <?= $runwayMonths < 3 ? 'text-danger' : ($runwayMonths < 6 ? 'text-warning' : 'text-success') ?> my-1" style="font-size: 2.5rem; letter-spacing: -1px;">
                                        <?= number_format($runwayMonths, 1) ?> <span class="fs-5 text-muted fw-medium">Months</span>
                                    </h1>
                                </div>
                                <div class="col-md-4 text-center d-flex flex-column justify-content-center border-end-md px-3">
                                    <div class="metric-label mb-1">Avg. Monthly Burn (Expenses)</div>
                                    <h2 class="fw-bold text-dark my-1" style="font-size: 2rem; letter-spacing: -0.5px;">RM <?= number_format($avgMonthlyBurnRate, 2) ?></h2>
                                </div>
                                <div class="col-md-4 text-center d-flex flex-column justify-content-center">
                                    <div class="metric-label mb-1">Lifetime Account Balance</div>
                                    <h2 class="fw-bold text-primary my-1" style="font-size: 2rem; letter-spacing: -0.5px;">RM <?= number_format($lifetimeBalance, 2) ?></h2>
                                </div>
                            </div>
                            <div class="mt-4 p-3 bg-light rounded-3 border-0 text-center fw-medium text-secondary" style="font-size: 0.9rem;">
                                🛡️ <?= $runwayMessage ?>
                            </div>
                        </div>
                    </div>

                </div> 
            </div> 
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>