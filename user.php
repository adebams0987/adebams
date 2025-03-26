<?php
include "pdo.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
} else {
    $user_id = $_SESSION['user_id'];
    $pdo->prepare("UPDATE Users SET last_activity = NOW() WHERE user_id = ?")
        ->execute([$user_id]);
}

include "process_property.php";
include "filter.php";
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
    <link rel="stylesheet" href="CSS/User.css">

</head>
<body>

<?php include "header.php"; ?>

    <main>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['message']); ?>
            <?php unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']); ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    <main>
    <section class="hero d-flex align-items-center" style="background-image: url('Images/about5.jpg'); background-size: cover; background-position: center; height: 80vh;">
        <div class="container">
            <div class="row justify-content-center">
                <!-- Hero Header -->
                <div class="col-12 text-center text-white mb-5">
                    <h1 class="display-4 fw-bold">Find Your Dream Property</h1>
                    <p class="lead">Search and filter from our wide range of properties to find the perfect one for you.</p>
                </div>
            </div>
        </div>
    </section>

   <!-- Featured Properties -->
<div class="container py-5">
    <h2 class="text-center mb-4">Featured Properties</h2>
    <div class="row g-4">
        <?php 
        // Filter properties for 'available' status
        $availableProperties = array_filter($properties, function($property) {
            return $property['status'] === 'available';
        });
        ?>

        <?php if (empty($availableProperties)): ?>
            <div class="col-12 text-center">
                <div class="py-5">
                    <i class="fas fa-home fa-3x text-muted mb-3"></i>
                    <h3>No Available Properties Found</h3>
                    <p class="text-muted">Try adjusting your search criteria.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($availableProperties as $property): ?>
                <div class="col-md-4">
                    <div class="card property-card h-100 border-0 shadow-sm rounded-lg overflow-hidden">
                        <div class="position-relative">
                        <img src="uploads/<?= htmlspecialchars(!empty($property['images']) ? explode(',', $property['images'])[0] : 'api/placeholder/400/250'); ?>"
                                 alt="<?= htmlspecialchars($property['title']); ?>" 
                                 class="card-img-top">
                            <span class="position-absolute top-0 end-0 m-3 badge bg-success">
                                <?= ucfirst($property['status']); ?>
                            </span>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-primary"><?= htmlspecialchars($property['title']); ?></h5>
                            <p class="card-text text-muted mb-2"><?= htmlspecialchars(substr($property['description'], 0, 100)); ?>...</p>
                            <p class="card-text">
                                <strong>$<?= number_format($property['price'], 0); ?></strong><br>
                                <small><?= htmlspecialchars($property['address']); ?>, <?= htmlspecialchars($property['city']); ?>, <?= htmlspecialchars($property['state']); ?></small>
                            </p>
                            <a href="property_details.php?property_id=<?= $property['property_id']; ?>" class="btn btn-primary btn-block rounded-pill mt-auto">
                                View Details
                            </a>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form action="" method="POST" class="mt-3">
                                    <input type="hidden" name="property_id" value="<?= $property['property_id']; ?>">
                                    <button type="submit" class="btn btn-outline-primary btn-block rounded-pill">Save Property</button>
                                </form>
                            <?php else: ?>
                                <p class="text-muted mt-3">Please <a href="login.php">log in</a> to save properties.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>


    <!-- Quick Links -->
        <div class="bg-light py-5">
            <div class="container">
                <h2 class="mb-4 text-center">Quick Links</h2>
                <div class="row g-4">
                    <!-- Search Properties -->
                    <div class="col-md-3">
                        <div class="card text-center shadow-sm border-0 rounded-lg h-100">
                            <div class="card-body">
                                <i class="fas fa-search fa-3x text-primary mb-3"></i>
                                <h5 class="card-title mb-3">Search Properties</h5>
                                <p class="card-text mb-4">Find your perfect home with our advanced search.</p>
                                <a href="properties.php?status=available" class="btn btn-outline-primary rounded-pill">Search Now</a>
                            </div>
                        </div>
                    </div>
                    <!-- Mortgage Calculator -->
                    <div class="col-md-3">
                        <div class="card text-center shadow-sm border-0 rounded-lg h-100">
                            <div class="card-body">
                                <i class="fas fa-calculator fa-3x text-primary mb-3"></i>
                                <h5 class="card-title mb-3">Mortgage Calculator</h5>
                                <p class="card-text mb-4">Calculate your monthly payments.</p>
                                <a href="calculator.php" class="btn btn-outline-primary rounded-pill">Calculate</a>
                            </div>
                        </div>
                    </div>
                    <!-- Schedule Viewing -->
                    <div class="col-md-3">
                        <div class="card text-center shadow-sm border-0 rounded-lg h-100">
                            <div class="card-body">
                                <i class="fas fa-calendar-alt fa-3x text-primary mb-3"></i>
                                <h5 class="card-title mb-3">Schedule Viewing</h5>
                                <p class="card-text mb-4">Book a tour of your favorite properties.</p>
                                <a href="schedule.php" class="btn btn-outline-primary rounded-pill">Book a Tour</a>
                            </div>
                        </div>
                    </div>
                    <!-- Saved Properties -->
                    <div class="col-md-3">
                        <div class="card text-center shadow-sm border-0 rounded-lg h-100">
                            <div class="card-body">
                                <i class="fas fa-heart fa-3x text-primary mb-3"></i>
                                <h5 class="card-title mb-3">Saved Properties</h5>
                                <p class="card-text mb-4">View and manage your saved listings.</p>
                                <a href="saved_properties.php" class="btn btn-outline-primary rounded-pill">View Saved</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

         <!-- What We Do Section -->
        <section class="what-we-do-section py-5">
            <div class="container">
                <h2 class="section-title">What We Offer</h2>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="what-we-do-card text-center">
                            <i class="uil uil-building text-primary fs-2 mb-3"></i>
                            <h3 class="h5">Property Sales</h3>
                            <p class="small">Expert assistance in buying and selling residential and commercial properties.</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4" >
                        <div class="what-we-do-card text-center">
                            <i class="uil uil-rent text-primary fs-2 mb-3"></i>
                            <h3 class="h5">Property Rentals</h3>
                            <p class="small">Connecting tenants with their ideal homes, whether short-term or long-term.</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="what-we-do-card text-center">
                            <i class="uil uil-clipboard-alt text-primary fs-2 mb-3"></i>
                            <h3 class="h5">Real Estate Consulting</h3>
                            <p class="small">Personalized advice on market trends, investment strategies, and property valuations.</p>
                        </div>
                    </div>
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


        <!-- Pagination -->
        <!-- <div class="container">
            <div class="d-flex justify-content-center">
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i; ?>&search=<?= htmlspecialchars($searchQuery); ?>&status=<?= $filterStatus; ?>&state=<?= $filterState; ?>&type=<?= $filterType; ?>&price_min=<?= $filterPriceMin; ?>&price_max=<?= $filterPriceMax; ?>" class="btn btn-outline-primary <?= ($i == $page) ? 'active' : ''; ?>">
                            <?= $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        </div> -->

    </main>

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