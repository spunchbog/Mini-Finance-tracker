<?php
// data.php
// In the future, this is where you'll put:
// $conn = mysqli_connect("localhost", "root", "", "fintrack");

// 1. Set the Timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

require_once 'db_connect.php';

// We join 'transaction' with 'category' to get the category NAME
$query = "
    SELECT 
        t.description AS name, 
        c.name AS cat, 
        t.date, 
        t.amount AS amt,
        t.type
    FROM transaction t
    JOIN category c ON t.category_id = c.category_id
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
    $amt = abs($trx['amt']);
    $cat = $trx['cat'];

    // We only want to chart EXPENSES (negative amounts)
    if ($trx['amt'] < 0) {
        
        // Helper logic to add to bucket if date matches
        if ($trxTime >= $today)          $buckets['today'][$cat]      = ($buckets['today'][$cat] ?? 0) + $amt;
        if ($trxTime >= $sevenDaysAgo)   $buckets['7_days'][$cat]     = ($buckets['7_days'][$cat] ?? 0) + $amt;
        if ($trxTime >= $thirtyDaysAgo)  $buckets['30_days'][$cat]    = ($buckets['30_days'][$cat] ?? 0) + $amt;
        if ($trxTime >= $ninetyDaysAgo)  $buckets['12_weeks'][$cat]   = ($buckets['12_weeks'][$cat] ?? 0) + $amt;
        if ($trxTime >= $sixMonthsAgo)   $buckets['6_months'][$cat]   = ($buckets['6_months'][$cat] ?? 0) + $amt;
        if ($trxTime >= $oneYearAgo)     $buckets['1_year'][$cat]     = ($buckets['1_year'][$cat] ?? 0) + $amt;
        if ($trxTime >= $fiveYearsAgo)   $buckets['5_years'][$cat]    = ($buckets['5_years'][$cat] ?? 0) + $amt;
        if ($trxTime >= $thisMonthStart) $buckets['this_month'][$cat] = ($buckets['this_month'][$cat] ?? 0) + $amt;
        if ($trxTime >= $thisYearStart)  $buckets['this_year'][$cat]  = ($buckets['this_year'][$cat] ?? 0) + $amt;
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

// 6. CALCULATE HEADER TOTALS (Optional but useful)
$totalIncome = 0;
$totalExpense = 0;
foreach ($transactions as $trx) {
    if ($trx['amt'] > 0) $totalIncome += $trx['amt'];
    else $totalExpense += abs($trx['amt']);
}
?>