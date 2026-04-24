<?php include 'db_connect.php'; ?>

<?php
// 1. Include the connection file
include 'db_connect.php';

// 2. Logic to handle the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_transaction'])) {
    
    // Collecting data from the form inputs
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $date = $_POST['date'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $userid = 1; // Placeholder: we will change this once we have user login

    // Using a Prepared Statement to safely insert data
    $stmt = $conn->prepare("INSERT INTO transactions (userid, amount, category, type, date, description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idssss", $userid, $amount, $category, $type, $date, $description);

    if ($stmt->execute()) {
        echo "<script>alert('Transaction saved successfully!');</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Spending</title>
</head>
<body>
    <h2>Log New Spending</h2>
    
    <form action="transaction.php" method="POST">
        <label>Amount (RM):</label>
        <input type="number" name="amount" step="0.01" required>

        <label>Category:</label>
        <select name="category">
            <option value="Food">Food</option>
            <option value="Transport">Transport</option>
            <option value="Entertainment">Entertainment</option>
            <option value="Bills">Bills</option>
        </select>

        <label>Date:</label>
        <input type="date" name="date" required>

        <label>Description:</label>
        <input type="text" name="description" placeholder="e.g., Lunch at MMU">

        <label>Type:</label>
        <select name="type">
            <option value="expense">Expense</option>
            <option value="income">Income</option>
        </select>

        <button type="submit" name="add_transaction">Save Transaction</button>
    </form>
</body>
</html>
