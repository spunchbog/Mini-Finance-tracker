<?php
// data.php
// In the future, this is where you'll put:
// $conn = mysqli_connect("localhost", "root", "", "fintrack");

// 1. Set the Timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

require_once 'db_connect.php';

// We join 'transaction' (t) and 'category' (c) 
// to replace the ID number with the actual category name.
$query = "
    SELECT 
        t.description AS description, 
        c.name AS cat, 
        t.date, 
        t.amount AS amt,
        t.type
    FROM transaction t
    INNER JOIN category c ON t.category_id = c.category_id
    ORDER BY t.date DESC
";

$stmt = $pdo->query($query);
$transactions = $stmt->fetchAll();

// Now the rest of your logic (Buckets, Thresholds, Calculations) 
// stays EXACTLY the same! It will loop through the DB data instead.

// 2. DEFINE TIME THRESHOLDS
$today          = strtotime('today');
$sevenDaysAgo   = strtotime('-7 days');
$thirtyDaysAgo  = strtotime('-30 days');
$ninetyDaysAgo  = strtotime('-90 days'); // 12 weeks
$sixMonthsAgo   = strtotime('-6 months');
$oneYearAgo     = strtotime('-1 year');
$fiveYearsAgo   = strtotime('-5 years');
$thisMonthStart = strtotime('first day of this month 00:00:00');
$thisYearStart  = strtotime('first day of January this year 00:00:00');

// 3. INITIALIZE BUCKETS (Using associative arrays to group by category)
$buckets = [
    'today'      => [],
    '7_days'     => [],
    '30_days'    => [],
    '12_weeks'   => [],
    '6_months'   => [],
    '1_year'     => [],
    '5_years'    => [],
    'this_month' => [],
    'this_year'  => []
];

// 4. THE CALCULATION ENGINE
foreach ($transactions as $trx) {
    $trxTime = strtotime($trx['date']);
    $amt = (float)$trx['amt'];
    $catName = $trx['cat']; // This comes from the 'category' table join

    // We only want to chart EXPENSES (negative amounts)
    if ($trx['type'] === 'expense') {
        
        // Helper logic to add to bucket if date matches
        if ($trxTime >= $today)          $buckets['today'][$catName]      = ($buckets['today'][$catName] ?? 0) + $amt;
        if ($trxTime >= $sevenDaysAgo)   $buckets['7_days'][$catName]     = ($buckets['7_days'][$catName] ?? 0) + $amt;
        if ($trxTime >= $thirtyDaysAgo)  $buckets['30_days'][$catName]    = ($buckets['30_days'][$catName] ?? 0) + $amt;
        if ($trxTime >= $ninetyDaysAgo)  $buckets['3_months'][$catName]   = ($buckets['3_months'][$catName] ?? 0) + $amt;
        if ($trxTime >= $sixMonthsAgo)   $buckets['6_months'][$catName]   = ($buckets['6_months'][$catName] ?? 0) + $amt;
        if ($trxTime >= $oneYearAgo)     $buckets['1_year'][$catName]     = ($buckets['1_year'][$catName] ?? 0) + $amt;
        if ($trxTime >= $fiveYearsAgo)   $buckets['5_years'][$catName]    = ($buckets['5_years'][$catName] ?? 0) + $amt;
        if ($trxTime >= $thisMonthStart) $buckets['this_month'][$catName] = ($buckets['this_month'][$catName] ?? 0) + $amt;
        if ($trxTime >= $thisYearStart)  $buckets['this_year'][$catName]  = ($buckets['this_year'][$catName] ?? 0) + $amt;
    }
}

// 5. FORMAT FOR CHART.JS
// Chart.js needs { labels: [], data: [] }. This step converts our grouped buckets into that format.
$finalChartData = [];
foreach ($buckets as $range => $categoryList) {
    $finalChartData[$range] = [
        'labels' => array_keys($categoryList),
        'data'   => array_values($categoryList)
    ];
}

// ==========================================
// 6. CALCULATE HEADER TOTALS (LOOP DRIVEN)
// ==========================================

$totalIncome = 0;
$totalExpense = 0;

// Read the user selection from the dropdown (default to monthly)
$viewFilter = isset($_GET['view']) ? $_GET['view'] : 'monthly';

// Calculate the precise timestamp for Monday of this week (for the "This Week" option)
$mondayThisWeek = strtotime('monday this week 00:00:00');

foreach ($transactions as $trx) {
    $trxTime = strtotime($trx['date']);
    $amount = (float)$trx['amt'];

    // Dynamic Filter Selector Logic
    if ($viewFilter === 'weekly') {
        // Strict Boundary: Current Calendar Week (Monday to Sunday)
        if ($trxTime < $mondayThisWeek) {
            continue;
        }
    } elseif ($viewFilter === '7_days') {
        // Rolling Boundary: Predefined last 7 continuous days
        if ($trxTime < $sevenDaysAgo) {
            continue;
        }
    } elseif ($viewFilter === '30_days') {
        // Rolling Boundary: Predefined last 30 continuous days
        if ($trxTime < $thirtyDaysAgo) {
            continue;
        }
    } else {
        // Monthly View (Default): Current Calendar Month
        if ($trxTime < $thisMonthStart) {
            continue;
        }
    }

    // Accumulate metrics if the row passed the timeline bounds filter check
    if ($trx['type'] === 'income') {
        $totalIncome += $amount;
    } elseif ($trx['type'] === 'expense') {
        $totalExpense += abs($amount);
    }
}

// Calculate total balance metrics live
$currentBalance = $totalIncome - $totalExpense;

// ====================================================================
// 1. DYNAMIC TREND TIMELINE GENERATOR (Starts from first real data)
// ====================================================================

$trendLabels = [];
$trendIncome = [];
$trendExpense = [];

