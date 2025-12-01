<?php
require_once 'header.php';
require_once 'connection.php';

// Restaurant coordinates
$restaurantLat = 43.68096692011496;
$restaurantLng = -79.33536456442678;

// Get user data including coordinates
$userData = [];
if (isset($_SESSION['customer_id'])) {
    $stmt = $mysqli->prepare("SELECT address, latitude, longitude FROM customers WHERE customer_id = ?");
    $stmt->bind_param("i", $_SESSION['customer_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
    $stmt->close();
}

// Make sure we have coordinates, even if null
if (!isset($userData['latitude'])) $userData['latitude'] = null;
if (!isset($userData['longitude'])) $userData['longitude'] = null;



$stmt = $mysqli->prepare("
    SELECT 
        ci.id as cart_item_id,
        ci.quantity,
        ci.total_price,
        p.id as product_id,
        p.product_title,
        p.product_price,
        pq.num_pizza,
        pq.num_wings,
        pq.num_pops
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    LEFT JOIN product_quantities pq ON p.id = pq.product_id
    WHERE ci.customer_id = ?
");

$stmt->bind_param("i", $_SESSION['customer_id']);
$stmt->execute();
$result = $stmt->get_result();
$cartItems = [];
while ($row = $result->fetch_assoc()) {
    $cartItemId = $row['cart_item_id'];
    $cartItems[$cartItemId] = $row;
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
        
        $cartItems[$cartItemId]['toppings'] = [];
        while ($topping = $toppingsResult->fetch_assoc()) {
            if (!isset($cartItems[$cartItemId]['toppings'][$topping['pizza_number']])) {
                $cartItems[$cartItemId]['toppings'][$topping['pizza_number']] = [];
            }
            $cartItems[$cartItemId]['toppings'][$topping['pizza_number']][] = $topping['topping_name'];
        }
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
        
        $cartItems[$cartItemId]['extra_toppings'] = [];
        while ($extraTopping = $extraToppingsResult->fetch_assoc()) {
            if (!isset($cartItems[$cartItemId]['extra_toppings'][$extraTopping['pizza_number']])) {
                $cartItems[$cartItemId]['extra_toppings'][$extraTopping['pizza_number']] = [];
            }
            $cartItems[$cartItemId]['extra_toppings'][$extraTopping['pizza_number']][] = $extraTopping['topping_name'];
        }
    }
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
        
        $cartItems[$cartItemId]['sauces'] = [];
        while ($sauce = $saucesResult->fetch_assoc()) {
            $cartItems[$cartItemId]['sauces'][$sauce['wing_number']] = $sauce['sauce_name'];
        }
    }
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
        
        $cartItems[$cartItemId]['pops'] = [];
        while ($pop = $popsResult->fetch_assoc()) {
            $cartItems[$cartItemId]['pops'][$pop['pop_number']] = $pop['pop_name'];
        }
    }
}


// Fetch Categories
$queryCategories = "SELECT * FROM categories";
$resultCategories = $mysqli->query($queryCategories);

// Pagination Setup
$limit = 12;  // Number of products per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter to show only products from category_id = 43
$categoryFilter = "WHERE category_id = 43";

// Fetch Products with Pagination
$queryProducts = "SELECT * FROM products $categoryFilter LIMIT $limit OFFSET $offset";
$resultProducts = $mysqli->query($queryProducts);

// Get total product count for pagination with category filter
$totalProductsQuery = "SELECT COUNT(*) as total FROM products $categoryFilter";
$totalProductsResult = $mysqli->query($totalProductsQuery);
$totalProducts = $totalProductsResult->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $limit);

?>



