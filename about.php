<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']); // Get the current page name
// Database connection
include "pdo.php"; 

/// Query to fetch agent details from the 'Users' and 'Agents' tables
$sql = "SELECT 
u.name, 
u.email, 
u.phone_number, 
a.profile_picture 
FROM Users u
JOIN Agents a ON u.user_id = a.user_id
WHERE u.role = 'agent'"; // Filter to get only agents

try {
// Prepare and execute the query
$stmt = $pdo->prepare($sql);
$stmt->execute();

// Fetch all agent details
$agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
die("Error fetching agent details: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Project Estates</title>
    <!-- Favicon Links -->
    <link rel="apple-touch-icon" sizes="180x180" href="Fonts/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="Fonts/favicon-32x32.png">

    <!-- CSS Links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="CSS/about.css" rel="stylesheet">

</head>
<body>

<?php include "header.php"; ?>

<!-- Modern Hero Section -->
<section class="hero-section d-flex align-items-center text-white">
    <div class="hero-content container text-center">
        <h1 class="hero-title mb-4">Reimagining Real Estate</h1>
        <p class="lead mb-5">Your trusted partner in creating exceptional property experiences</p>
        <a href="#about" class="btn btn-primary btn-lg">Discover More</a>
    </div>
    <div class="hero-background" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 1;">
        <div id="heroCarousel" class="carousel slide carousel-fade h-100" data-bs-ride="carousel">
            <div class="carousel-inner h-100">
                <div class="carousel-item active h-100">
                    <img src="Images/about2.jpg" class="d-block w-100 h-100" style="object-fit: cover;" alt="Modern real estate">
                </div>
                <div class="carousel-item h-100">
                    <img src="Images/about3.jpg" class="d-block w-100 h-100" style="object-fit: cover;" alt="Luxury properties">
                </div>
                <div class="carousel-item h-100">
                    <img src="Images/about5.jpg" class="d-block w-100 h-100" style="object-fit: cover;" alt="Property investments">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="about" class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="Images/team.jpg" alt="Our team" class="img-fluid rounded-3 shadow-lg">
            </div>
            <div class="col-lg-6">
                <h2 class="display-4 fw-bold mb-4">Who We Are</h2>
                <div class="about-content">
                    <p class="mb-4">At Project Estates, we're revolutionizing the real estate experience through innovation, integrity, and exceptional service. Our team of industry experts combines deep market knowledge with cutting-edge technology to deliver unparalleled results.</p>
                    <p>We understand that every property journey is unique, which is why we offer personalized solutions tailored to your specific needs. Whether you're buying your dream home, selling a property, or seeking the perfect investment opportunity, we're here to guide you every step of the way.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center section-title mb-5">Our Core Values</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="value-card text-center">
                    <div class="value-icon">
                        <i class="fas fa-handshake fa-2x text-primary"></i>
                    </div>
                    <h3 class="h4 mb-3">Integrity</h3>
                    <p class="mb-0">Building trust through transparent and honest relationships with every client.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="value-card text-center">
                    <div class="value-icon">
                        <i class="fas fa-users fa-2x text-primary"></i>
                    </div>
                    <h3 class="h4 mb-3">Client Focus</h3>
                    <p class="mb-0">Putting your needs first and delivering exceptional, personalized service.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="value-card text-center">
                    <div class="value-icon">
                        <i class="fas fa-chart-line fa-2x text-primary"></i>
                    </div>
                    <h3 class="h4 mb-3">Excellence</h3>
                    <p class="mb-0">Striving for excellence in every aspect of our service delivery.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center section-title mb-5">Meet Our Expert Team</h2>
        <div class="row g-4">
            <?php if (empty($agents)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        Our team is growing! Check back soon to meet our experts.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($agents as $agent): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="agent-card card h-100">
                            <div class="card-body text-center p-4">
                                <div class="position-relative mb-4">
                                <img src="uploads/profile_pictures/<?= htmlspecialchars($agent['profile_picture']); ?>" 
                                        alt="<?= htmlspecialchars($agent['name']); ?>"
                                        class="rounded-circle shadow-sm"
                                        width="150" 
                                        height="150"
                                        style="object-fit: cover;"
                                    >
                                    <span class="position-absolute bottom-0 end-0 p-2 bg-success rounded-circle">
                                        <span class="visually-hidden">Active</span>
                                    </span>
                                </div>
                                
                                <h3 class="h4 mb-3"><?= htmlspecialchars($agent['name']); ?></h3>
                                
                                <div class="mb-4 text-muted">
                                    <p class="mb-2">
                                        <i class="fas fa-envelope me-2"></i>
                                        <?= htmlspecialchars($agent['email']); ?>
                                    </p>
                                    <p class="mb-0">
                                        <i class="fas fa-phone me-2"></i>
                                        <?= htmlspecialchars($agent['phone_number']); ?>
                                    </p>
                                </div>

                                <div class="social-links mb-4">
                                    <a href="#" class="text-decoration-none">
                                        <i class="fab fa-linkedin-in"></i>
                                    </a>
                                    <a href="#" class="text-decoration-none">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                    <a href="#" class="text-decoration-none">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                </div>

                                <a href="mailto:<?= htmlspecialchars($agent['email']); ?>" 
                                   class="btn btn-primary w-100">
                                    <i class="fas fa-envelope me-2"></i>
                                    Contact Me
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include "footer.php"; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>