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

// Fetch tax rate
$taxQuery = "SELECT tax_percentage FROM tax WHERE id = 1";
$taxResult = $mysqli->query($taxQuery);
$taxRate = $taxResult->fetch_assoc()['tax_percentage'] / 100; // Convert to decimal

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
                        <h1 class="breadcumb-title">Custom Cart</h1>
                        <ul class="breadcumb-menu">
                            <li><a href="index.php">Home</a></li>
                            <li class="text-white">/</li>
                            <li class="active">Custom Cart</li>
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
                    <tr class="cart_item" data-cart-item-id="1">
                        <td>Medium Cheese Pizza</td>
                        <td>
                            Pizza 1 Extra Toppings: Deluxe, Vegetarian
                        </td>
                        <td></td>
                        <td></td>
                        <td data-title="Quantity">
                            <div class="quantity">
                                <button class="quantity-minus qty-btn"><i class="far fa-minus"></i></button>
                                <input type="number" class="qty-input" value="3" min="1" max="99" data-price="20.51">
                                <button class="quantity-plus qty-btn"><i class="far fa-plus"></i></button>
                            </div>
                        </td>
                        <td>$20.51</td>
                        <td class="item-total">$61.53</td>
                        <td data-title="Remove">
                            <a href="#" class="remove"><i class="fal fa-trash-alt"></i></a>
                        </td>
                    </tr>
                    <tr class="cart_item" data-cart-item-id="2">
                        <td>1 medium pizza</td>
                        <td>
                            Pizza 1: Beef Sausage, Beef Pepperoni, Ground Beef, Anchovies<br>
                            Pizza 1 Extra Toppings: Chicken, Vegetarian
                        </td>
                        <td></td>
                        <td></td>
                        <td data-title="Quantity">
                            <div class="quantity">
                                <button class="quantity-minus qty-btn"><i class="far fa-minus"></i></button>
                                <input type="number" class="qty-input" value="9" min="1" max="99" data-price="26.33">
                                <button class="quantity-plus qty-btn"><i class="far fa-plus"></i></button>
                            </div>
                        </td>
                        <td>$26.33</td>
                        <td class="item-total">$236.97</td>
                        <td data-title="Remove">
                            <a href="#" class="remove"><i class="fal fa-trash-alt"></i></a>
                        </td>
                    </tr>
                    <tr class="cart_item" data-cart-item-id="3">
                        <td>Large Potato Wedges</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td data-title="Quantity">
                            <div class="quantity">
                                <button class="quantity-minus qty-btn"><i class="far fa-minus"></i></button>
                                <input type="number" class="qty-input" value="7" min="1" max="99" data-price="8.99">
                                <button class="quantity-plus qty-btn"><i class="far fa-plus"></i></button>
                            </div>
                        </td>
                        <td>$8.99</td>
                        <td class="item-total">$62.93</td>
                        <td data-title="Remove">
                            <a href="#" class="remove"><i class="fal fa-trash-alt"></i></a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>




        <div class="row">
            <!-- Main Cart Content -->
            <div class="col-lg-8">
                <!-- Add-Ons Section -->
                <div class="add-ons-section mt-5">
                    <h3 class="section-title">Add More to Your Order</h3>
                    <div class="add-ons-grid">
                        <?php while ($product = $resultProducts->fetch_assoc()): ?>
                        <div class="add-on-item" data-product-id="<?php echo $product['id']; ?>">
                            <div class="add-on-image">
                                <?php
                                $images = explode(';', $product['image_path']);
                                $firstImage = $images[0];
                                ?>
                                <img src="<?php echo $firstImage; ?>" alt="<?php echo htmlspecialchars($product['product_title']); ?>">
                            </div>
                            <div class="add-on-content">
                                <h4 class="add-on-title"><?php echo htmlspecialchars($product['product_title']); ?></h4>
                                <p class="add-on-description">
                                    <?php echo htmlspecialchars(substr($product['product_description'], 0, 80)) . (strlen($product['product_description']) > 80 ? '...' : ''); ?>
                                </p>
                                <div class="add-on-footer">
                                    <span class="add-on-price">$<?php echo number_format($product['product_price'], 2); ?></span>
                                    <button class="add-to-cart-quick-btn" data-product-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-plus"></i> Add
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>

                    <!-- Pagination for Add-Ons -->
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination-wrapper">
                        <nav aria-label="Add-ons pagination">
                            <ul class="pagination">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cart Summary Sidebar -->
            <div class="col-lg-4">
                <div class="cart-summary">
                    <h3 class="summary-title">Order Summary</h3>

                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="cart-subtotal-display">$361.43</span>
                    </div>

                    <div class="summary-row">
                        <span>Tax (<?php echo ($taxRate * 100); ?>%)</span>
                        <span id="cart-tax-display">$<?php echo number_format(361.43 * $taxRate, 2); ?></span>
                    </div>

                    <div class="shipping-section">
                        <h4>Shipping Method</h4>
                        <div class="shipping-options">
                            <label class="shipping-option">
                                <input type="radio" name="shipping_method" value="0" checked>
                                <span class="option-content">
                                    <span class="option-title">Takeaway</span>
                                    <span class="option-price">Free</span>
                                </span>
                            </label>
                            <label class="shipping-option" id="delivery-option">
                                <input type="radio" name="shipping_method" value="3.99">
                                <span class="option-content">
                                    <span class="option-title">Delivery</span>
                                    <span class="option-price">$3.99</span>
                                </span>
                            </label>
                        </div>

                        <?php if ($userData['address']): ?>
                        <div class="address-section">
                            <h5>Delivery Address</h5>
                            <div class="current-address">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($userData['address']); ?></span>
                            </div>
                            <button type="button" class="change-address-btn" id="changeAddressBtn">
                                <i class="fas fa-edit"></i> Change Address
                            </button>

                            <div id="newAddressContainer" style="display: none;">
                                <div class="address-input-group">
                                    <input id="newAddress" type="text" placeholder="Enter new address" class="form-control">
                                    <input type="hidden" id="newLatitude">
                                    <input type="hidden" id="newLongitude">
                                    <button type="button" class="location-btn" onclick="locateMe()">
                                        <i class="fas fa-location-crosshairs"></i>
                                    </button>
                                </div>
                                <div class="address-actions" style="margin-top: 0.75rem;">
                                    <button type="button" class="change-address-btn cancel-btn" id="cancelAddressBtn">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </div>
                                <div id="selected-delivery-address" style="display: none;">
                                    <div class="selected-address">
                                        <i class="fas fa-check-circle"></i>
                                        <span id="delivery-address-text"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div id="delivery-message-container">
                            <div id="delivery-message" style="margin-top: 0.5rem; font-size: 0.9rem;"></div>
                        </div>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-row total-row">
                        <span><strong>Order Total</strong></span>
                        <span id="order-total-display"><strong>$<?php echo number_format(361.43 + (361.43 * $taxRate), 2); ?></strong></span>
                    </div>

                    <button type="button" class="checkout-btn" id="proceedCheckoutBtn">
                        <i class="fas fa-credit-card"></i> Proceed to Checkout
                    </button>

                    <div class="continue-shopping">
                        <a href="shop.php" class="continue-link">
                            <i class="fas fa-arrow-left"></i> Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCbFtWxrgq5P_RQ6X_Rinpnk1OnRyrXKWY&libraries=places">