<!-- Breadcumb Section S T A R T -->
<div class="breadcumb-section">
    <div class="breadcumb-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="breadcumb-content">
                        <h1 class="breadcumb-title">Cart</h1>
                        <ul class="breadcumb-menu">
                            <li><a href="index.php">Home</a></li>
                            <li class="text-white">/</li>
                            <li class="active">Cart</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="th-cart-wrapper section-padding fix bg-white">
    <div class="container">
        <!-- Cart Table Section -->
        <form action="#" class="woocommerce-cart-form">
            <table class="cart_table">
                <thead>
                    <tr>
                        <th class="cart-col-image">Product Title</th>
                        <th class="cart-colname">Toppings</th>
                        <th class="cart-col-price">Sauces</th>
                        <th class="cart-col-quantity">Pops</th>
                        <th class="cart-col-quantity">Quantity</th>
                        <th class="cart-col-total">Price</th>
                        <th class="cart-col-total">Total</th>
                        <th class="cart-col-remove">Remove</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $subtotal = 0;
                    foreach ($cartItems as $item): 
                        $subtotal += $item['total_price'];
                    endforeach;
                    
                    // Fetch tax rate and calculate tax
                    $taxQuery = "SELECT tax_percentage FROM tax WHERE id = 1";
                    $taxResult = $mysqli->query($taxQuery);
                    $taxRate = $taxResult->fetch_assoc()['tax_percentage'] / 100; // Convert to decimal
                    $tax = $subtotal * $taxRate;
                    $total = $subtotal + $tax;
                    
                    // Reset cart items loop
                    $cartItemsKeys = array_keys($cartItems);
                    $currentIndex = 0;
                    foreach ($cartItems as $item):
                    ?>
                    <tr class="cart_item" data-cart-item-id="<?php echo $item['cart_item_id']; ?>">
                        <td><?php echo htmlspecialchars($item['product_title']); ?></td>
                        <td>
                            <?php
                            if (!empty($item['toppings'])) {
                                foreach ($item['toppings'] as $pizzaNum => $pizzaToppings) {
                                    echo "Pizza " . ($pizzaNum + 1) . ": " . 
                                         htmlspecialchars(implode(', ', $pizzaToppings)) . "<br>";
                                }
                            }
                            if (!empty($item['extra_toppings'])) {
                                foreach ($item['extra_toppings'] as $pizzaNum => $extraToppings) {
                                    echo "Pizza " . ($pizzaNum + 1) . " Extra Toppings: " . 
                                         htmlspecialchars(implode(', ', $extraToppings)) . "<br>";
                                }
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if (!empty($item['sauces'])) {
                                foreach ($item['sauces'] as $wingNum => $sauceName) {
                                    echo "Wing " . ($wingNum+1) . ": " . 
                                         htmlspecialchars($sauceName) . "<br>";
                                }
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if (!empty($item['pops'])) {
                                foreach ($item['pops'] as $popNum => $popName) {
                                    echo "Pop " . ($popNum+1) . ": " . 
                                         htmlspecialchars($popName) . "<br>";
                                }
                            }
                            ?>
                        </td>
                        <td data-title="Quantity">
                            <div class="quantity">
                                <button type="button" class="quantity-minus qty-btn"><i class="far fa-minus"></i></button>
                                <input type="number" class="qty-input" value="<?php echo $item['quantity']; ?>" min="1"
                                    max="99" data-price="<?php echo $item['product_price']; ?>">
                                <button type="button" class="quantity-plus qty-btn"><i class="far fa-plus"></i></button>
                            </div>
                        </td>
                        <td>$<?php echo number_format($item['total_price'] / $item['quantity'], 2); ?></td>
                        <td class="item-total">$<?php echo number_format($item['total_price'], 2); ?></td>
                        <td data-title="Remove">
                            <a href="#" class="remove"><i class="fal fa-trash-alt"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>




        <div class="row">

            <style>
            .recent-box {
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                align-items: center;
                border: 1px solid #333;
                background-color: #fff;
                transition: all 0.3s ease;
            }

            .recent-box:hover {
                border-color: #ff5722;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            }

            .recent-thumb {
                flex: 0 0 90px;
                margin-right: 15px;
            }

            .recent-thumb img {
                width: 90px;
                height: 90px;
                object-fit: cover;
                border-radius: 10px;
            }

            .product-title {
                font-size: 1.3rem;
                font-weight: bold;
                color: #333;
                text-decoration: none;
                transition: color 0.3s ease;
            }

            .product-title:hover {
                color: #ff5722;
            }

            .regular-price {
                font-size: 1.1rem;
                color: #333;
            }

            .add-to-cart {
                display: flex;
                justify-content: center;
                align-items: center;
                width: 100%;
            }

            .add-to-cart .theme-btn {
                font-size: 1rem;
                text-decoration: none;
                text-align: center;
                transition: background-color 0.3s ease;
            }
            </style>

            <!-- Add-Ons Section -->
            <div class="col-xl-3 col-lg-4 wow fadeInUp" data-wow-delay=".3s">
                <div class="main-sidebar">
                    <div class="single-sidebar-widget">
                        <h5 class="widget-title">Add-Ons</h5>

                        <?php while ($product = $resultProducts->fetch_assoc()): ?>
                        <div class="recent-box">
                            <div class="recent-thumb">
                                <?php
                        // Get the first image for the product
                        $images = explode(';', $product['image_path']);
                        $firstImage = $images[0];
                        ?>
                                <img src="<?php echo $firstImage; ?>" alt="menu-thumb">
                            </div>
                            <div class="recent-content">
                                <a href="shop-details.php?id=<?php echo $product['id']; ?>" class="product-title">
                                    <?php echo $product['product_title']; ?>
                                </a>
                                <div class="price">
                                    <div class="regular-price">
                                        $<?php echo number_format($product['product_price'], 2); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="add-to-cart">
                                <button type="button" class="theme-btn style6 add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                    Add <i class="fa-regular fa-basket-shopping"></i>
                                </button>
                            </div>
                        </div>
                        <?php endwhile; ?>

                    </div>
                </div>
            </div>

            <!-- Cart Totals Section -->
            <div class="col-md-8 col-lg-7 col-xl-6 offset-xl-3 mt-4 mt-lg-0">
                <h2 class="h4 summary-title">Cart Totals</h2>
                <table class="cart_totals">
                    <tbody>
                        <tr>
                            <td>Cart Subtotal</td>
                            <td data-title="Cart Subtotal">
                                <span class="amount"><bdi><span>$</span><span id="cart-subtotal">
                                            <?php echo number_format($subtotal, 2); ?></span></bdi></span>
                            </td>
                        </tr>
                        <tr>
                            <td>Tax (<?php echo ($taxRate * 100); ?>%)</td>
                            <td data-title="Tax">
                                <span class="amount"><bdi><span>$</span><span id="cart-tax">
                                            <?php echo number_format($tax, 2); ?></span></bdi></span>
                            </td>
                        </tr>
                        <tr class="shipping">
                            <th>Shipping Charge</th>
                            <td data-title="Shipping and Handling">
                                <div class="woocommerce-shipping-methods">
                                    <ul class="wc_shipping_methods shipping_methods methods">
                                        <li class="wc_shipping_method shipping_method_takeaway">
                                            <input id="shipping_method_takeaway" type="radio"
                                                class="input-radio shipping-option" name="shipping_method" value="0"
                                                checked>
                                            <label for="shipping_method_takeaway">Takeaway - $0</label>
                                        </li>
                                        <li id="delivery-option" class="wc_shipping_method shipping_method_delivery">
                                            <input id="shipping_method_delivery" type="radio"
                                                class="input-radio shipping-option" name="shipping_method" value="3.99">
                                            <label for="shipping_method_delivery">Delivery - $3.99</label>
                                        </li>
                                    </ul>
                                </div>
                                <?php if ($userData['address']) : ?>
                                <div class="user-address">
                                    <strong>Your Address:</strong>
                                    <div style="word-wrap: break-word; max-width: 100%; font-size: 1rem;">
                                        <?php echo htmlspecialchars($userData['address']); ?>
                                    </div>
                                    <div id="delivery-message"></div>
                                    <div id="user-delivery-message-container"></div>
                                </div>


                                <div id="selected-delivery-address" style="display: none; margin-top: 15px;">
                                    <strong>Delivery Address:</strong>
                                    <div id="delivery-address-text"
                                        style="word-wrap: break-word; max-width: 100%; font-size: 1rem;"></div>
                                </div>


                                <div id="delivery-message-container"></div>
                                <button type="button" class="btn btn-primary" id="differentAddressBtn">Different
                                    Address</button>
                                <div id="newAddressContainer" style="display: none; margin-top: 10px;">
                                    <div class="input-group mb-3">
                                        <input id="newAddress" type="text" class="form-control pr-5"
                                            placeholder="Enter new address" required>
                                        <input type="hidden" id="newLatitude">
                                        <input type="hidden" id="newLongitude">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary location-icon"
                                                onclick="locateMe()"
                                                style="font-size: 25px; border: 2px solid #ccc; transition: background 0.3s;">
                                                <i class="fa-solid fa-location-crosshairs"
                                                    style="transition: color 0.3s;"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="order-total">
                            <td>Order Total</td>
                            <td data-title="Total">
                                <strong><span class="amount"><bdi><span>$</span><span id="order-total">
                                                <?php echo number_format($total, 2); ?></span></bdi></span></strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <div class="wc-proceed-to-checkout mt-3">
                    <button type="button" class="theme-btn btn-fw checkout-btn">Proceed to checkout</button>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCbFtWxrgq5P_RQ6X_Rinpnk1OnRyrXKWY&libraries=places">
</script>

<script>
function initializeAutocomplete() {
    const input = document.getElementById('newAddress');
    const options = {};

    const autocomplete = new google.maps.places.Autocomplete(input, options);
    autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();
        if (place.geometry) {
            const newLat = place.geometry.location.lat();
            const newLng = place.geometry.location.lng();
            document.getElementById('newLatitude').value = newLat;
            document.getElementById('newLongitude').value = newLng;

            // Show and update delivery address
            const deliveryAddressDiv = document.getElementById('selected-delivery-address');
            const deliveryAddressText = document.getElementById('delivery-address-text');
            deliveryAddressDiv.style.display = 'block';
            deliveryAddressText.textContent = place.formatted_address;

            checkDeliveryAvailability(newLat, newLng, restaurantLat, restaurantLng);
        }
    });
}



function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

function checkDeliveryAvailability(customerLat, customerLng, restaurantLat, restaurantLng) {
    const distance = calculateDistance(customerLat, customerLng, restaurantLat, restaurantLng);
    const deliveryOption = document.getElementById('delivery-option');
    const deliveryMessage = document.getElementById('delivery-message');

    if (distance <= 7) {
        deliveryOption.style.display = 'block';
        deliveryMessage.innerHTML =
            `<span class="text-success">Delivery available (${distance.toFixed(2)} km away)</span>`;
    } else {
        deliveryOption.style.display = 'none';
        if (document.getElementById('shipping_method_delivery').checked) {
            document.getElementById('shipping_method_takeaway').checked = true;
        }
        deliveryMessage.innerHTML =
            `<span class="text-danger">Delivery not available (${distance.toFixed(2)} km away - maximum distance is 7 km)</span>`;
    }
}

function locateMe() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const geocoder = new google.maps.Geocoder();
                const latlng = {
                    lat: lat,
                    lng: lng
                };

                geocoder.geocode({
                    location: latlng
                }, (results, status) => {
                    if (status === "OK" && results[0]) {
                        const formattedAddress = results[0].formatted_address;
                        
                        // Update address input
                        document.getElementById('newAddress').value = formattedAddress;
                        document.getElementById('newLatitude').value = lat;
                        document.getElementById('newLongitude').value = lng;
                        
                        // Show and update delivery address immediately
                        const deliveryAddressDiv = document.getElementById('selected-delivery-address');
                        const deliveryAddressText = document.getElementById('delivery-address-text');
                        deliveryAddressDiv.style.display = 'block';
                        deliveryAddressText.textContent = formattedAddress;
                        
                        // Check delivery availability with restaurant coordinates
                        const restaurantLat = <?php echo $restaurantLat; ?>;
                        const restaurantLng = <?php echo $restaurantLng; ?>;
                        checkDeliveryAvailability(lat, lng, restaurantLat, restaurantLng);
                    }
                });
            },
            function(error) {
                alert("Error getting your location: " + error.message);
            }
        );
    } else {
        alert("Geolocation is not supported by this browser.");
    }
}

