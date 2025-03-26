<?php
session_start();
include "pdo.php";

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    exit("You must be logged in to view property details.");
}

$user_id = $_SESSION['user_id'];

// Get property_id from GET request or session
if (!isset($_GET['property_id'])) {
    exit("Property ID not specified.");
}

$property_id = $_GET['property_id'];
$_SESSION['property_id'] = $property_id;

// Fetch property details with agent info and main image
$query = "SELECT 
            p.*, 
            u.name AS agent_name, 
            a.agent_id, a.profile_picture, a.agency_name, 
            u.phone_number AS agent_phone_number, 
            u.email AS agent_email,
            (SELECT pi.image_url FROM Property_Images pi WHERE pi.property_id = p.property_id ORDER BY pi.image_id ASC LIMIT 1) AS image_url
          FROM properties p
          JOIN agents a ON p.agent_id = a.agent_id
          JOIN users u ON a.user_id = u.user_id
          WHERE p.property_id = :property_id";

$stmt = $pdo->prepare($query);
$stmt->execute(['property_id' => $property_id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    exit("Property not found.");
}

$_SESSION['agent_id'] = $property['agent_id'];

$stmt_images = $pdo->prepare("
    SELECT image_url 
    FROM Property_Images 
    WHERE property_id = :property_id 
    ORDER BY image_id ASC
");
$stmt_images->execute(['property_id' => $property_id]);
$images = $stmt_images->fetchAll(PDO::FETCH_ASSOC);

// Debug images (add this temporarily)
error_log('Number of images found: ' . count($images));
foreach ($images as $image) {
    error_log('Image URL: ' . $image['image_url']);
}

// Check if property is already saved
$stmt = $pdo->prepare("SELECT 1 FROM saved_properties WHERE user_id = :user_id AND property_id = :property_id");
$stmt->execute(['user_id' => $user_id, 'property_id' => $property_id]);
$saved_property = $stmt->fetchColumn();

// Handle save action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_property']) && !$saved_property) {
    $stmt = $pdo->prepare("INSERT INTO saved_properties (user_id, property_id) VALUES (:user_id, :property_id)");
    $stmt->execute(['user_id' => $user_id, 'property_id' => $property_id]);
    header("Location: property_details.php?property_id=$property_id");
    exit;
}

// Handle unsave action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unsave_property']) && $saved_property) {
    $stmt = $pdo->prepare("DELETE FROM saved_properties WHERE user_id = :user_id AND property_id = :property_id");
    $stmt->execute(['user_id' => $user_id, 'property_id' => $property_id]);
    header("Location: property_details.php?property_id=$property_id");
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Details</title>

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
    <?php include "header.php"; ?>

    <div class="property-container">
        <!-- Fetch all images for the current property -->
        <?php
        $query = "SELECT * FROM Property_Images WHERE property_id = :property_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':property_id', $property['property_id']);
        $stmt->execute();
        $images = $stmt->fetchAll();
        ?>

        <!-- Bootstrap Carousel -->
        <div id="propertyCarousel-<?php echo $property['property_id']; ?>" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($images as $index => $image): ?>
                <div class="carousel-item <?php echo ($index === 0) ? 'active' : ''; ?>">
                    <img src="uploads/<?php echo htmlspecialchars($image['image_url'] ?? 'api/placeholder/400/300'); ?>" class="d-inline w-100" alt="Property Image">
                </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel-<?php echo $property['property_id']; ?>" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel-<?php echo $property['property_id']; ?>" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
        <div class="container mt-4">
            <div class="row">
                <!-- Main Content (left side) -->
                <?php if (isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])): ?>
                <div class="col-lg-8">
                <?php else: ?>
                <div class="col-lg-12">
                <?php endif; ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h1 class="card-title h2"><?= htmlspecialchars($property['title']) ?></h1>
                                <?php if (isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])): ?>
                                <form method="POST">
                                        <?php if ($saved_property): ?>
                                            <button type="submit" name="unsave_property" class="btn btn-danger">
                                                <i class="fas fa-heart-broken"></i> Unsave
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" name="save_property" class="btn btn-outline-danger">
                                                <i class="fas fa-heart"></i> Save
                                            </button>
                                        <?php endif; ?>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                            <h2 class="text-primary mb-3">$<?= number_format($property['price'], 2) ?></h2>
                            <p class="text-muted">
                                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($property['address']) ?>, <?= htmlspecialchars($property['city']) ?>, <?= htmlspecialchars($property['state']) ?> <?= htmlspecialchars($property['zip_code']) ?>
                            </p>

                            <hr>
                            <h3>Description</h3>
                            <p class="mb-4"><?= nl2br(htmlspecialchars($property['description'])) ?></p>

                            <h3>Property Features</h3>
                            <div class="row mb-4">
                                <?php 
                                $features = explode(',', $property['features']); 
                                $features = array_filter(array_map('trim', $features)); // Remove empty features and trim spaces
                                $total_features = count($features);

                                if ($total_features > 0): 
                                    $sliced_features = array_chunk($features, 5); 
                                    foreach ($sliced_features as $column): ?>
                                        <div class="col-md-4">
                                            <ul class="list-unstyled">
                                                <?php foreach ($column as $feature): ?>
                                                    <li><i class="fas fa-check text-success"></i> <?= htmlspecialchars($feature) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endforeach; 
                                else: ?>
                                    <p>No features available for this property.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar (right side) -->
                <?php if (isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])): ?>
                <div class="col-lg-4">
                    <!-- Agent Info -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body text-center">
                            <img src="<?= htmlspecialchars($property['profile_picture'] ?: '/api/placeholder/150/150') ?>" class="rounded-circle mb-3" alt="Agent Photo">
                            <h4><?= htmlspecialchars($property['agent_name']) ?></h4>
                            <p class="text-muted"><?= htmlspecialchars($property['agency_name']) ?></p>
                            <p><i class="fas fa-phone"></i> <?= htmlspecialchars($property['agent_phone_number'] ?: 'N/A') ?></p>
                            <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($property['agent_email'] ?: 'N/A') ?></p>

                            <a href="contact.php?agent_id=<?= $property['agent_id']; ?>&property_id=<?= $property['property_id']; ?>" class="btn btn-primary w-100 mb-2">
                                Contact Agent
                            </a>

                            <a href="schedule.php?agent_id=<?= $property['agent_id']; ?>&property_id=<?= $property['property_id']; ?>" class="btn btn-primary w-100">
                                Schedule a Tour
                            </a>
                        </div>
                    </div>

                    <!-- Mortgage Calculator -->
                    <div class="card shadow-sm mb-6">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Mortgage Calculator</h5>
                            <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#mortgageCalculator" aria-expanded="false" aria-controls="mortgageCalculator">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                        <div id="mortgageCalculator" class="collapse">
                            <div class="card-body">
                                <form id="mortgageForm">
                                    <div class="mb-3">
                                        <label for="loanAmount" class="form-label">Loan Amount ($)</label>
                                        <input type="number" class="form-control" id="loanAmount" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="interestRate" class="form-label">Annual Interest Rate (%)</label>
                                        <input type="number" class="form-control" id="interestRate" step="0.1" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="loanTerm" class="form-label">Loan Term (years)</label>
                                        <input type="number" class="form-control" id="loanTerm" required>
                                    </div>
                                    <button type="button" class="btn btn-primary w-100" id="calculateButton">Calculate</button>
                                    <div class="mt-4">
                                        <h6 class="text-center">Results</h6>
                                        <p id="monthlyPayment" class="output text-center"></p>
                                        <p id="totalPayment" class="output text-center"></p>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <!-- Map Section -->
    <section class="bg-light py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body p-0">
                            <h3>Location</h3>
                            <div class="ratio ratio-21x9">
                                <iframe 
                                    src="https://www.google.com/maps/embed/v1/place?key=AIzaSyDkpu7EwJeB8mpTuyKMYTh1c5_l9H9pKy4&q=<?= urlencode($property['latitude']) ?>,<?= urlencode($property['longitude']) ?>" 
                                    style="border:0;"
                                    allowfullscreen=""
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include "footer.php"; ?>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('calculateButton').addEventListener('click', function() {
        const loanAmount = parseFloat(document.getElementById('loanAmount').value);
        const interestRate = parseFloat(document.getElementById('interestRate').value) / 100 / 12;
        const loanTerm = parseInt(document.getElementById('loanTerm').value) * 12;

        if (isNaN(loanAmount) || isNaN(interestRate) || isNaN(loanTerm) || loanAmount <= 0 || interestRate <= 0 || loanTerm <= 0) {
            alert("Please fill out all fields with valid values.");
            return;
        }

        const monthlyPayment = (loanAmount * interestRate) / (1 - Math.pow(1 + interestRate, -loanTerm));
        const totalPayment = monthlyPayment * loanTerm;

        document.getElementById('monthlyPayment').innerText = `Monthly Payment: $${monthlyPayment.toFixed(2)}`;
        document.getElementById('totalPayment').innerText = `Total Payment: $${totalPayment.toFixed(2)}`;
    });
    </script>
</body>
</html>

