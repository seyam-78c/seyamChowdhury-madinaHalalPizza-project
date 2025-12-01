<?php
// Set error handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/php-error.log');
error_reporting(E_ALL);

// Indicate the content type for JSON responses
header('Content-Type: application/json');

session_start();
require_once 'connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Decode the incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Validate the required parameters
if (!isset($data['productId'], $data['quantity']) || !is_numeric($data['productId']) || !is_numeric($data['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$productId = (int)$data['productId'];
$quantity = (int)$data['quantity'];
$toppings = isset($data['toppings']) ? $data['toppings'] : [];
$sauces = isset($data['sauces']) ? $data['sauces'] : [];
$pops = isset($data['pops']) ? $data['pops'] : [];
$userId = $_SESSION['user_id'];

// Insert into the cart
$query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("iii", $userId, $productId, $quantity);

if ($stmt->execute()) {
    $cartId = $stmt->insert_id;

    // Insert toppings
    if (!empty($toppings)) {
        $toppingQuery = "INSERT INTO cart_toppings (cart_id, topping_id) VALUES (?, ?)";
        $toppingStmt = $mysqli->prepare($toppingQuery);

        foreach ($toppings as $toppingId) {
            $toppingStmt->bind_param("ii", $cartId, $toppingId);
            $toppingStmt->execute();
        }
    }

    // Insert sauces
    if (!empty($sauces)) {
        $sauceQuery = "INSERT INTO cart_sauces (cart_id, sauce_id) VALUES (?, ?)";
        $sauceStmt = $mysqli->prepare($sauceQuery);

        foreach ($sauces as $sauceId) {
            $sauceStmt->bind_param("ii", $cartId, $sauceId);
            $sauceStmt->execute();
        }
    }

    // Insert pops
    if (!empty($pops)) {
        $popQuery = "INSERT INTO cart_pops (cart_id, pop_id) VALUES (?, ?)";
        $popStmt = $mysqli->prepare($popQuery);

        foreach ($pops as $popId) {
            $popStmt->bind_param("ii", $cartId, $popId);
            $popStmt->execute();
        }
    }

    echo json_encode(['success' => true, 'message' => 'Item added to cart']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add item to cart']);
}

// Close the statement and database connection
$stmt->close();
$mysqli->close();
?>