</script>

<script>
// Function to update cart totals for custom cart
function updateCartTotals() {
    let subtotal = 0;
    document.querySelectorAll('.cart_item').forEach(row => {
        const totalPrice = parseFloat(row.querySelector('.item-total').textContent.replace('$', ''));
        subtotal += totalPrice;
    });

    const taxRate = <?php echo $taxRate; ?>;
    const tax = subtotal * taxRate;
    const selectedShippingOption = document.querySelector('input[name="shipping_method"]:checked');
    const shippingCharge = parseFloat(selectedShippingOption.value);
    const total = subtotal + tax + shippingCharge;

    // Update display elements
    document.getElementById('cart-subtotal-display').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('cart-tax-display').textContent = '$' + tax.toFixed(2);
    document.getElementById('order-total-display').innerHTML = '<strong>$' + total.toFixed(2) + '</strong>';
}

// Update quantity for custom cart
function updateQuantity(row, quantity) {
    const price = parseFloat(row.querySelector('.qty-input').dataset.price);
    const total = price * quantity;
    row.querySelector('.item-total').textContent = '$' + total.toFixed(2);
    updateCartTotals();
}

// Rest of the script remains similar, but adjusted for custom cart
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

            // Save the new address
            saveNewAddress(place.formatted_address, newLat, newLng);

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
    const deliveryRadio = document.querySelector('input[name="shipping_method"][value="3.99"]');
    const takeawayRadio = document.querySelector('input[name="shipping_method"][value="0"]');
    const deliveryMessage = document.getElementById('delivery-message');

    if (distance <= 7) {
        // Delivery available
        deliveryOption.style.display = 'block';
        deliveryRadio.disabled = false;
        deliveryRadio.parentElement.style.opacity = '1';
        deliveryMessage.innerHTML =
            `<span class="text-success">✓ Delivery available (${distance.toFixed(2)} km away)</span>`;
    } else {
        // Delivery not available
        deliveryOption.style.display = 'block'; // Keep showing but disabled
        deliveryRadio.disabled = true;
        deliveryRadio.checked = false; // Uncheck if it was checked
        deliveryRadio.parentElement.style.opacity = '0.5';
        takeawayRadio.checked = true; // Select takeaway
        updateCartTotals(); // Update totals when switching to takeaway
        deliveryMessage.innerHTML =
            `<span class="text-danger">✗ Delivery not available (${distance.toFixed(2)} km away - maximum distance is 7 km)</span>`;
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
                        document.getElementById('newAddress').value = results[0].formatted_address;
                        document.getElementById('newLatitude').value = lat;
                        document.getElementById('newLongitude').value = lng;
                        updateDeliveryAddress(results[0].formatted_address);
                        
                        // Save the new address
                        saveNewAddress(results[0].formatted_address, lat, lng);
                        
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

document.getElementById('changeAddressBtn').addEventListener('click', function() {
    const newAddressContainer = document.getElementById('newAddressContainer');
    const deliveryAddressDiv = document.getElementById('selected-delivery-address');
    const changeAddressBtn = document.getElementById('changeAddressBtn');

    if (newAddressContainer.style.display === 'none') {
        newAddressContainer.style.display = 'block';
        this.style.display = 'none'; // Hide the Change Address button
    }
});

// Handle Cancel button separately
document.getElementById('cancelAddressBtn').addEventListener('click', function() {
    const newAddressContainer = document.getElementById('newAddressContainer');
    const deliveryAddressDiv = document.getElementById('selected-delivery-address');
    const changeAddressBtn = document.getElementById('changeAddressBtn');

    newAddressContainer.style.display = 'none';
    deliveryAddressDiv.style.display = 'none';
    changeAddressBtn.style.display = 'inline-block'; // Show the Change Address button again
    document.getElementById('newAddress').value = '';
    document.getElementById('newLatitude').value = '';
    document.getElementById('newLongitude').value = '';

    const customerLat = <?php echo $userData['latitude'] ?? 'null'; ?>;
    const customerLng = <?php echo $userData['longitude'] ?? 'null'; ?>;
    if (customerLat && customerLng) {
        checkDeliveryAvailability(customerLat, customerLng, <?php echo $restaurantLat; ?>,
            <?php echo $restaurantLng; ?>);
    }
});

// Update delivery address display
function updateDeliveryAddress(address) {
    const deliveryAddressDiv = document.getElementById('selected-delivery-address');
    const deliveryAddressText = document.getElementById('delivery-address-text');
    deliveryAddressDiv.style.display = 'block';
    deliveryAddressText.textContent = address;
}

// Save new address to database
function saveNewAddress(address, latitude, longitude) {
    fetch('update-address.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            address: address,
            latitude: latitude,
            longitude: longitude
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the current address display
            const currentAddressSpan = document.querySelector('.current-address span');
            if (currentAddressSpan) {
                currentAddressSpan.textContent = address;
            }
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Address Updated!',
                text: 'Your delivery address has been updated successfully.',
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
        } else {
            alert('Error updating address: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating address');
    });
}

