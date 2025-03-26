<?php 
session_start();
include "header.php";
include "pdo.php";

try {
        $stmt = $pdo->prepare("SELECT 
        p.property_id, 
        p.title, 
        p.description, 
        p.price, 
        p.city, 
        (SELECT image_url FROM Property_Images 
        WHERE property_id = p.property_id 
        ORDER BY image_id ASC LIMIT 1) as image_url
    FROM Properties p
    WHERE p.status = 'available'
    ORDER BY p.created_at DESC
    LIMIT 5");
    $stmt->execute();
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch property statistics in a single query
    $stmt = $pdo->prepare("SELECT 
            SUM(status = 'available') AS available_count, 
            SUM(status = 'sold') AS sold_count, 
            SUM(status = 'rented') AS rented_count 
        FROM Properties
    ");
    $stmt->execute();
    $propertyStats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch total users
    // Modify the query to count only users with the role 'user'
    $stmt = $pdo->prepare("SELECT COUNT(*) AS user_count FROM Users WHERE role = 'user'");
    $stmt->execute();
    $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['user_count'];
} catch (PDOException $e) {
    // Log error and display a generic message
    error_log("Database Error: " . $e->getMessage());
    die("An error occurred. Please try again later.");
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
    <link rel="stylesheet" href="CSS/index.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

</head>

<body>
    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container position-relative z-2">
            <h1 class="display-4 fw-bold mb-4" data-aos="fade-up">Discover Your Dream Home</h1>
            <p class="lead mb-5" data-aos="fade-up" data-aos-delay="200">
                Connecting you with exceptional properties and investment opportunities
            </p>
            <a href="properties.php" class="btn btn-light btn-lg px-5 rounded-pill" data-aos="zoom-in">
                Explore Properties
            </a>
        </div>
    </section>

    <!-- Featured Properties Section -->
    <section id="featured-properties" class="py-5">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Featured Properties</h2>
            <div class="row">
                <?php if (!empty($properties)): ?>
                    <?php foreach ($properties as $property): ?>
                        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                            <div class="card property-card">
                            <img src="uploads/<?php echo htmlspecialchars($property['image_url']); ?>"   
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($property['title']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title text-center">
                                        <?php echo htmlspecialchars($property['title']); ?>
                                    </h5>
                                    <p class="card-text text-muted text-center">
                                        <?php echo htmlspecialchars($property['description']); ?>
                                    </p>
                                    <div class="d-flex justify-content-center align-items-center mt-3">
                                        <span class="h5 text-primary mb-0">
                                            $<?php echo number_format($property['price'], 2); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">No properties are available at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>



    <!-- What We Do Section -->
    <section class="what-we-do-section py-5">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">What We Offer</h2>
            <div class="row">
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="what-we-do-card text-center">
                        <i class="uil uil-building text-primary fs-2 mb-3"></i>
                        <h3 class="h5">Property Sales</h3>
                        <p class="small">Expert assistance in buying and selling residential and commercial properties.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="what-we-do-card text-center">
                        <i class="uil uil-rent text-primary fs-2 mb-3"></i>
                        <h3 class="h5">Property Rentals</h3>
                        <p class="small">Connecting tenants with their ideal homes, whether short-term or long-term.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="what-we-do-card text-center">
                        <i class="uil uil-clipboard-alt text-primary fs-2 mb-3"></i>
                        <h3 class="h5">Real Estate Consulting</h3>
                        <p class="small">Personalized advice on market trends, investment strategies, and property valuations.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="statistics-section py-5">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Our Achievements</h2>
            <div class="row">
                
                <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <a href="properties.php?status=available" class="text-decoration-none">
                        <div class="statistics-card text-center">
                            <i class="fas fa-building text-primary fs-2 mb-3"></i>
                            <h3 class="counter"><?php echo $propertyStats['available_count']; ?></h3>
                            <p class="text-dark">Available Properties</p>
                        </div>
                    </a>
                </div>

                <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <a href="properties.php?status=sold" class="text-decoration-none">
                        <div class="statistics-card text-center">
                            <i class="uil uil-check-circle text-primary fs-2 mb-3"></i>
                            <h3 class="counter"><?php echo $propertyStats['sold_count']; ?></h3>
                            <p>Sold Properties</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <a href="properties.php?status=rented" class="text-decoration-none">
                        <div class="statistics-card text-center">
                            <i class="fas fa-handshake text-primary fs-2 mb-3"></i>
                            <h3 class="counter"><?php echo $propertyStats['rented_count']; ?></h3>
                            <p>Rented Properties</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="statistics-card text-center">
                        <i class="uil uil-users-alt text-primary fs-2 mb-3"></i>
                        <h3 class="counter"><?php echo $userCount; ?></h3>
                        <p>Registered Users</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Testimonials Section -->
    <section class="testimonial-section py-5">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Client Testimonials</h2>
            <div class="row">
                <div class="col-md-4 mb-4" data-aos="fade-up">
                    <div class="testimonial-card">
                        <p>"Horizon Estates made finding my dream home incredibly smooth and enjoyable."</p>
                        <div class="d-flex align-items-center">
                            <img src="https://randomuser.me/api/portraits/women/44.jpg" class="rounded-circle me-3" width="50" height="50">
                            <div>
                                <h6 class="mb-0">Sarah Thompson</h6>
                                <small class="text-muted">First-time Homebuyer</small>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Additional testimonial cards -->
            </div>
        </div>
    </section>

    <!-- Contact CTA Section -->
    <section class="cta-section py-5 text-center">
        <div class="container">
            <h2 class="mb-4">Ready to Find Your Perfect Property?</h2>
            <p class="lead mb-4">Our expert team is here to assist you every step of the way.</p>
            <a href="contact.php" class="btn btn-light btn-lg px-5 rounded-pill">Contact Us</a>
        </div>
    </section>

    <!-- Footer -->
    <?php include "footer.php"; ?>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
</body>
</html>