document.getElementById('differentAddressBtn').addEventListener('click', function() {
    const newAddressContainer = document.getElementById('newAddressContainer');
    const deliveryAddressDiv = document.getElementById('selected-delivery-address');

    if (newAddressContainer.style.display === 'none') {
        newAddressContainer.style.display = 'block';
        this.textContent = 'Cancel';
    } else {
        newAddressContainer.style.display = 'none';
        deliveryAddressDiv.style.display = 'none';
        this.textContent = 'Different Address';
        document.getElementById('newAddress').value = '';
        document.getElementById('newLatitude').value = '';
        document.getElementById('newLongitude').value = '';
        
        // Clear delivery message
        const deliveryMessage = document.getElementById('delivery-message');
        if (deliveryMessage) {
            deliveryMessage.innerHTML = '';
        }

        const customerLat = <?php echo $userData['latitude'] ?? 'null'; ?>;
        const customerLng = <?php echo $userData['longitude'] ?? 'null'; ?>;
        if (customerLat && customerLng) {
            checkDeliveryAvailability(customerLat, customerLng, <?php echo $restaurantLat; ?>,
                <?php echo $restaurantLng; ?>);
        }
    }
});

function updateCartItem(cartItemId, quantity, row) {
    fetch('update-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cart_item_id: cartItemId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.newTotalPrice) {
                    row.querySelector('.item-total').textContent = '$' + parseFloat(data.newTotalPrice).toFixed(
                        2);
                    updateCartTotals();
                }
            } else {
                alert('Error updating cart: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating cart');
        });
}

