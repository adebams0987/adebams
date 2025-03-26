<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mortgage Calculator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Favicon Links -->
    <link rel="apple-touch-icon" sizes="180x180" href="Fonts/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="Fonts/favicon-32x32.png">
    <!-- Font Awesome and other CSS Links -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        header {
            position: sticky;
            top: 0; /* Stick the header at the top of the page */
            z-index: 1000;
            background-color: #343a40;
            padding: 10px 0;
            margin-bottom: 80px; /* Ensures content isn't hidden behind sticky header */
        }

        header .brand-logo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
        }

        header .brand-logo img {
            max-height: 50px; /* Adjust as needed */
        }

        header .brand-logo .brand-name {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
            margin-left: 10px;
        }

        header .back-home-btn {
            font-size: 16px;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
        }

        header .back-home-btn:hover {
            text-decoration: underline;
        }

        footer {
            margin-top: 100px; /* Adds space between footer and body */
        }

        .calculator-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 500px;
            margin: 0 auto;
            margin-bottom: 30px;
        }

        .output {
            font-size: 1.5rem;
            font-weight: bold;
        }

        h3 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: #333;
            margin-bottom: 30px;
        }

        .btn-primary {
            background-color: #007bff;
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 600;
            width: 100%;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }
    </style>
</head>
<body>

<?php include "header.php"; ?>

<div class="container">
    <div class="calculator-container">
        <h3 class="text-center mb-4">Mortgage Calculator</h3>
        <form id="mortgageForm">
            <!-- Loan Amount -->
            <div class="mb-3">
                <label for="loanAmount" class="form-label">Loan Amount ($)</label>
                <input type="number" class="form-control" id="loanAmount" required>
            </div>

            <!-- Interest Rate -->
            <div class="mb-3">
                <label for="interestRate" class="form-label">Annual Interest Rate (%)</label>
                <input type="number" class="form-control" id="interestRate" step="0.1" required>
            </div>

            <!-- Loan Term -->
            <div class="mb-3">
                <label for="loanTerm" class="form-label">Loan Term (years)</label>
                <input type="number" class="form-control" id="loanTerm" required>
            </div>

            <!-- Calculate Button -->
            <button type="button" class="btn btn-primary" id="calculateButton">Calculate</button>

            <!-- Result Section -->
            <div class="mt-4">
                <h4 class="text-center">Results</h4>
                <p id="monthlyPayment" class="output text-center"></p>
                <p id="totalPayment" class="output text-center"></p>
            </div>
        </form>
    </div>
</div>

<?php
    include "footer.php";  /* Include your footer */
?>

<script>
    document.getElementById('calculateButton').addEventListener('click', function() {
        // Get input values
        const loanAmount = parseFloat(document.getElementById('loanAmount').value);
        const interestRate = parseFloat(document.getElementById('interestRate').value) / 100 / 12; // Monthly interest
        const loanTerm = parseInt(document.getElementById('loanTerm').value) * 12; // Convert years to months

        if (isNaN(loanAmount) || isNaN(interestRate) || isNaN(loanTerm) || loanAmount <= 0 || interestRate <= 0 || loanTerm <= 0) {
            alert("Please fill out all fields with valid values.");
            return;
        }

        // Calculate monthly payment using the formula
        const monthlyPayment = (loanAmount * interestRate) / (1 - Math.pow(1 + interestRate, -loanTerm));

        // Calculate total payment over the life of the loan
        const totalPayment = monthlyPayment * loanTerm;

        // Display results
        document.getElementById('monthlyPayment').innerText = `Monthly Payment: $${monthlyPayment.toFixed(2)}`;
        document.getElementById('totalPayment').innerText = `Total Payment: $${totalPayment.toFixed(2)}`;
    });
</script>

</body>
</html>