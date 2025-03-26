<?php
session_start();
include "pdo.php";

// Initialize filter values
$searchQuery = $_GET['search'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$filterState = $_GET['state'] ?? '';
$filterType = $_GET['type'] ?? '';
$filterPriceMin = $_GET['price_min'] ?? '';
$filterPriceMax = $_GET['price_max'] ?? '';

// Pagination setup
$limit = 10; // Number of properties per page
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

// Base SQL condition for filters
$whereClauses = ['1 = 1'];
$params = [];

// Add filters to the WHERE clause only if they are set
if ($searchQuery) {
    $whereClauses[] = "(Properties.title LIKE :search OR Properties.description LIKE :search)";
    $params[':search'] = '%' . $searchQuery . '%';
}
if ($filterStatus) {
    $whereClauses[] = "Properties.status = :status";
    $params[':status'] = $filterStatus;
}
if ($filterState) {
    $whereClauses[] = "Properties.state = :state";
    $params[':state'] = $filterState;
}
if ($filterType) {
    $whereClauses[] = "Properties.property_type = :type";
    $params[':type'] = $filterType;
}
if (is_numeric($filterPriceMin)) {
    $whereClauses[] = "Properties.price >= :price_min";
    $params[':price_min'] = $filterPriceMin;
}
if (is_numeric($filterPriceMax)) {
    $whereClauses[] = "Properties.price <= :price_max";
    $params[':price_max'] = $filterPriceMax;
}

// Main query to fetch properties
$sql = "SELECT 
            Properties.property_id, 
            Properties.title, 
            Properties.description, 
            Properties.price, 
            Properties.address, 
            Properties.city, 
            Properties.state, 
            Properties.zip_code, 
            Properties.property_type,
            Properties.listing_type, 
            Properties.status, 
            Properties.latitude, 
            Properties.longitude, 
            GROUP_CONCAT(Property_Images.image_url) AS images
        FROM Properties
        LEFT JOIN Property_Images ON Properties.property_id = Property_Images.property_id
        WHERE " . implode(' AND ', $whereClauses) . "
        AND Properties.status = 'available' 
        AND Properties.listing_type = 'rent'
        GROUP BY Properties.property_id
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);

// Bind parameters for main query
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->execute();
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query to get the total number of matching properties (without LIMIT)
$sqlTotal = "SELECT COUNT(DISTINCT Properties.property_id) AS total 
             FROM Properties
             LEFT JOIN Property_Images ON Properties.property_id = Property_Images.property_id
             WHERE " . implode(' AND ', $whereClauses) . "
             AND Properties.status = 'available' 
             AND Properties.listing_type = 'sale'";

$stmtTotal = $pdo->prepare($sqlTotal);
foreach ($params as $key => $value) {
    $stmtTotal->bindValue($key, $value);
}

$stmtTotal->execute();
$totalResults = $stmtTotal->fetchColumn();
$totalPages = ceil($totalResults / $limit);