function removeCartItem(cartItemId, row) {
    fetch('remove-from-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cart_item_id: cartItemId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                row.remove();
                updateCartTotals();
            } else {
                alert('Error removing item: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error removing item');
        });
}

function updateCartTotals() {
    let subtotal = 0;
    document.querySelectorAll('.cart_item').forEach(row => {
        const totalPrice = parseFloat(row.querySelector('.item-total').textContent.replace('$', ''));
        subtotal += totalPrice;
    });
    
    const taxRate = <?php echo $taxRate; ?>;
    const tax = subtotal * taxRate;
    const selectedShippingOption = document.querySelector('.shipping-option:checked');
    const shippingCharge = parseFloat(selectedShippingOption.value);
    const total = subtotal + tax + shippingCharge;
    
    document.getElementById('cart-subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('cart-tax').textContent = tax.toFixed(2);
    document.getElementById('order-total').textContent = total.toFixed(2);
}

function addToCart(productId) {
    fetch('add-to-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Added to Cart!',
                text: 'The item has been added to your cart.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                customClass: {
                    popup: 'custom-toast',
                    title: 'swal2-title',
                    htmlContainer: 'swal2-text',
                    timerProgressBar: 'custom-progress-bar'
                }
            });
            // Refresh cart without page reload
            refreshCart();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to add item to cart',
                customClass: {
                    popup: 'custom-toast',
                    title: 'swal2-title',
                    htmlContainer: 'swal2-text'
                }
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to add item to cart',
            customClass: {
                popup: 'custom-toast',
                title: 'swal2-title',
                htmlContainer: 'swal2-text'
            }
        });
    });
}

