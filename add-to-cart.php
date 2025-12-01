<?php
session_start();
require_once 'connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in first']);
    exit;
}

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception('Invalid data received');
    }

    $customerId = $_SESSION['customer_id'];
    $productId = $data['product_id'];
    $quantity = $data['quantity'];

    // Validate customer
    $customerCheck = $mysqli->prepare("SELECT customer_id FROM customers WHERE customer_id = ?");
    $customerCheck->bind_param("i", $customerId);
    $customerCheck->execute();
    if ($customerCheck->get_result()->num_rows === 0) {
        throw new Exception('Invalid customer account');
    }

    // Validate product
    $productCheck = $mysqli->prepare("SELECT id, product_price FROM products WHERE id = ?");
    $productCheck->bind_param("i", $productId);
    $productCheck->execute();
    $productResult = $productCheck->get_result();
    if ($productResult->num_rows === 0) {
        throw new Exception('Invalid product');
    }
    
    $productData = $productResult->fetch_assoc();
    $basePrice = $productData['product_price'];

    $mysqli->begin_transaction();

    // Calculate total price including extra toppings
    $extraToppingsTotal = 0;
    if (!empty($data['extraToppings'])) {
        foreach ($data['extraToppings'] as $topping) {
            $extraToppingsTotal += $topping['price'];
        }
    }
    
    $totalPrice = ($basePrice + $extraToppingsTotal) * $quantity;

    // Insert cart item
    $stmt = $mysqli->prepare("INSERT INTO cart_items (customer_id, product_id, quantity, total_price, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiid", $customerId, $productId, $quantity, $totalPrice);
    $stmt->execute();
    
    $cartItemId = $mysqli->insert_id;

    // Insert regular toppings if any
    if (!empty($data['toppings'])) {
        $stmt = $mysqli->prepare("INSERT INTO cart_item_toppings (cart_item_id, pizza_number, topping_id) VALUES (?, ?, ?)");
        foreach ($data['toppings'] as $topping) {
            $stmt->bind_param("iii", $cartItemId, $topping['pizzaNumber'], $topping['toppingId']);
            $stmt->execute();
        }
    }

    // Insert extra toppings if any
    if (!empty($data['extraToppings'])) {
        $stmt = $mysqli->prepare("INSERT INTO extra_cart_item_toppings (cart_item_id, pizza_number, topping_id) VALUES (?, ?, ?)");
        foreach ($data['extraToppings'] as $topping) {
            $stmt->bind_param("iii", $cartItemId, $topping['pizzaNumber'], $topping['toppingId']);
            $stmt->execute();
        }
    }

    // Insert sauces if any
    if (!empty($data['sauces'])) {
        $stmt = $mysqli->prepare("INSERT INTO cart_item_sauces (cart_item_id, wing_number, sauce_id) VALUES (?, ?, ?)");
        foreach ($data['sauces'] as $sauce) {
            $stmt->bind_param("iii", $cartItemId, $sauce['wingNumber'], $sauce['sauceId']);
            $stmt->execute();
        }
    }

    // Insert pops if any
    if (!empty($data['pops'])) {
        $stmt = $mysqli->prepare("INSERT INTO cart_item_pops (cart_item_id, pop_number, pop_id) VALUES (?, ?, ?)");
        foreach ($data['pops'] as $pop) {
            $stmt->bind_param("iii", $cartItemId, $pop['popNumber'], $pop['popId']);
            $stmt->execute();
        }
    }

    $mysqli->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if (isset($mysqli)) {
        $mysqli->rollback();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($customerCheck)) $customerCheck->close();
    if (isset($productCheck)) $productCheck->close();
    if (isset($stmt)) $stmt->close();
    if (isset($mysqli)) $mysqli->close();
}
?>