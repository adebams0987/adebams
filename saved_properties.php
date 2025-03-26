<?php
session_start();
include "pdo.php";

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Retrieve the property ID from the URL (e.g., passed as a query parameter)
if (isset($_GET['property_id'])) {
    $_SESSION['property_id'] = $_GET['property_id'];  // Store the property ID in the session
}

$user_id = $_SESSION['user_id']; // Retrieve the logged-in user's ID

// Query to fetch saved properties with their details and the first image
$query = "SELECT 
        properties.property_id,
        properties.title,
        properties.description,
        properties.price,
        properties.address,
        properties.city,
        properties.state,
        properties.zip_code,
        properties.property_type,
        properties.listing_type,
        properties.status,
        properties.latitude,
        properties.longitude,
        properties.created_at,
        (SELECT image_url FROM Property_Images WHERE property_id = properties.property_id LIMIT 1) AS image_url
    FROM 
        saved_properties
    JOIN 
        Properties ON saved_properties.property_id = properties.property_id
    WHERE 
        saved_properties.user_id = ?
    ORDER BY 
        saved_properties.saved_at DESC;";

// Prepare and execute the query
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$savedProperties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Properties | Project Estates</title>

    <!-- Favicon Links -->
    <link rel="apple-touch-icon" sizes="180x180" href="Fonts/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="Fonts/favicon-32x32.png">

    <!-- CSS Links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                        url('Images/about3.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 4rem 0;
        }

        .property-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .property-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .property-card .card-img-top {
            height: 200px;
            object-fit: cover;
        }

        .property-card .badge {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
        }

        .property-card .btn-block {
            width: 100%;
        }

        .property-card .btn {
            text-transform: uppercase;
            font-weight: 600;
        }

        h2 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: #333;
        }

        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .card-body i {
            transition: color 0.3s ease;
        }

        .card-body i:hover {
            color: #007bff;
        }

        .btn-outline-primary {
            border-radius: 50px;
            padding: 10px 25px;
        }

        .card-title {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .card-text {
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <?php include "header.php"; ?>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col">
                <h1 class="mb-0">Saved Properties</h1>
                <p class="text-muted">Your favorite listings in one place</p>
            </div>
        </div>

        <div class="row g-4">
            <?php if (empty($savedProperties)): ?>
                <div class="col-12 text-center">
                    <div class="py-5">
                        <i class="fas fa-home fa-3x text-muted mb-3"></i>
                        <h3>No Saved Properties Found</h3>
                        <p class="text-muted">Start saving your favorite properties!</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($savedProperties as $property): ?>
                    <div class="col-md-6">
                        <div class="card property-card h-100 border-0 shadow-sm rounded-lg overflow-hidden">
                            <div class="position-relative">
                            <img src="uploads/<?= htmlspecialchars($property['image_url'] ?? 'api/placeholder/400/250'); ?>"
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
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include "footer.php"; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>
