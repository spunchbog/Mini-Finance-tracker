<?php include 'db_connect.php'; ?>

<?php
// transaction.php - Right after the include
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_transaction'])) {
    
    // Sanitize and collect inputs
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $date = $_POST['date'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $userid = 1; // Placeholder until build auth

    // Prepare and execute the insert
    $stmt = $conn->prepare("INSERT INTO transactions (userid, amount, category, type, date, description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idssss", $userid, $amount, $category, $type, $date, $description);

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Transaction saved successfully!</p>";
    } else {
        echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}
?>


<form action="transaction.php" method="POST">
    <input type="number" name="amount" step="0.01" required placeholder="Amount">
    <select name="category">
        <option value="Food">Food</option>
        <option value="Transport">Transport</option>
    </select>
    <input type="date" name="date" required>
    <input type="text" name="description" placeholder="Description">
    <select name="type">
        <option value="income">Income</option>
        <option value="expense">Expense</option>
    </select>
    <button type="submit" name="add_transaction">Save Transaction</button>
</form>
