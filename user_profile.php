<?php
session_start();
include "pdo.php";
include "process_property.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Prepare the SQL query to fetch saved properties for the user
$query = "SELECT properties.property_id, properties.title, properties.address, properties.city, properties.state, properties.price, properties.created_at 
    FROM saved_properties
    INNER JOIN Properties ON saved_properties.property_id = properties.property_id
    WHERE saved_properties.user_id = :user_id
    ORDER BY saved_properties.saved_at DESC
";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
// Fetch all saved properties
$saved_properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch User Details
$query = "SELECT name, email, phone_number, role, created_at FROM Users WHERE user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (isset($_POST['name']) && isset($_POST['email'])) {
    // Get the form inputs
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $password = isset($_POST['password']) ? $_POST['password'] : ''; // Handle case when password is not provided

    // Input Validation: Ensure fields are not empty
    if (empty($name) || empty($email) || empty($phone_number)) {
        $_SESSION['error_message'] = "Name, email, and phone number are.";
        header("Location: user_profile.php");
        exit();
    }

    // Start building the update query
    $updateQuery = "UPDATE Users SET name = :name, email = :email, phone_number = :phone_number";

    // If password is provided, hash it and include it in the update query
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $updateQuery .= ", password = :password";
    }
    
    $updateQuery .= " WHERE user_id = :user_id";

    // Prepare the query
    $stmt = $pdo->prepare($updateQuery);
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    
    // Bind password if provided
    if (!empty($password)) {
        $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
    }

    // Execute the query
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Profile updated successfully!";
        header("Location: user_profile.php");
        exit();
    } else {
        // Error logging for better debugging
        $_SESSION['error_message'] = "Error updating profile: " . implode(", ", $stmt->errorInfo());
        header("Location: user_profile.php");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Estates</title>

    <!-- Favicon Links -->
    <link rel="apple-touch-icon" sizes="180x180" href="Fonts/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="Fonts/favicon-32x32.png">

    <!-- CSS Links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- <link rel="stylesheet" href="CSS/User.css"> -->

    <style>
        .profile-header {
            background: linear-gradient(135deg, #6c5ce7 0%, #a367dc 100%);
            color: white;
            padding: 2rem 0;
        }
        .form-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
        .favorite-property {
            transition: transform 0.2s;
        }
        .favorite-property:hover {
            transform: translateY(-5px);
        }
    </style>

</head>
<?php include "header.php"; 

if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger" id="alert-message">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}

if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success" id="alert-message">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}
?>
<body>

<div class="profile-header mb-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-9">
                    <h1 class="display-4 mb-0"><?php echo htmlspecialchars($user['name']); ?></h1>
                    <p class="lead mb-2"><?php echo $user['role'] === 'user' ? 'Home Seeker' : ucfirst($user['role']); ?></p>
                    <p class="text-muted mb-4">
                        <i class="bi bi-clock"></i> Member since: <?php echo date("F Y", strtotime($user['created_at'])); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile and Saved Properties Content -->
    <div class="container mb-5">
        <div class="row">
            <!-- Profile Section -->
            <div class="col-md-8">
                <div class="form-container properties-4">
                    <h3 class="mb-4">Profile</h3>
                    
                    <!-- View Mode -->
                    <div id="viewProfile">
                        <div class="mb-3">
                            <strong>Full Name:</strong>
                            <p class="mb-0"><?php echo htmlspecialchars($user['name']); ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Email:</strong>
                            <p class="mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>

                        <div class="mb-3">
                            <strong>Phone Number:</strong>
                            <p class="mb-0"><?php echo htmlspecialchars($user['phone_number'] ?? 'N/A'); ?></p>
                        </div>

                        <div class="mb-3">
                            <strong>Role:</strong>
                            <p class="mb-0"><?php echo ucfirst(htmlspecialchars($user['role'])); ?></p>
                        </div>

                        <div class="mb-3">
                            <strong>Member Since:</strong>
                            <p class="mb-0"><?php echo date("F d, Y", strtotime($user['created_at'])); ?></p>
                        </div>

                        <button id="editButton" class="btn btn-primary mt-3">Edit Profile</button>
                    </div>

                    <!-- Edit Mode -->
                    <div id="editProfile" style="display: none;">
                        <form id="profileForm" method="POST">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Change Password</label>
                                <input type="password" class="form-control" name="password" placeholder="Enter new password">
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="button" class="btn btn-secondary me-md-2" id="cancelButton">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


            <!-- Saved Properties Section -->
            <div class="col-md-4">
                <div class="form-container p-4">
                    <h4 class="mb-3">Saved Properties</h4>
                    <div class="list-group">
                        <?php if ($saved_properties): ?>
                            <?php foreach ($saved_properties as $property): ?>
                                <a href="property_details.php?id=<?php echo $property['property_id']; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($property['title']); ?></h6>
                                        <small class="text-muted"><?php echo time_ago($property['created_at']); ?> ago</small>
                                    </div>
                                    <small><?php echo htmlspecialchars($property['city'] . ', ' . $property['state']); ?> | $<?php echo number_format($property['price'], 2); ?></small>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No saved properties found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
    const editButton = document.getElementById('editButton');
    const cancelButton = document.getElementById('cancelButton');
    const viewProfile = document.getElementById('viewProfile');
    const editProfile = document.getElementById('editProfile');

    // Show Edit Mode
    editButton.addEventListener('click', () => {
        viewProfile.style.display = 'none';
        editProfile.style.display = 'block';
    });

    // Cancel Edit Mode
    cancelButton.addEventListener('click', () => {
        editProfile.style.display = 'none';
        viewProfile.style.display = 'block';
    });

    // Set a timeout for the alert to disappear after 3 seconds
    setTimeout(function() {
        var alertMessage = document.getElementById('alert-message');
        if (alertMessage) {
            alertMessage.style.display = 'none'; // Hide the alert after 3 seconds
        }
    }, 3000); // 3000 milliseconds = 3 seconds
</script>

<?php include "footer.php"; ?>
</body>
</html>