function refreshCart() {
    fetch('fetch-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartDisplay(data.cartItems);
            updateCartTotals();
        }
    })
    .catch(error => {
        console.error('Error refreshing cart:', error);
    });
}

function updateCartDisplay(cartItems) {
    const cartTableBody = document.querySelector('.cart_table tbody');
    
    if (cartItems.length === 0) {
        cartTableBody.innerHTML = '<tr><td colspan="8" style="text-align: center;">Your cart is empty</td></tr>';
        return;
    }

    let cartHtml = '';
    let subtotal = 0;
    
    cartItems.forEach(item => {
        subtotal += parseFloat(item.total_price);
        
        cartHtml += `
            <tr class="cart_item" data-cart-item-id="${item.cart_item_id}">
                <td>${escapeHtml(item.product_title)}</td>
                <td>
                    ${item.toppings ? item.toppings.map((pizza, index) => 
                        `Pizza ${index + 1}: ${escapeHtml(pizza.join(', '))}<br>`
                    ).join('') : ''}
                    ${item.extra_toppings ? item.extra_toppings.map((pizza, index) => 
                        `Pizza ${index + 1} Extra Toppings: ${escapeHtml(pizza.join(', '))}<br>`
                    ).join('') : ''}
                </td>
                <td>
                    ${item.sauces ? item.sauces.map((wing, index) => 
                        `Wing ${index + 1}: ${escapeHtml(wing)}<br>`
                    ).join('') : ''}
                </td>
                <td>
                    ${item.pops ? item.pops.map((pop, index) => 
                        `Pop ${index + 1}: ${escapeHtml(pop)}<br>`
                    ).join('') : ''}
                </td>
                <td data-title="Quantity">
                    <div class="quantity">
                        <button type="button" class="quantity-minus qty-btn"><i class="far fa-minus"></i></button>
                        <input type="number" class="qty-input" value="${item.quantity}" min="1" max="99" data-price="${item.product_price}">
                        <button type="button" class="quantity-plus qty-btn"><i class="far fa-plus"></i></button>
                    </div>
                </td>
                <td>$${parseFloat(item.total_price / item.quantity).toFixed(2)}</td>
                <td class="item-total">$${parseFloat(item.total_price).toFixed(2)}</td>
                <td data-title="Remove">
                    <a href="#" class="remove"><i class="fal fa-trash-alt"></i></a>
                </td>
            </tr>
        `;
    });
    
    cartTableBody.innerHTML = cartHtml;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
document.querySelector('.wc-proceed-to-checkout .theme-btn').addEventListener('click', function(e) {
    e.preventDefault();

    const shippingMethod = document.querySelector('.shipping-option:checked').value === '3.99' ?
        'delivery' :
        'takeaway';
    const additionalAddress = document.getElementById('newAddress')?.value || null;

    const checkoutData = {
        shipping_method: shippingMethod,
        additional_address: additionalAddress
    };

    fetch('process-checkout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(checkoutData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'checkout.php?temp_id=' + data.temp_id;
            } else {
                alert('Error processing checkout: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error processing checkout');
        });
});


