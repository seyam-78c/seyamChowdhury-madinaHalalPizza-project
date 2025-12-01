<?php
session_start();
require_once 'connection.php';

$cartItems = [];
$subtotal = 0;

if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];

    $stmt = $mysqli->prepare("
        SELECT 
            ci.id as cart_item_id,
            ci.quantity,
            ci.total_price,
            p.id as product_id,
            p.product_title,
            p.product_price,
            p.image_path,
            pq.num_pizza,
            pq.num_wings,
            pq.num_pops
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        LEFT JOIN product_quantities pq ON p.id = pq.product_id
        WHERE ci.customer_id = ?
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $cartItemId = $row['cart_item_id'];
        $cartItem = $row;
        
        // Get toppings
        if ($row['num_pizza'] > 0) {
            $toppingsStmt = $mysqli->prepare("
                SELECT 
                    cit.pizza_number,
                    pt.topping_name
                FROM cart_item_toppings cit
                JOIN pizza_toppings pt ON cit.topping_id = pt.id
                WHERE cit.cart_item_id = ?
                ORDER BY cit.pizza_number
            ");
            
            $toppingsStmt->bind_param("i", $cartItemId);
            $toppingsStmt->execute();
            $toppingsResult = $toppingsStmt->get_result();
            
            $cartItem['toppings'] = [];
            while ($topping = $toppingsResult->fetch_assoc()) {
                if (!isset($cartItem['toppings'][$topping['pizza_number']])) {
                    $cartItem['toppings'][$topping['pizza_number']] = [];
                }
                $cartItem['toppings'][$topping['pizza_number']][] = $topping['topping_name'];
            }
            $toppingsStmt->close();

            // Get extra toppings
            $extraToppingsStmt = $mysqli->prepare("
                SELECT 
                    ecit.pizza_number,
                    pt.topping_name
                FROM extra_cart_item_toppings ecit
                JOIN pizza_toppings pt ON ecit.topping_id = pt.id
                WHERE ecit.cart_item_id = ?
                ORDER BY ecit.pizza_number
            ");
            $extraToppingsStmt->bind_param("i", $cartItemId);
            $extraToppingsStmt->execute();
            $extraToppingsResult = $extraToppingsStmt->get_result();
            
            $cartItem['extra_toppings'] = [];
            while ($extraTopping = $extraToppingsResult->fetch_assoc()) {
                if (!isset($cartItem['extra_toppings'][$extraTopping['pizza_number']])) {
                    $cartItem['extra_toppings'][$extraTopping['pizza_number']] = [];
                }
                $cartItem['extra_toppings'][$extraTopping['pizza_number']][] = $extraTopping['topping_name'];
            }
            $extraToppingsStmt->close();
        }
        
        // Get sauces
        if ($row['num_wings'] > 0) {
            $saucesStmt = $mysqli->prepare("
                SELECT 
                    cis.wing_number,
                    s.sauce_name
                FROM cart_item_sauces cis
                JOIN sauces s ON cis.sauce_id = s.id
                WHERE cis.cart_item_id = ?
                ORDER BY cis.wing_number
            ");
            $saucesStmt->bind_param("i", $cartItemId);
            $saucesStmt->execute();
            $saucesResult = $saucesStmt->get_result();
            
            $cartItem['sauces'] = [];
            while ($sauce = $saucesResult->fetch_assoc()) {
                $cartItem['sauces'][$sauce['wing_number']] = $sauce['sauce_name'];
            }
            $saucesStmt->close();
        }
        
        // Get pops
        if ($row['num_pops'] > 0) {
            $popsStmt = $mysqli->prepare("
                SELECT 
                    cip.pop_number,
                    p.pop_name
                FROM cart_item_pops cip
                JOIN pops p ON cip.pop_id = p.id
                WHERE cip.cart_item_id = ?
                ORDER BY cip.pop_number
            ");
            $popsStmt->bind_param("i", $cartItemId);
            $popsStmt->execute();
            $popsResult = $popsStmt->get_result();
            
            $cartItem['pops'] = [];
            while ($pop = $popsResult->fetch_assoc()) {
                $cartItem['pops'][$pop['pop_number']] = $pop['pop_name'];
            }
            $popsStmt->close();
        }

        $cartItems[] = $cartItem;
        $subtotal += $row['total_price'];
    }
    $stmt->close();
}

$response = [
    'success' => true,
    'cartItems' => $cartItems,
    'subtotal' => $subtotal,
    'totalItems' => count($cartItems)
];

header('Content-Type: application/json');
echo json_encode($response);
?>