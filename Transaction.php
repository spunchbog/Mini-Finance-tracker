<?php
session_start();
include('db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_transaction'])) {
    
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $userid = $_SESSION['user_id'];

    if ($type == 'expense') {
        $category_map = [
            "Food" => 1,
            "Transport" => 2,
            "Entertainment" => 3,
            "Bills" => 4,
            "Others" => 5
        ];
        $category_id = $category_map[$_POST['category']] ?? 5;
    } else {
        $category_map = [
            "Salary" => 6,
            "Freelance" => 7,
            "Business" => 8,
            "Savings" => 9,
            "Investments" => 10,
            "Other Income" => 11
        ];
        $category_id = $category_map[$_POST['category']] ?? 11;
    }

    $stmt = $conn->prepare("INSERT INTO transaction (user_id, category_id, amount, type, date, description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iidsss", $userid, $category_id, $amount, $type, $date, $description);

    if ($stmt->execute()) {
        echo "<script>alert('Transaction saved successfully!');</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">    
<head>  
    <meta charset="UTF-8">
    <title>FinTrack - Log Transaction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Scoped styles explicitly restricted to the input card module to protect sidebar UI definitions */
        .main-container {
            flex: 1;
            overflow-y: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .input-card {
            background: #FFFFFF;
            padding: 40px;
            border-radius: 16px;
            border: 1px solid var(--border-subtle);
            box-shadow: var(--card-shadow);
            width: 100%;
            max-width: 500px;
        }

        .card-header h2 { 
            margin: 0; 
            font-size: 22px; 
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--text-main); 
        }
        
        .card-header p { 
            color: var(--text-muted); 
            font-size: 14px; 
            margin-top: 6px; 
            margin-bottom: 30px; 
        }

        .type-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 10px;
        }

        .type-btn {
            padding: 16px;
            border: 1px solid var(--border-subtle);
            border-radius: 10px;
            background: var(--background-cream);
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--text-main);
        }

        .type-btn:hover { 
            border-color: var(--text-muted); 
            background: #FFFFFF; 
        }

        .type-btn.active-expense {
            border-color: var(--danger);
            background: var(--danger-bg);
            color: var(--danger);
            box-shadow: 0 0 0 3px rgba(201, 42, 42, 0.08);
        }

        .type-btn.active-income {
            border-color: var(--safe);
            background: var(--safe-bg);
            color: var(--safe);
            box-shadow: 0 0 0 3px rgba(43, 138, 62, 0.08);
        }

        .form-section {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease, margin-top 0.2s ease;
            opacity: 0;
            margin-top: 0;
        }

        .form-section.expanded {
            max-height: 900px;
            opacity: 1;
            margin-top: 25px;
        }

        .form-group { margin-bottom: 20px; display: flex; flex-direction: column; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }

        label { 
            font-size: 13px; 
            font-weight: 600; 
            margin-bottom: 8px; 
            color: var(--text-main); 
        }

        input, select {
            padding: 12px;
            border: 1px solid var(--border-subtle);
            border-radius: 8px;
            background: #FFFFFF;
            font-size: 15px;
            color: var(--text-main);
            transition: all 0.2s ease;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--text-muted);
            box-shadow: 0 0 0 3px rgba(108, 114, 127, 0.08);
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: var(--sidebar-bg);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: transform 0.2s, opacity 0.2s;
        }

        .submit-btn:hover { 
            transform: translateY(-2px); 
            opacity: 0.95; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<div id="wrapper" class="d-flex vh-100 w-100" style="overflow: hidden;"> 
    
    <?php include 'sidebar.php'; ?>

    <div id="page-content-wrapper" class="flex-grow-1 p-0">
        <div class="main-container">
            <div class="input-card">
                <div class="card-header">
                    <h2>Log New Transaction</h2>
                    <p>Choose whether you're logging an expense or income.</p>
                </div>

                <div class="type-buttons">
                    <button class="type-btn" id="expenseBtn" onclick="selectType('expense')">💸 Expense</button>
                    <button class="type-btn" id="incomeBtn" onclick="selectType('income')">💰 Income</button>
                </div>

                <div class="form-section" id="formSection">
                    <form action="transaction.php" method="POST" class="modern-form">
                        <input type="hidden" name="type" id="typeInput" value="">

                        <div class="form-group">
                            <label>Amount (RM)</label>
                            <input type="number" name="amount" step="0.01" placeholder="0.00" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category" id="categorySelect" onchange="toggleOtherInput()">
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Type</label>
                                <input type="text" id="typeDisplay" readonly style="background: var(--background-cream); color: var(--text-muted);">
                            </div>
                        </div>

                        <div id="otherCategoryDiv" class="form-group" style="display:none;">
                            <label>Specify Category</label>
                            <input type="text" name="other_category" placeholder="e.g., Medical, Gift...">
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
        </div>
    </div>
</div>

<script>
    const expenseCategories = ["Food", "Transport", "Entertainment", "Bills", "Others"];
    const incomeCategories = ["Salary", "Freelance", "Business", "Savings", "Investments", "Other Income"];

    function selectType(type) {
        const formSection = document.getElementById('formSection');
        const categorySelect = document.getElementById('categorySelect');
        const typeInput = document.getElementById('typeInput');
        const typeDisplay = document.getElementById('typeDisplay');
        const expenseBtn = document.getElementById('expenseBtn');
        const incomeBtn = document.getElementById('incomeBtn');

        typeInput.value = type;
        typeDisplay.value = type.charAt(0).toUpperCase() + type.slice(1);

        expenseBtn.className = 'type-btn' + (type === 'expense' ? ' active-expense' : '');
        incomeBtn.className = 'type-btn' + (type === 'income' ? ' active-income' : '');

        const categories = type === 'expense' ? expenseCategories : incomeCategories;
        categorySelect.innerHTML = '';
        categories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat;
            option.textContent = cat;
            categorySelect.appendChild(option);
        });

        formSection.classList.add('expanded');
        toggleOtherInput();
    }

    function toggleOtherInput() {
        const categorySelect = document.getElementById('categorySelect');
        const otherDiv = document.getElementById('otherCategoryDiv');
        const val = categorySelect.value;
        otherDiv.style.display = (val === 'Others' || val === 'Other Income') ? 'block' : 'none';
    }
</script>

</body>
</html>