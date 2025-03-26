<?php
// Database configuration
$host = 'localhost'; // Database host
$dbname = 'project'; // Database name
$username = 'Maleek'; // Database username
$password = 'maleekdb'; // Database password

try {
    // Create a PDO instance with the corrected connection string
    $pdo = new PDO("mysql:host=$host;port=3306;dbname=$dbname;charset=utf8", $username, $password);

    // Set the PDO error mode to exception for better error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // In case of connection error, display message
    echo "Connection failed: " . $e->getMessage();
}
?>
