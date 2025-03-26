<?php
session_start();
// Include database connection
include 'pdo.php';

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] == 'admin' ? 'admin/admin.php' : 
           ($_SESSION['role'] == 'agent' ? 'agent/agent.php' : 'user.php')));
    exit();
}

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login']) && isset($_POST['password'])) {
        $login = $_POST['login'];
        $password = $_POST['password'];

        // Validation
        if (empty($login) || empty($password)) {
            $_SESSION['error'] = 'Please enter both name/email and password.';
            header('Location: login.php');
            exit();
        }

        // Email/name validation
        if (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
            if (preg_match("/^[a-zA-Z0-9_]{3,20}$/", $login) === 0) {
                $_SESSION['error'] = 'Invalid name format.';
                header('Location: login.php');
                exit();
            }
        }

        try {
            $query = "SELECT * FROM Users WHERE name = :login OR email = :login LIMIT 1";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':login', $login, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];

                    // Fetch agent_id or admin_id based on role
                    if ($user['role'] == 'agent') {
                        $agentQuery = "SELECT agent_id FROM Agents WHERE user_id = :user_id LIMIT 1";
                        $agentStmt = $pdo->prepare($agentQuery);
                        $agentStmt->bindParam(':user_id', $user['user_id'], PDO::PARAM_INT);
                        $agentStmt->execute();
                        $agent = $agentStmt->fetch(PDO::FETCH_ASSOC);
                        $_SESSION['agent_id'] = $agent['agent_id'];
                    } 
                    elseif ($user['role'] == 'admin') {
                        $adminQuery = "SELECT admin_id FROM Admins WHERE user_id = :user_id LIMIT 1";
                        $adminStmt = $pdo->prepare($adminQuery);
                        $adminStmt->bindParam(':user_id', $user['user_id'], PDO::PARAM_INT);
                        $adminStmt->execute();
                        $admin = $adminStmt->fetch(PDO::FETCH_ASSOC);
                        $_SESSION['admin_id'] = $admin['admin_id'];
                    }

                    header('Location: ' . ($_SESSION['role'] == 'admin' ? 'admin/admin.php' : 
                           ($_SESSION['role'] == 'agent' ? 'agent/agent.php' : 'user.php')));
                    exit();
                } else {
                    $_SESSION['error'] = 'Invalid name/email or password';
                }
            } else {
                $_SESSION['error'] = 'User does not exist';
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        }
        
        if (isset($_SESSION['error'])) {
            header('Location: login.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>

    <link rel="apple-touch-icon" sizes="180x180" href="/Fonts/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/Fonts/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/Fonts/favicon-16x16.png">
    <link rel="manifest" href="/Fonts/site.webmanifest">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/login.css">
    
</head>

<body class="bg-light d-flex flex-column min-vh-100">
    <?php include "header.php"; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo htmlspecialchars($_SESSION['error']); ?>
        <?php unset($_SESSION['error']); ?>
    </div>
    <?php endif; ?>

    <div class="container d-flex justify-content-center align-items-center flex-grow-1 my-5">
        <div class="card p-4 shadow-sm w-100" style="max-width: 400px;">
            <h2 class="text-center mb-4">Login to Your Account</h2>
            <form method="POST" action="login.php">
                <div class="mb-3 position-relative">
                    <label for="login" class="form-label">Name or Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" id="login" name="login" class="form-control" 
                               placeholder="Enter your name or email" required>
                    </div>
                </div>

                <div class="mb-3 position-relative">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Enter your password" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input type="checkbox" id="remember" name="remember" class="form-check-input">
                        <label for="remember" class="form-check-label">Remember me</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>

            <div class="mt-3 text-center">
                <p>Don't have an account? <a href="register.php" class="text-decoration-none">Register here</a></p>
            </div>
        </div>
    </div>

    <div class="mt-5"></div>

    <?php include "footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>