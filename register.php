<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);  // Get the current page name

// Include database connection
include "pdo.php";  // Assuming $pdo is your PDO connection object

// Initialize error message
$error_msg = "";

// Check if the form is submitted using POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the required fields are set
    if (isset($_POST['name'], $_POST['email'], $_POST['password'], $_POST['role'], $_POST['phone_number'])) {
        // Get form inputs
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $role = trim($_POST['role']);
        $phone_number = trim($_POST['phone_number']);  // Get phone_number number

        // Validate form inputs
        if (empty($name) || empty($email) || empty($password) || empty($role) || empty($phone_number)) {
            $error_msg = "All fields are required.";
        } 
        else {
            // Validate name: allow letters, numbers, underscores, and spaces between 3 and 20 characters
            if (!preg_match("/^[a-zA-Z0-9_ ]{3,20}$/", $name)) {
                $error_msg = "name must be between 3 and 20 characters and only contain letters, numbers, underscores, and spaces.";
            }            

            // Validate email format
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error_msg = "Please enter a valid email address.";
            }

            // Validate password: should be at least 8 characters long, contain at least one letter, and one number
            elseif (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/", $password)) {
                $error_msg = "Password must be at least 8 characters long and contain at least one letter and one number.";
            }

            // Validate role
            elseif (!in_array($role, ['user', 'agent', 'admin'])) {
                $error_msg = "Invalid role selected.";
            }

            // Validate phone_number number: should only contain digits and be of a specific length (e.g., 10 digits)
            elseif (empty($phone_number) || !preg_match("/^[0-9]{11}$/", $phone_number)) {
                $error_msg = "Please enter a valid 11-digit phone_number number.";
            }

            // If no errors, proceed with database checks
            if (empty($error_msg)) {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Check if the name or email already exists using PDO
                $query = "SELECT * FROM users WHERE name = :name OR email = :email";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result) {
                    $error_msg = "name or email already exists. Please choose another.";
                } 
                else {
                    // Insert the new user into the database
                    $insert_query = "INSERT INTO users (name, email, password, role, phone_number) VALUES (:name, :email, :password, :role, :phone_number)";
                    $insert_stmt = $pdo->prepare($insert_query);
                    $insert_stmt->bindParam(':name', $name);
                    $insert_stmt->bindParam(':email', $email);
                    $insert_stmt->bindParam(':password', $hashed_password);
                    $insert_stmt->bindParam(':role', $role);
                    $insert_stmt->bindParam(':phone_number', $phone_number);

                    if ($insert_stmt->execute()) {
                        // Registration successful, redirect to the login page
                        header("Location: login.php?status=success");
                        exit();
                    } 
                    else {
                        $error_msg = "There was an error with the registration process. Please try again later.";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>

    <!-- Favicon Links -->
    <link rel="apple-touch-icon" sizes="180x180" href="/Fonts/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/Fonts/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/Fonts/favicon-16x16.png">
    <link rel="manifest" href="/Fonts/site.webmanifest">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="CSS/register.css">
</head>

<body class="bg-light d-flex flex-column min-vh-100">

    <?php include "header.php"; ?>

    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_msg); ?>
        </div>
    <?php endif; ?>

    <!-- Centered container with extra space -->
    <div class="container d-flex justify-content-center align-items-center flex-grow-1 my-5">
        <div class="card p-4 shadow-sm w-100" style="max-width: 400px;">
            <h2 class="text-center mb-4">Create Your Account</h2>
            <form method="POST">
                <!-- name Field -->
                <div class="mb-3 position-relative">
                    <label for="name" class="form-label">Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Enter your name" required value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                    </div>
                </div>

                <!-- Email Field -->
                <div class="mb-3 position-relative">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                    </div>
                </div>

                <!-- Password Field -->
                <div class="mb-3 position-relative">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>
                </div>

                <!-- phone_number Number Field -->
                <div class="mb-3 position-relative">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone_number"></i></span>
                        <input type="text" id="phone_number" name="phone_number" class="form-control" placeholder="Enter your phone number" required value="<?php echo isset($phone_number) ? htmlspecialchars($phone_number) : ''; ?>">
                    </div>
                </div>

                <!-- Role Selection -->
                <div class="mb-3 position-relative">
                    <label for="role" class="form-label">Select Role</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-users-cog"></i></span>
                        <select id="role" name="role" class="form-select" required>
                            <option value="user" <?php echo (isset($role) && $role == 'user') ? 'selected' : ''; ?>>User</option>
                            <option value="agent" <?php echo (isset($role) && $role == 'agent') ? 'selected' : ''; ?>>Agent</option>
                            <option value="admin" <?php echo (isset($role) && $role == 'admin') ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Register</button>

                <div class="mt-3 text-center">
                <p>Do you have an account? <a href="login.php" class="text-decoration-none">Login here</a></p>
            </div>
            </form>
        </div>
    </div>

    <div class="mt-5"></div>

    <?php include "footer.php"; ?> <!-- Include footer -->

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>

</html>