// Add product to cart (AJAX)
function addToCart(productId, button) {
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';

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
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Added to Cart!',
                text: 'Product has been added to your cart.',
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
            }).then(() => {
                // Reload the page to update the cart table
                window.location.reload();
            });

            // Reset button
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-plus"></i> Add';
        } else {
            alert('Error adding to cart: ' + data.message);
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-plus"></i> Add';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding to cart');
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-plus"></i> Add';
    });
}

// Proceed to checkout
document.getElementById('proceedCheckoutBtn').addEventListener('click', function(e) {
    e.preventDefault();

    const shippingMethod = document.querySelector('input[name="shipping_method"]:checked').value === '3.99' ?
        'delivery' : 'takeaway';
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

// DOM Content Loaded
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

    // Initialize autocomplete for new address input
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
                
                // Save the new address
                saveNewAddress(place.formatted_address, newLat, newLng);
                
                checkDeliveryAvailability(newLat, newLng, restaurantLat, restaurantLng);
            }
        });
    }

    // Quantity button event delegation for custom cart
    document.addEventListener('click', function(e) {
        const button = e.target.closest('.qty-btn');
        if (button && !button.disabled) {
            e.preventDefault();

            // Disable button temporarily to prevent double clicks
            button.disabled = true;
            button.style.opacity = '0.5';

            const row = button.closest('.cart_item');
            const input = row.querySelector('.qty-input');
            let quantity = parseInt(input.value);

            if (button.classList.contains('quantity-minus')) {
                quantity = Math.max(1, quantity - 1);
            } else if (button.classList.contains('quantity-plus')) {
                quantity = Math.min(99, quantity + 1);
            }

            input.value = quantity;
            updateQuantity(row, quantity);

            // Re-enable button
            setTimeout(() => {
                button.disabled = false;
                button.style.opacity = '1';
            }, 200);
        }
    });

    // Quantity input change handlers for manual input
    document.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('change', function() {
            const row = this.closest('.cart_item');
            let quantity = parseInt(this.value);

            if (isNaN(quantity) || quantity < 1) {
                quantity = 1;
                this.value = 1;
            } else if (quantity > 99) {
                quantity = 99;
                this.value = 99;
            }

            updateQuantity(row, quantity);
        });
    });

    // Remove item event delegation for custom cart
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove')) {
            e.preventDefault();
            const button = e.target.closest('.remove');
            const row = button.closest('.cart_item');

            Swal.fire({
                title: 'Remove Item?',
                text: "Are you sure you want to remove this item from your cart?",
                icon: 'warning',
                customClass: {
                    popup: 'custom-toast',
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
                    row.remove();
                    updateCartTotals();
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

    // Add to cart button event delegation
    document.addEventListener('click', function(e) {
        if (e.target.closest('.add-to-cart-quick-btn')) {
            e.preventDefault();
            const button = e.target.closest('.add-to-cart-quick-btn');
            const productId = button.dataset.productId;
            addToCart(productId, button);
        }
    });

    // Shipping option change handlers
    document.querySelectorAll('input[name="shipping_method"]').forEach(option => {
        option.addEventListener('change', function() {
            updateCartTotals();
        });
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

/* New Cart Layout Styles */
.page-header {
    text-align: center;
    margin-bottom: 2rem;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 0.5rem;
}

.page-subtitle {
    font-size: 1.1rem;
    color: #666;
    margin: 0;
}

.section-title {
    font-size: 1.8rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #EB0029;
}

/* Cart Table Styles */
.cart-table-responsive {
    overflow-x: auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
}

.cart-table {
    width: 100%;
    border-collapse: collapse;
}

.cart-table th {
    background: #f8f9fa;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #e9ecef;
}

.cart-table td {
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    vertical-align: middle;
}

.cart-item-row:hover {
    background: #f8f9fa;
}

.product-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.25rem;
}

.customizations {
    font-size: 0.9rem;
    color: #666;
    line-height: 1.4;
}

/* Quantity Controls */
.quantity-wrapper {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.qty-btn {
    width: 30px;
    height: 30px;
    border: 1px solid #ddd;
    background: #fff;
    color: #666;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.qty-btn:hover {
    background: #EB0029;
    color: white;
    border-color: #EB0029;
}

.qty-input {
    width: 60px;
    height: 30px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.9rem;
}

.price, .total {
    font-weight: 600;
    color: #333;
}

/* Remove Button */
.remove-btn {
    width: 35px;
    height: 35px;
    border: none;
    background: #dc3545;
    color: white;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.remove-btn:hover {
    background: #c82333;
    transform: scale(1.1);
}

/* Empty Cart */
.empty-cart {
    text-align: center;
    padding: 3rem 2rem;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
}

.empty-cart-icon {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 1rem;
}

.empty-cart h4 {
    color: #333;
    margin-bottom: 0.5rem;
}

.empty-cart p {
    color: #666;
    margin-bottom: 1.5rem;
}

/* Add-Ons Grid */
.add-ons-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.add-on-item {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.add-on-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(235, 0, 41, 0.15);
    border-color: #EB0029;
}

.add-on-image {
    position: relative;
    height: 160px;
    overflow: hidden;
}

.add-on-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.add-on-item:hover .add-on-image img {
    transform: scale(1.05);
}

.add-on-content {
    padding: 1.25rem;
}

.add-on-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
    line-height: 1.3;
}

.add-on-description {
    color: #666;
    font-size: 0.9rem;
    line-height: 1.4;
    margin-bottom: 1rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.add-on-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.add-on-price {
    font-size: 1.2rem;
    font-weight: 700;
    color: #EB0029;
}

.add-to-cart-quick-btn {
    background: linear-gradient(135deg, #EB0029 0%, #b80020 100%);
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.add-to-cart-quick-btn:hover {
    background: linear-gradient(135deg, #b80020 0%, #990019 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(235, 0, 41, 0.3);
}

/* Pagination */
.pagination-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
}

.pagination {
    display: flex;
    gap: 0.25rem;
}

.page-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border: 1px solid #e9ecef;
    background: #fff;
    color: #EB0029;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.page-link:hover {
    background: #EB0029;
    color: white;
    border-color: #EB0029;
}

.page-item.active .page-link {
    background: #EB0029;
    color: white;
    border-color: #EB0029;
}

/* Cart Summary */
.cart-summary {
    background: #fff;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    position: sticky;
    top: 2rem;
}

.summary-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #EB0029;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
    font-size: 0.95rem;
}

.summary-row span:first-child {
    color: #666;
}

.summary-row span:last-child {
    font-weight: 600;
    color: #333;
}

.total-row {
    font-size: 1.1rem;
    margin: 1rem 0;
    padding: 1rem 0;
    border-top: 1px solid #e9ecef;
    border-bottom: 1px solid #e9ecef;
}

.total-row strong {
    color: #333;
}

/* Shipping Section */
.shipping-section h4 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 1rem;
}

.shipping-options {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.shipping-option {
    display: flex;
    align-items: center;
    cursor: pointer;
    padding: 0.75rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.shipping-option:hover {
    border-color: #EB0029;
}

.shipping-option input[type="radio"] {
    margin-right: 0.75rem;
}

.shipping-option input[type="radio"]:checked + .option-content {
    color: #EB0029;
}

.shipping-option:has(input[type="radio"]:checked) {
    border-color: #EB0029;
    background-color: #fff5f5;
}

.option-content {
    display: flex;
    justify-content: space-between;
    width: 100%;
}

.option-title {
    font-weight: 600;
    color: #333;
}

.option-price {
    color: #EB0029;
    font-weight: 600;
}

/* Address Section */
.address-section h5 {
    font-size: 1rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.75rem;
}

.current-address {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 0.75rem;
}

.current-address i {
    color: #EB0029;
    margin-top: 0.1rem;
}

.current-address span {
    font-size: 0.9rem;
    color: #666;
    line-height: 1.4;
}

.change-address-btn {
    background: #6c757d;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.change-address-btn:hover {
    background: #5a6268;
}

.address-input-group {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.address-input-group .form-control {
    flex: 1;
    border-radius: 6px;
    border: 1px solid #ddd;
}

.location-btn {
    background: #28a745;
    color: white;
    border: none;
    padding: 0.5rem;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.location-btn:hover {
    background: #218838;
}

.selected-address {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: #d4edda;
    border: 1px solid #c3e6cb;
    border-radius: 6px;
    color: #155724;
}

.selected-address i {
    color: #28a745;
}

/* Checkout Button */
.checkout-btn {
    width: 100%;
    background: linear-gradient(135deg, #EB0029 0%, #b80020 100%);
    color: white;
    border: none;
    padding: 1rem;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 1.5rem 0;
}

.checkout-btn:hover {
    background: linear-gradient(135deg, #b80020 0%, #990019 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(235, 0, 41, 0.3);
}

.address-actions {
    display: flex;
    justify-content: flex-end;
}

.cancel-btn {
    background: #dc3545 !important;
}

.cancel-btn:hover {
    background: #c82333 !important;
}

/* Responsive Design */
@media (max-width: 768px) {
    .add-ons-grid {
        grid-template-columns: 1fr;
    }

    .cart-table-responsive {
        font-size: 0.9rem;
    }

    .cart-table th,
    .cart-table td {
        padding: 0.5rem;
    }

    .page-title {
        font-size: 2rem;
    }

    .section-title {
        font-size: 1.5rem;
    }

    .cart-summary {
        position: static;
        margin-top: 2rem;
    }
}

@media (max-width: 576px) {
    .quantity-wrapper {
        flex-direction: column;
        gap: 0.25rem;
    }

    .qty-input {
        width: 50px;
    }

    .add-on-footer {
        flex-direction: column;
        gap: 0.75rem;
        align-items: flex-start;
    }

    .shipping-option {
        padding: 0.5rem;
    }

    .option-content {
        flex-direction: column;
        gap: 0.25rem;
        align-items: flex-start;
    }
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