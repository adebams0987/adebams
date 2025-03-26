<?php
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

// Bind pagination parameters for the count query
// $countStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
// $countStmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$countStmt->execute();
$totalProperties = $countStmt->fetchColumn();
$totalPages = ceil($totalProperties / $limit);

// Fetch states from the database
$sqlStates = "SELECT DISTINCT state FROM Properties WHERE state IS NOT NULL";
$stmtStates = $pdo->query($sqlStates);
$states = $stmtStates->fetchAll(PDO::FETCH_ASSOC);

// Fetch latest 5 available properties
$latestPropertiesSql = "SELECT * FROM Properties WHERE status = 'available' ORDER BY created_at DESC LIMIT 5";
$latestStmt = $pdo->query($latestPropertiesSql);
$latestProperties = $latestStmt->fetchAll(PDO::FETCH_ASSOC);

// Save property logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['property_id'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "You need to be logged in to save properties.";
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $property_id = (int)$_POST['property_id'];

    // Check if the property is already saved
    $checkStmt = $pdo->prepare("SELECT * FROM Saved_Properties WHERE user_id = :user_id AND property_id = :property_id");
    $checkStmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $checkStmt->bindValue(':property_id', $property_id, PDO::PARAM_INT);
    $checkStmt->execute();

    if ($checkStmt->rowCount() > 0) {
        $_SESSION['error'] = "You have already saved this property.";
    } else {
        // Insert the saved property into the Saved_Properties table
        $insertStmt = $pdo->prepare("INSERT INTO Saved_Properties (user_id, property_id) VALUES (:user_id, :property_id)");
        $insertStmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $insertStmt->bindValue(':property_id', $property_id, PDO::PARAM_INT);

        if ($insertStmt->execute()) {
            $_SESSION['message'] = "Property saved successfully!";
        } else {
            $_SESSION['error'] = "There was an error saving the property.";
        }
    }

    // Redirect back to the same page
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

?>

<?php
function time_ago($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    
    $minutes      = round($seconds / 60);           // value 60 is seconds
    $hours        = round($seconds / 3600);         // value 3600 is 60 minutes * 60 sec
    $days         = round($seconds / 86400);        // value 86400 is 24 hours * 60 minutes * 60 sec
    $weeks        = round($seconds / 604800);       // value 604800 is 7 days * 24 hours * 60 minutes * 60 sec
    $months       = round($seconds / 2629440);      // value 2629440 is (365.25 days * 24 hours * 60 minutes * 60 sec) / 12 months
    $years        = round($seconds / 31553280);     // value 31553280 is (365.25 days * 24 hours * 60 minutes * 60 sec)

    if ($seconds <= 60) {
        return "Just Now";
    } else if ($minutes <= 60) {
        if ($minutes == 1) {
            return "one minute ago";
        } else {
            return "$minutes minutes ago";
        }
    } else if ($hours <= 24) {
        if ($hours == 1) {
            return "an hour ago";
        } else {
            return "$hours hours ago";
        }
    } else if ($days <= 7) {
        if ($days == 1) {
            return "yesterday";
        } else {
            return "$days days ago";
        }
    } else if ($weeks <= 4.3) { // 4.3 == 30/7
        if ($weeks == 1) {
            return "one week ago";
        } else {
            return "$weeks weeks ago";
        }
    } else if ($months <= 12) {
        if ($months == 1) {
            return "one month ago";
        } else {
            return "$months months ago";
        }
    } else {
        if ($years == 1) {
            return "one year ago";
        } else {
            return "$years years ago";
        }
    }
}
?>
