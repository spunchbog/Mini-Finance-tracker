<?php
session_start();
include('header.php');
include('db_connect.php');

// 2. Logic to handle the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_transaction'])) {
    
    $amount = $_POST['amount'];
    
    // Map your text categories to IDs (Check your 'category' table in phpMyAdmin to see the real IDs!)
    $category_map = [
        "Food" => 1,
        "Transport" => 2,
        "Entertainment" => 3,
        "Bills" => 4,
        "Others" => 5
    ];
    $category_id = $category_map[$_POST['category']] ?? 5; // Default to 5 if not found

    $date = $_POST['date'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $userid = 1; 

    // UPDATED SQL: Using the exact column names from your screenshot
    $stmt = $conn->prepare("INSERT INTO transaction (user_id, category_id, amount, type, date, description) VALUES (?, ?, ?, ?, ?, ?)");
    
    // "i" for int, "d" for decimal/double, "s" for string
    // Order: user_id(i), category_id(i), amount(d), type(s), date(s), description(s)
    $stmt->bind_param("iidsss", $userid, $category_id, $amount, $type, $date, $description);

    if ($stmt->execute()) {
        echo "<script>alert('Transaction saved successfully!');</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<style>
    :root {
        --bg-color: #fcfbf4;
        --card-bg: #ffffff;
        --text-main: #333;
        --accent: #4a90e2;
        --border-radius: 12px;
    }

    body {
        background-color: var(--bg-color);
        font-family: 'Inter', -apple-system, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
    }

    .main-container {
        width: 100%;
        max-width: 500px;
        padding: 20px;
    }

    .input-card {
        background: var(--card-bg);
        padding: 40px;
        border-radius: var(--border-radius);
        box-shadow: 0 10px 25px rgba(0,0,0,0.03); /* Soft shadow like the image */
    }

    .card-header h2 { margin: 0; font-size: 22px; color: var(--text-main); }
    .card-header p { color: #888; font-size: 14px; margin-top: 5px; margin-bottom: 30px; }

    .form-group { margin-bottom: 20px; display: flex; flex-direction: column; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }

    label { font-size: 13px; font-weight: 600; margin-bottom: 8px; color: #555; }

    input, select {
        padding: 12px;
        border: 1px solid #eee;
        border-radius: 8px;
        background: #f9f9f9;
        font-size: 15px;
        transition: all 0.3s ease;
    }

    input:focus, select:focus {
        outline: none;
        border-color: var(--accent);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(74, 144, 226, 0.1);
    }

    .submit-btn {
        width: 100%;
        padding: 14px;
        background: var(--text-main); /* Dark like the side-menu button */
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        margin-top: 10px;
        transition: transform 0.2s;
    }

    .submit-btn:hover { transform: translateY(-2px); opacity: 0.9; }
</style>
</head>

<div class="main-container">
    <div class="input-card">
        <div class="card-header">
            <h2>Log New Transaction</h2>
            <p>Enter your transaction details below to update your dashboard.</p>
        </div>

        <form action="transaction.php" method="POST" class="modern-form">
            <div class="form-group">
                <label>Amount (RM)</label>
                <input type="number" name="amount" step="0.01" placeholder="0.00" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" id="categorySelect" onchange="toggleOtherInput()">
                        <option value="Food">Food</option>
                        <option value="Transport">Transport</option>
                        <option value="Entertainment">Entertainment</option>
                        <option value="Bills">Bills</option>
                        <option value="Others">Others</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="type">
                        <option value="expense">Expense</option>
                        <option value="income">Income</option>
                    </select>
                </div>
            </div>

            <!-- Custom Category Input (Hidden by default) -->
            <div id="otherCategoryDiv" class="form-group animate-fade" style="display:none;">
                <label>Specify Hobby/Activity</label>
                <input type="text" name="other_category" placeholder="e.g., Badminton">
            </div>

            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" placeholder="e.g., Lunch at MMU">
            </div>

            <button type="submit" name="add_transaction" class="submit-btn">Save Transaction</button>
        </form>
    </div>
</div>