$sqlStates = "SELECT DISTINCT state FROM Properties";
$stmtStates = $pdo->prepare($sqlStates);
$stmtStates->execute();
$states = $stmtStates->fetchAll(PDO::FETCH_ASSOC);

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

    <section class="hero bg-cover bg-center text-white d-flex align-items-center" style="background-image: url('Images/about3.jpg'); background-size: cover; background-position: center; height: 70vh;">
    <div class="container">
        <div class="row justify-content-center text-center">
            <!-- Hero Heading -->
            <div class="col-12">
                <h1 class="display-4 mb-4 fw-bold">Find Your Dream Property</h1>
                <p class="lead mb-5">Discover the perfect property for you with our wide selection of homes, apartments, and commercial spaces.</p>
            </div>
            <!-- Search and Filter Form -->
            <div class="col-lg-8 col-md-10">
                <div class="bg-light p-4 rounded shadow-sm">
                    <form method="GET">
                        <div class="row g-3 align-items-center">
                            <!-- Search Bar -->
                            <div class="col-md-8">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0" name="search" placeholder="Search properties by title or description..." value="<?= htmlspecialchars($searchQuery); ?>">
                                </div>
                            </div>

                            <!-- Filters Button -->
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-secondary w-100" data-bs-toggle="collapse" data-bs-target="#filterOptions">
                                    <i class="fas fa-sliders-h me-2"></i> Filters
                                </button>
                            </div>

                            <!-- Submit Button -->
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Search</button>
                            </div>
                        </div>

                        <!-- Filters Section (Collapsed) -->
                        <div class="collapse mt-3" id="filterOptions">
                            <div class="card p-3">
                                <div class="row g-3">
                                    <!-- Status Filter -->
                                    <div class="col-md-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="status">
                                            <option value="">All Statuses</option>
                                            <option value="available" <?= $filterStatus == 'available' ? 'selected' : ''; ?>>Available</option>
                                            <option value="sold" <?= $filterStatus == 'sold' ? 'selected' : ''; ?>>Sold</option>
                                            <option value="rented" <?= $filterStatus == 'rented' ? 'selected' : ''; ?>>Rented</option>
                                        </select>
                                    </div>

                                    <!-- State Filter -->
                                    <div class="col-md-3">
                                        <label class="form-label">State</label>
                                        <select class="form-select" name="state">
                                            <option value="">All States</option>
                                            <?php foreach ($states as $state): ?>
                                                <option value="<?= htmlspecialchars($state['state']); ?>"
                                                    <?= $filterState == $state['state'] ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars($state['state']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Property Type Filter -->
                                    <div class="col-md-3">
                                        <label class="form-label">Property Type</label>
                                        <select class="form-select" name="type">
                                            <option value="">All Types</option>
                                            <option value="residential" <?= $filterType == 'residential' ? 'selected' : ''; ?>>Residential</option>
                                            <option value="commercial" <?= $filterType == 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                                        </select>
                                    </div>

                                    <!-- Price Range Filter -->
                                    <div class="col-md-3">
                                        <label class="form-label">Price Range</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="price_min" placeholder="Min"
                                                value="<?= htmlspecialchars($filterPriceMin); ?>">
                                            <input type="number" class="form-control" name="price_max" placeholder="Max"
                                                value="<?= htmlspecialchars($filterPriceMax); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>



    <<!-- Featured Properties -->
    <div class="container py-5">
        <h2 class="text-center mb-4">Available Properties</h2>
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
                        <properties class="text-muted">Try adjusting your search criteria.</properties>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($availableProperties as $property): ?>
                    <div class="col-md-6">
                        <div class="card property-card h-100 border-0 shadow-sm rounded-lg overflow-hidden">
                            <div class="position-relative">
                            <img src="uploads/<?= htmlspecialchars(explode(',', $property['images'])[0] ?? 'api/placeholder/400/250'); ?>" 
                                    alt="<?= htmlspecialchars($property['title']); ?>" 
                                    class="card-img-top">
                                <span class="position-absolute top-0 end-0 m-3 badge bg-success">
                                    <?= ucfirst($property['status']); ?>
                                </span>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title text-primary"><?= htmlspecialchars($property['title']); ?></h5>
                                <properties class="card-text text-muted mb-2"><?= htmlspecialchars(substr($property['description'], 0, 100)); ?>...</properties>
                                <properties class="card-text">
                                    <strong>$<?= number_format($property['price'], 0); ?></strong><br>
                                    <small><?= htmlspecialchars($property['address']); ?>, <?= htmlspecialchars($property['city']); ?>, <?= htmlspecialchars($property['state']); ?></small>
                                </properties>
                                <a href="property_details.php?property_id=<?= $property['property_id']; ?>" class="btn btn-primary btn-block rounded-pill mt-auto">
                                    View Details
                                </a>
                                
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <form action="" method="POST" class="mt-3">
                                        <input type="hidden" name="property_id" value="<?= $property['property_id']; ?>">
                                        <button type="submit" class="btn btn-outline-primary btn-block rounded-pill">Save Property</button>
                                    </form>
                                <?php else: ?>
                                    <properties class="text-muted mt-3">Please <a href="login.php">log in</a> to save properties.</properties>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pagination Section -->
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <!-- Previous Button -->
            <li class="page-item <?= ($page == 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?= $page - 1; ?>&search=<?= htmlspecialchars($searchQuery); ?>&status=<?= $filterStatus; ?>&state=<?= $filterState; ?>&type=<?= $filterType; ?>&price_min=<?= $filterPriceMin; ?>&price_max=<?= $filterPriceMax; ?>" tabindex="-1">Previous</a>
            </li>

            <!-- Page Number Buttons -->
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?= $i; ?>&search=<?= htmlspecialchars($searchQuery); ?>&status=<?= $filterStatus; ?>&state=<?= $filterState; ?>&type=<?= $filterType; ?>&price_min=<?= $filterPriceMin; ?>&price_max=<?= $filterPriceMax; ?>">
                        <?= $i; ?>
                    </a>
                </li>
            <?php endfor; ?>

            <!-- Next Button -->
            <li class="page-item <?= ($page == $totalPages) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?= $page + 1; ?>&search=<?= htmlspecialchars($searchQuery); ?>&status=<?= $filterStatus; ?>&state=<?= $filterState; ?>&type=<?= $filterType; ?>&price_min=<?= $filterPriceMin; ?>&price_max=<?= $filterPriceMax; ?>">Next</a>
            </li>
        </ul>
    </nav>

    


    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <?php include "footer.php"; ?>
</body>

</html>