if (!empty($transactions)) {
    // A. Find the oldest transaction date in your database array
    // Since your query orders by date DESC, the last item in the array is the oldest!
    $oldestTransaction = end($transactions);
    $startDateStr = $oldestTransaction['date'];
    
    // B. Create actual DateTime objects for the start month and the current month
    $startDateTime   = new DateTime(date('Y-m-01', strtotime($startDateStr)));
    $currentDateTime = new DateTime(date('Y-m-01')); // Today's month
    
    // C. Loop forward month-by-month from the first data point to today
    while ($startDateTime <= $currentDateTime) {
        $monthKey   = $startDateTime->format('Y-m'); // e.g., "2026-02"
        $monthLabel = $startDateTime->format('M');   // e.g., "Feb"
        
        $trendLabels[] = $monthLabel;
        $trendIncome[$monthKey] = 0;
        $trendExpense[$monthKey] = 0;
        
        // Move our loop pointer forward by exactly 1 month
        $startDateTime->modify('+1 month');
    }
} else {
    // Fallback if the database table is completely empty
    for ($i = 5; $i >= 0; $i--) {
        $monthKey   = date('Y-m', strtotime("first day of -$i months"));
        $monthLabel = date('M', strtotime("first day of -$i months"));
        $trendLabels[] = $monthLabel;
        $trendIncome[$monthKey] = 0;
        $trendExpense[$monthKey] = 0;
    }
}

// ====================================================================
// 2. Loop through transactions and sum them up (KEEP YOUR EXISTING STEP 2)
// ====================================================================
foreach ($transactions as $trx) {
    $trxMonth = date('Y-m', strtotime($trx['date']));
    
    if (isset($trendIncome[$trxMonth])) {
        $amt = (float)$trx['amt'];
        
        if ($trx['type'] === 'income') {
            $trendIncome[$trxMonth] += $amt;
        } else {
            $trendExpense[$trxMonth] += abs($amt);
        }
    }
}

// ====================================================================
// 3. Convert to clean arrays for Chart.js (UPDATED TO PRESERVE TIMELINE CHRONOLOGY)
// ====================================================================
// Using array_values guarantees the chart matches the forward chronologic sorting of $trendLabels
$finalTrendIncome = array_values($trendIncome);
$finalTrendExpense = array_values($trendExpense);


// 7. FETCH INITIAL CAPITAL
// Using the PDO connection already established in your data.php
$userStmt = $pdo->prepare("SELECT initial_capital FROM user WHERE user_id = 1");
$userStmt->execute();
$userData = $userStmt->fetch();
$initialCapital = (float)($userData['initial_capital'] ?? 0);

// New Correct Balance calculation
$currentBalance = $initialCapital + $totalIncome - $totalExpense;


//For Reports Financial Insights Feature
// Fetch the top spending category
$topCatStmt = $pdo->query("
    SELECT c.name AS category_name, SUM(ABS(t.amount)) AS total_spent
    FROM transaction t
    JOIN category c ON t.category_id = c.category_id
    WHERE t.type = 'expense'
    GROUP BY t.category_id
    ORDER BY total_spent DESC
    LIMIT 1
");
$topCategoryData = $topCatStmt->fetch();

// Store values for layout display
$highestSpendingCategory = $topCategoryData['category_name'] ?? 'None';
$highestSpendingAmount = (float)($topCategoryData['total_spent'] ?? 0);

// 1. Total spent in the last 7 days (This Week)
$thisWeekStmt = $pdo->query("
    SELECT SUM(ABS(amount)) AS total 
    FROM transaction 
    WHERE type = 'expense' 
      AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
");
$thisWeekTotal = (float)($thisWeekStmt->fetch()['total'] ?? 0);
// 2. Fetch trailing 7-day spending total (This Week)
$thisWeekStmt = $pdo->query("
    SELECT SUM(ABS(t.amount)) AS total 
    FROM transaction t
    WHERE t.type = 'expense' 
      AND t.date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
");
$thisWeekTotal = (float)($thisWeekStmt->fetch()['total'] ?? 0);


// 3. Fetch preceding 7-day spending total (Last Week)
$lastWeekStmt = $pdo->query("
    SELECT SUM(ABS(t.amount)) AS total 
    FROM transaction t
    WHERE t.type = 'expense' 
      AND t.date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) 
      AND t.date < DATE_SUB(CURDATE(), INTERVAL 7 DAY)
");
$lastWeekTotal = (float)($lastWeekStmt->fetch()['total'] ?? 0);

// 4. Calculate comparative percentage change sentence
$insightMessage = "No spending data from last week to compare."; // Default initial fallback value

if ($lastWeekTotal > 0) {
    $percentageChange = (($thisWeekTotal - $lastWeekTotal) / $lastWeekTotal);
    $roundedPercent = round(abs($percentageChange), 2);

    if ($percentageChange > 0) {
        $insightMessage = "You spent <span class='text-danger fw-bold'>{$roundedPercent}x more</span> this week compared to last week.";
    } elseif ($percentageChange < 0) {
        $insightMessage = "You spent <span class='text-success fw-bold'>{$roundedPercent}x less</span> this week compared to last week.";
    } else {
        $insightMessage = "Your spending this week matches exactly with last week.";
    }
} elseif ($thisWeekTotal > 0 && $lastWeekTotal == 0) {
    $insightMessage = "You have started tracking new expenses this week.";
} else {
    // This catches the exact scenario causing your bug!
    $insightMessage = "No expenses recorded in the last 14 days to analyze.";
}

//savings rate calculation
$savingsRate = 0;
if ($totalIncome > 0) {
    $netSavings = $totalIncome - $totalExpense;
    $savingsRate = round(($netSavings / $totalIncome) * 100);
}

?>