// Single DOMContentLoaded event listener
document.addEventListener('DOMContentLoaded', function() {
    // Initialize autocomplete
    initializeAutocomplete();

    // Check initial delivery availability
    const restaurantLat = <?php echo $restaurantLat; ?>;
    const restaurantLng = <?php echo $restaurantLng; ?>;
    const customerLat = <?php echo $userData['latitude'] ?? 'null'; ?>;
    const customerLng = <?php echo $userData['longitude'] ?? 'null'; ?>;

    if (customerLat && customerLng) {
        checkDeliveryAvailability(customerLat, customerLng, restaurantLat, restaurantLng);
    }



    const newAddressInput = document.getElementById('newAddress');
    if (newAddressInput) {
        const autocomplete = new google.maps.places.Autocomplete(newAddressInput);
        autocomplete.addListener('place_changed', function() {
            const place = autocomplete.getPlace();
            if (place.geometry) {
                const newLat = place.geometry.location.lat();
                const newLng = place.geometry.location.lng();
                document.getElementById('newLatitude').value = newLat;
                document.getElementById('newLongitude').value = newLng;
                checkDeliveryAvailability(newLat, newLng, restaurantLat, restaurantLng);
            }
        });
    }









    document.addEventListener('click', function(e) {
        if (e.target.closest('.qty-btn')) {
            e.preventDefault();

            const button = e.target.closest('.qty-btn');
            const row = button.closest('.cart_item');
            const input = row.querySelector('.qty-input');
            const cartItemId = row.dataset.cartItemId;
            let quantity = parseInt(input.value);

            if (button.classList.contains('quantity-minus')) {
                quantity = Math.max(1, quantity - 1);
            } else if (button.classList.contains('quantity-plus')) {
                quantity = Math.min(99, quantity + 1);
            }

            input.value = quantity;
            updateCartItem(cartItemId, quantity, row);
        }
    });



    // Quantity input change handlers
    document.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('change', function() {
            const row = this.closest('.cart_item');
            const cartItemId = row.dataset.cartItemId;
            let quantity = parseInt(this.value);

            if (isNaN(quantity) || quantity < 1) {
                quantity = 1;
            } else if (quantity > 99) {
                quantity = 99;
            }

            this.value = quantity;
            updateCartItem(cartItemId, quantity, row);
        });
    });



    // Remove item event delegation using SweetAlert2
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove')) {
            e.preventDefault();
            const button = e.target.closest('.remove');
            const row = button.closest('.cart_item');
            const cartItemId = row.dataset.cartItemId;

            Swal.fire({
                title: 'Remove Item?',
                text: "Are you sure you want to remove this item from your cart?",
                icon: 'warning',
                customClass: {
                    popup: 'custom-toast', // Custom styling
                    title: 'swal2-title',
                    htmlContainer: 'swal2-text',
                    confirmButton: 'swal2-confirm',
                    cancelButton: 'swal2-cancel',
                    actions: 'swal2-actions'
                },
                showCancelButton: true,
                confirmButtonText: 'Yes, remove it!',
                cancelButtonText: 'Cancel',
                buttonsStyling: false,
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    removeCartItem(cartItemId, row);
                    Swal.fire({
                        icon: 'success',
                        title: 'Removed!',
                        text: 'The item has been removed from your cart.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'custom-toast',
                            title: 'swal2-title',
                            htmlContainer: 'swal2-text',
                            timerProgressBar: 'custom-progress-bar'
                        }
                    });
                }
            });
        }
    });
    // Shipping option change handlers
    document.querySelectorAll('.shipping-option').forEach(option => {
        option.addEventListener('change', function() {
            updateCartTotals();
        });
    });

    // Add to cart button handlers
    document.addEventListener('click', function(e) {
        if (e.target.closest('.add-to-cart-btn')) {
            e.preventDefault();
            const button = e.target.closest('.add-to-cart-btn');
            const productId = button.dataset.productId;
            addToCart(productId);
        }
    });
});
</script>


