<?php
session_start();
include_once "header.php";
include "process_property.php";

// Include database connection
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
if ($filterPriceMin) {
    $whereClauses[] = "Properties.price >= :price_min";
    $params[':price_min'] = $filterPriceMin;
}
if ($filterPriceMax) {
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
            Properties.status, 
            Properties.latitude, 
            Properties.longitude, 
            GROUP_CONCAT(Property_Images.image_url) AS images
        FROM Properties
        LEFT JOIN Property_Images ON Properties.property_id = Property_Images.property_id
        WHERE " . implode(' AND ', $whereClauses) . "
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

// Count query to determine total properties for pagination
$countSql = "SELECT COUNT(DISTINCT Properties.property_id) 
             FROM Properties 
             LEFT JOIN Property_Images ON Properties.property_id = Property_Images.property_id
             WHERE " . implode(' AND ', $whereClauses);

$countStmt = $pdo->prepare($countSql);
foreach ($params as $key => $value) {
    $countStmt->bindValue($key, $value);
}

$countStmt->execute();
$totalProperties = $countStmt->fetchColumn();
$totalPages = ceil($totalProperties / $limit);

// Fetch states from the database
$sqlStates = "SELECT DISTINCT state FROM Properties WHERE state IS NOT NULL";
$stmtStates = $pdo->query($sqlStates);
$states = $stmtStates->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Properties Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="CSS/properties.css">
    
</head>
<body>

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

    <!-- Search Section -->
    <section class="search-section py-5 bg-transparent">
        <div class="container">
            <div class="search-container">
                <form class="row g-3" method="GET">
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
                        <button type="button" class="filter-button btn btn-outline-secondary w-100" data-bs-toggle="collapse" data-bs-target="#filterOptions">
                            <i class="fas fa-sliders-h me-2"></i> Filters
                        </button>
                    </div>
                    <!-- Submit Button -->
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
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
                                        <input type="number" class="form-control" name="price_min" placeholder="Min" value="<?= htmlspecialchars($filterPriceMin); ?>">
                                        <input type="number" class="form-control" name="price_max" placeholder="Max" value="<?= htmlspecialchars($filterPriceMax); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Properties Section -->
    <section class="container py-5">
        <div class="row g-4">
            <?php if (empty($properties)): ?>
                <div class="col-12 text-center">
                    <div class="py-5">
                        <i class="fas fa-home fa-3x text-muted mb-3"></i>
                        <h3>No Properties Found</h3>
                        <p class="text-muted">Try adjusting your search criteria</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($properties as $property): ?>
                    <div class="col-md-4">
                        <div class="card property-card h-100">
                            <div class="position-relative overflow-hidden">
                            <img src="uploads/<?php echo htmlspecialchars(explode(',', $property['images'])[0] ?? 'default.jpg'); ?>" 
                                 alt="<?= htmlspecialchars($property['title']); ?>" 
                                 class="card-img-top">
                                <span class="position-absolute top-0 end-0 m-3 property-badge badge-<?= $property['status']; ?>">
                                    <?= ucfirst($property['status']); ?>
                                </span>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-3"><?= htmlspecialchars($property['title']); ?></h5>
                                <p class="property-price mb-3">$<?= number_format($property['price'], 0); ?></p>
                                <p class="card-text text-muted mb-3"><?= htmlspecialchars(substr($property['description'], 0, 100)); ?>...</p>
                                <p class="property-location mb-4">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= htmlspecialchars($property['city']); ?>, <?= htmlspecialchars($property['state']); ?>
                                </p>
                                <a href="property_details.php?property_id=<?= $property['property_id']; ?>" 
                                class="view-details-btn btn mt-auto text-center">
                                    View Details
                                </a>
                                
                                <!-- Display "Saved" button if user is logged in -->
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <form action="" method="POST" class="mt-3 text-center">
                                        <input type="hidden" name="property_id" value="<?= $property['property_id']; ?>">
                                        <button type="submit" class="save-property-btn view-details-btn btn mt-auto text-center">
                                            Save Property
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-5">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?= $page - 1; ?>&<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" aria-label="Previous">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?= $i; ?>&<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                            <?= $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?= $page + 1; ?>&<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" aria-label="Next">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>

    <?php include "footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
