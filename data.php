<?php
// data.php
// In the future, this is where you'll put:
// $conn = mysqli_connect("localhost", "root", "", "fintrack");

// 1. Set the Timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

// 2. Global Variables
$currentMonth = "May 2026";

// 3. Chart Data (Source of Truth)
$monthlySpending = [
    'labels' => ['Food', 'Rent', 'Bills'],
    'data' => [300, 1200, 500]
];

$weeklySpending = [
    'labels' => ['Groceries', 'Coffee', 'Fuel'],
    'data' => [85, 40, 60]
];


// 4. Transaction List 
$transactions = [
    ['name' => 'Food', 'cat' => 'Software', 'date' => 'May 04', 'amt' => -55.00],
    ['name' => 'Salary', 'cat' => 'Income', 'date' => 'May 01', 'amt' => 4500.00],
    ['name' => 'Transport', 'cat' => 'Food', 'date' => 'May 03', 'amt' => -18.50],
];

// 5. Calculations
// You can even calculate totals here so the dashboard doesn't have to
$totalIncome = 0;
$totalExpense = 0;

foreach ($transactions as $t) {
    if ($t['amt'] > 0) $totalIncome += $t['amt'];
    else $totalExpense += abs($t['amt']);
}
?>