<style>
/* Custom Toast Styling */
.custom-toast {
    background-color: #1a1a1a !important;
    /* Black background */
    color: #ffffff !important;
    /* White text */
    border-left: 5px solid #ff0000 !important;
    /* Red accent on the left */
    box-shadow: 0 4px 10px rgba(255, 0, 0, 0.3) !important;
    /* Red glow */
    padding: 15px !important;
    font-size: 14px !important;
}

/* Title Styling */
.custom-toast .swal2-title {
    color: #ff0000 !important;
    /* Red title */
    font-weight: bold !important;
}

/* Text Styling */
.custom-toast .swal2-text {
    color: #ffffff !important;
    /* White text */
}

/* Custom Progress Bar */
.custom-progress-bar {
    background: linear-gradient(90deg, #ff0000, #ffffff) !important;
    /* Red-to-white progress bar */
}

/* Button Styling */
.swal2-confirm {
    background-color: #ff0000 !important;
    color: white !important;
    border-radius: 5px !important;
    padding: 8px 15px !important;
    font-size: 14px !important;
}

.swal2-cancel {
    background-color: #444 !important;
    color: white !important;
    border-radius: 5px !important;
    padding: 8px 15px !important;
    font-size: 14px !important;
}

.swal2-actions {
    gap: 10px !important;
}
</style>

<?php
require_once 'footer.php';
require_once 'script.php';
?>



<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>