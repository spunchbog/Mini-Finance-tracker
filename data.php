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
        t.description AS name, 
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

// 6. CALCULATE HEADER TOTALS
$totalIncome = 0;
$totalExpense = 0;

foreach ($transactions as $trx) {
    // We convert to float to ensure mathematical accuracy
    $amount = (float)$trx['amt'];

    // Check the 'type' column instead of the number sign
    if ($trx['type'] === 'income') {
        $totalIncome += $amount;
    } elseif ($trx['type'] === 'expense') {
        // We use abs() just in case the expense was stored as -50.00
        // This ensures $totalExpense is always a positive "total"
        $totalExpense += abs($amount);
    }
}

// Calculate Balance
$totalBalance = $totalIncome - $totalExpense;


// 1. Get the last 6 months (e.g., ["Dec", "Jan", "Feb", "Mar", "Apr", "May"])
$trendLabels = [];
$trendIncome = [];
$trendExpense = [];

for ($i = 5; $i >= 0; $i--) {
    $monthKey = date('Y-m', strtotime("-$i months")); // e.g., "2026-05"
    $monthLabel = date('M', strtotime("-$i months")); // e.g., "May"
    
    $trendLabels[] = $monthLabel;
    $trendIncome[$monthKey] = 0;
    $trendExpense[$monthKey] = 0;
}

// 2. Loop through transactions and sum them up by month/type
foreach ($transactions as $trx) {
    $trxMonth = date('Y-m', strtotime($trx['date'])); // e.g., "2026-05"
    
    // If the transaction month exists in our 6-month window
    if (isset($trendIncome[$trxMonth])) {
        $amt = (float)$trx['amt'];
        
        if ($trx['type'] === 'income') {
            $trendIncome[$trxMonth] += $amt;
        } else {
            $trendExpense[$trxMonth] += abs($amt);
        }
    }
}

// 3. Convert to simple arrays for Chart.js
$finalTrendIncome = array_values($trendIncome);
$finalTrendExpense = array_values($trendExpense);

?>