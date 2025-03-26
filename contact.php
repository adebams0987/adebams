<?php
session_start();
include "header.php";
include "pdo.php"; // Assuming the PDO connection is created here

// Check if property_id or agent_id is set in the session or GET request
if (isset($_GET['property_id'])) {
    $property_id = $_GET['property_id'];
    $_SESSION['property_id'] = $property_id; // Save property_id in session
} 

if (isset($_GET['agent_id'])) {
    $agent_id = $_GET['agent_id'];
    $_SESSION['agent_id'] = $agent_id; // Save agent_id in session
}

// Fetch agent's name if agent_id is passed
if (isset($_GET['agent_id'])) {
    $agent_id = $_GET['agent_id'];
    $sql = "SELECT u.name FROM Users u
            JOIN Agents a ON u.user_id = a.user_id
            WHERE a.agent_id = :agent_id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':agent_id', $agent_id, PDO::PARAM_INT);
    $stmt->execute();

    // Check if the agent is found
    if ($stmt->rowCount() > 0) {
        $agent = $stmt->fetch(PDO::FETCH_ASSOC);
        $agent_name = htmlspecialchars($agent['name']); // Sanitize output
    } else {
        $agent_name = "Agent not found";
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all required form fields are set
    if (isset($_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['message'])) {

        // Sanitize form inputs
        $name = trim($_POST['first_name']) . ' ' . trim($_POST['last_name']); // Combine first and last name
        $email = trim($_POST['email']);
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : null; // Optional phone number
        $message = trim($_POST['message']);

        // Validate inputs
        $errors = [];

        // Name validation
        if (empty($name)) {
            $errors[] = "Name is required.";
        }

        // Email validation
        if (empty($email)) {
            $errors[] = "Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }

        // Message validation
        if (empty($message)) {
            $errors[] = "Message is required.";
        }

        // If there are no errors, proceed with database insertion
        if (empty($errors)) {
            // Prepare SQL insert query
            $sql = "INSERT INTO contact_messages (name, email, subject, message, agent_id, property_id) 
                    VALUES (:name, :email, :subject, :message, :agent_id, :property_id)";
            $stmt = $pdo->prepare($sql);

            // Subject could be a predefined string, or you can make it dynamic
            $subject = "Contact Inquiry"; // Default subject

            // Bind parameters
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
            $stmt->bindParam(':message', $message, PDO::PARAM_STR);
            $stmt->bindParam(':agent_id', $agent_id, PDO::PARAM_INT); // Bind agent_id
            $stmt->bindParam(':property_id', $property_id, PDO::PARAM_INT); // Bind property_id

            // Execute the query
            $stmt->execute();

            // Set success message in session and redirect
            $_SESSION['success_message'] = "Your message has been sent successfully! We will get back to you soon.";
            header("Location: contact.php");
            exit();
        } else {
            // Set error messages in session
            $_SESSION['error_messages'] = $errors;
            header("Location: contact.php");
            exit();
        }
    } else {
        $_SESSION['error_messages'] = ["All required fields must be filled out."];
        header("Location: contact.php");
        exit();
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horizon Estates | Premium Real Estate</title>

    <!-- Favicon Links -->
    <link rel="apple-touch-icon" sizes="180x180" href="Fonts/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="Fonts/favicon-32x32.png">

    <!-- CSS Links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
   
</head>
<body>

    <!-- Success and Error Messages -->
    <?php
    if (isset($_SESSION['success_message'])):
    ?>
        <div class="alert alert-success">
            <?php
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php
    endif;

    if (isset($_SESSION['error_messages'])):
        foreach ($_SESSION['error_messages'] as $error):
    ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php
        endforeach;
        unset($_SESSION['error_messages']);
    endif;
    ?>

    <!-- Header -->
    <header class="bg-white shadow-sm py-5">
        <div class="container">
            <h1 class="display-4 fw-bold">Contact Us</h1>
            <p class="lead text-muted">We're here to help you find your dream home</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="py-5">
        <div class="container">
            <div class="row g-4">
                <!-- Contact Information -->
                <div class="col-lg-5">
                    <h2 class="h3 mb-4">Get in Touch</h2>
                    
                    <!-- Contact Cards -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h3 class="h6 mb-1">Office Location</h3>
                                    <p class="mb-0 text-muted">
                                        <a href="https://www.google.com/maps?q=123+Luxury+Lane,+Suite+100,+Premium+City,+ST+12345" target="_blank" class="text-muted text-decoration-none">123 Luxury Lane, Suite 100<br> Premium City, ST 12345</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <h3 class="h6 mb-1">Phone</h3>
                                    <p class="mb-0 text-muted">
                                        <a href="tel:+15551234567" class="text-muted text-decoration-none">(555) 123-4567</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <h3 class="h6 mb-1">Email</h3>
                                    <p class="mb-0 text-muted">
                                        <a href="mailto:contact@luxuryrealestate.com" class="text-muted text-decoration-none">contact@luxuryrealestate.com</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <h3 class="h6 mb-1">Business Hours</h3>
                                    <p class="mb-0 text-muted">
                                        Mon-Fri: 9:00 AM - 6:00 PM<br>
                                        Sat: 10:00 AM - 4:00 PM
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="col-lg-7">
                    <div class="card shadow-sm">
                <div class="card-body p-4">
                    <?php if (isset($agent_name)): ?>
                        <h2 class="h3 mb-4">Send <?php echo htmlspecialchars($agent_name); ?> a Message</h2>
                    <?php else: ?>
                        <h2 class="h3 mb-4">Send Us a Message</h2>
                    <?php endif; ?>

                    <?php
                    if (isset($_GET['property_id'])) {
                        $property_id = $_GET['property_id'];
                        $_SESSION['property_id'] = $property_id;

                        $query = "SELECT title, price, city, state, property_type, listing_type, status, description
                                FROM properties WHERE property_id = ?";
                        $stmt = $pdo->prepare($query);
                        $stmt->execute([$property_id]);
                        $property = $stmt->fetch();

                        if ($property): ?>
                            <section class="property-section p-4 bg-light rounded-4 shadow-sm mb-4">
                                <h2 class="h5 mb-4 text-center text-primary fw-bold">Property Overview</h2>
                                <div class="card border-0 bg-transparent p-3">
                                    <div class="card-body p-0">
                                        <h6 class="fw-bold mb-3"><?php echo htmlspecialchars($property['title']); ?></h6>
                                        <div class="d-flex flex-column gap-2">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                                <span class="text-muted"><?php echo htmlspecialchars($property['city'] . ', ' . $property['state']); ?></span>
                                            </div>
                                            <div class="mt-2">
                                                <span class="h5 text-primary fw-bold">$<?php echo number_format($property['price'], 2); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        <?php else: ?>
                            <p class="text-center text-muted">Property details not found.</p>
                        <?php endif;
                    } else {
                        echo '<p class="text-center text-muted"></p>';
                    } ?>

                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="first_name" placeholder="John" required>
                            </div>
                            <div class="col-md-6">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="last_name" placeholder="Doe" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="john@example.com" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="(555) 123-4567">
                            </div>
                            <div class="col-12">
                                <label for="message" class="form-label">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5" placeholder="How can we help you?" required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill">Send Message</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Map Section -->
        <section class="bg-light py-5">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body p-0">
                                <div class="ratio ratio-21x9">
                                    <iframe
                                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3024.268520576145!2d-74.0060!3d40.7128!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDDCsDQyJzQ2LjEiTiA3NMKwMDAnMjEuNiJX!5e0!3m2!1sen!2sus!4v1639025800000!5m2!1sen!2sus"
                                        style="border:0;"
                                        allowfullscreen=""
                                        loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade"
                                    ></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <?php include "footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>