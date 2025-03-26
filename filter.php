<?php
// Initialize filter values
$searchQuery = $_GET['search'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$filterState = $_GET['state'] ?? '';
$filterType = $_GET['type'] ?? '';
$filterPriceMin = $_GET['price_min'] ?? '';
$filterPriceMax = $_GET['price_max'] ?? '';


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

// Fetch states from the database
$sqlStates = "SELECT DISTINCT state FROM Properties WHERE state IS NOT NULL";
$stmtStates = $pdo->query($sqlStates);
$states = $stmtStates->fetchAll(PDO::FETCH_ASSOC);
?>