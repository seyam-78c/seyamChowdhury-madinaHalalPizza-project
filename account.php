<?php
session_start(); // Start the session

require_once 'connection.php';

// Check if the user is signed in
if (!isset($_SESSION['customer_id'])) {
    header("Location: signin.php");
    exit();
}
// Check if the user is already signed in
if (isset($_SESSION['customer_id'])) {
    // Fetch customer details from the database
    $stmt = $mysqli->prepare("SELECT customer_name, email, phone, address, latitude, longitude FROM customers WHERE customer_id = ?");
    $stmt->bind_param("i", $_SESSION['customer_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $customer = $result->fetch_assoc();
    } else {
        // If no user is found, redirect to login and clear session
        session_unset();
        session_destroy();
        header("Location: signin.php");
        exit();
    }
} else {
    header("Location: signin.php");
    exit();
}

// Handle the form submission for updating customer details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_phone = htmlspecialchars(trim($_POST['phone']));
    $new_address = htmlspecialchars(trim($_POST['address']));
    $new_latitude = htmlspecialchars(trim($_POST['latitude']));
    $new_longitude = htmlspecialchars(trim($_POST['longitude']));

    if (!empty($new_phone) && !empty($new_address) && !empty($new_latitude) && !empty($new_longitude)) {
        $update_stmt = $mysqli->prepare("UPDATE customers SET phone = ?, address = ?, latitude = ?, longitude = ? WHERE customer_id = ?");
        $update_stmt->bind_param("ssddi", $new_phone, $new_address, $new_latitude, $new_longitude, $_SESSION['customer_id']);
        if ($update_stmt->execute()) {
            // Update the local $customer array to reflect changes
            $customer['phone'] = $new_phone;
            $customer['address'] = $new_address;
            $customer['latitude'] = $new_latitude;
            $customer['longitude'] = $new_longitude;
            $success_message = "Details updated successfully.";
        } else {
            $error_message = "Failed to update details. Please try again.";
        }
        $update_stmt->close();
    } else {
        $error_message = "Phone, address, latitude, and longitude fields cannot be empty.";
    }
}

$customer_id = $_SESSION['customer_id'];

// Fetch orders for the logged-in customer
$order_query = $mysqli->prepare(
    "SELECT order_id, order_number, total_amount, order_date, delivery_type, payment_method, order_status 
     FROM orders 
     WHERE customer_id = ?"
);
$order_query->bind_param("i", $customer_id);
$order_query->execute();
$order_result = $order_query->get_result();

require_once 'sheader.php';
?>


<div class="account-section section-padding fix">
    <div class="container">
        <div class="account-wrapper shadow rounded p-4">
            <!-- Flex container for header and button -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="text-danger mb-0">Welcome, <?= htmlspecialchars($customer['customer_name']) ?>!</h3>
                <a href="logout.php" class="theme-btn style6">Log Out</a>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="text-success mb-0">Account Details:</h3>
            </div>
            <!-- Display success or error messages -->
            <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
            <?php elseif (isset($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <!-- Non-editable fields -->
                <fieldset disabled>
                    <div class="form-group mb-3">
                        <label><strong>Name:</strong></label>
                        <input type="text" class="form-control text-secondary"
                            value="<?= htmlspecialchars($customer['customer_name']) ?>">
                    </div>
                    <div class="form-group mb-3">
                        <label><strong>Email:</strong></label>
                        <input type="email" class="form-control text-secondary"
                            value="<?= htmlspecialchars($customer['email']) ?>">
                    </div>
                </fieldset>

                <!-- Editable fields -->
                <div class="form-group mb-3">
                    <label for="phone"><strong>Phone: </strong></label>
                    <input type="text" id="phone" name="phone" class="form-control"
                        value="<?= htmlspecialchars($customer['phone']) ?>" required>
                </div>
                <div class="form-group mb-3">
                    <label for="autocomplete"><strong>Address: </strong></label>
                    <div class="col-12 position-relative">
                        <input id="autocomplete" type="text" name="address" placeholder="Address" required
                            class="form-control pr-5" style="border-right: 1px solid #ccc;"
                            value="<?= htmlspecialchars($customer['address']) ?>">
                        <input type="hidden" name="latitude" id="latitude"
                            value="<?= htmlspecialchars($customer['latitude'] ?? '') ?>">
                        <input type="hidden" name="longitude" id="longitude"
                            value="<?= htmlspecialchars($customer['longitude'] ?? '') ?>">
                        <button type="button" class="btn btn-outline-secondary location-icon" onclick="locateMe()"
                            style="position: absolute; right: 13px; top: 50%; transform: translateY(-50%); background: transparent; font-size: 25px; border: none; border-left: 1px solid #ccc; transition: background 0.3s;">
                            <i class="fa-solid fa-location-crosshairs" style="transition: color 0.3s;"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="theme-btn style3">Save Changes</button>
            </form>
        </div>
    </div>




    <div class="section-padding">
        <div class="container">
            <div class="tinv-wishlist woocommerce tinv-wishlist-clear">
                <div class="tinv-header">
                    <h2 class="mb-30">Order List</h2>
                </div>
                <!-- Add shadow and rounded corners to the order table section -->
                <div class="shadow rounded p-4">
                    <table class="tinvwl-table-manage-list" style="width: 100%; border-collapse: collapse;">
                        <thead style="background-color: #F1F1F1; text-align: left;">
                            <tr>
                                <th class="product-stock" style="padding: 12px; border: 1px solid #ddd;">Date</th>
                                <th class="product-name" style="padding: 12px; border: 1px solid #ddd;">Order Number
                                </th>
                                <th class="product-price" style="padding: 12px; border: 1px solid #ddd;">Total Amount
                                </th>
                                <th class="product-date" style="padding: 12px; border: 1px solid #ddd;">Order Status
                                </th>
                                <th class="product-stock" style="padding: 12px; border: 1px solid #ddd;">Delivery Type
                                </th>
                                <th class="product-stock" style="padding: 12px; border: 1px solid #ddd;">Payment Method
                                </th>
                                <th class="product-action"
                                    style="padding: 12px; border: 1px solid #ddd; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($order_result->num_rows > 0): ?>
                            <?php while ($order = $order_result->fetch_assoc()): ?>
                            <tr class="wishlist_item" style="background-color: #F4F1EA;">
                                <td style="padding: 12px; border: 1px solid #ddd;">
                                    <?= htmlspecialchars($order['order_date']) ?></td>
                                <td style="padding: 12px; border: 1px solid #ddd;">
                                    <?= htmlspecialchars($order['order_number']) ?></td>
                                <td style="padding: 12px; border: 1px solid #ddd;">
                                    $<?= htmlspecialchars(number_format($order['total_amount'], 2)) ?></td>
                                <td style="padding: 12px; border: 1px solid #ddd;">
                                    <?= htmlspecialchars($order['order_status']) ?></td>
                                <td style="padding: 12px; border: 1px solid #ddd;">
                                    <?= htmlspecialchars($order['delivery_type']) ?></td>
                                <td style="padding: 12px; border: 1px solid #ddd;">
                                    <?= htmlspecialchars($order['payment_method']) ?></td>
                                <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">
                                    <a href="madina-invoice.php?order_id=<?= htmlspecialchars($order['order_id']) ?>"
                                        class="theme-btn style4" target="_blank">Invoice</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center" style="padding: 12px; border: 1px solid #ddd;">No
                                    orders found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div> <!-- End of the shadow and rounded div -->
            </div>
        </div>
    </div>



</div>

<?php
require_once 'footer.php';
require_once 'script.php';
?>

<style>
/* Add hover effect for buttons and icons */
.location-icon:hover {
    background-color: #f0f0f0;
}

.location-icon:hover i {
    color: #007bff;
}
</style>

<script>
let autocomplete;

function initAutocomplete() {
    autocomplete = new google.maps.places.Autocomplete(
        document.getElementById('autocomplete'), {
            types: ['geocode']
        }
    );

    autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        if (place.geometry) {
            document.getElementById('latitude').value = place.geometry.location.lat();
            document.getElementById('longitude').value = place.geometry.location.lng();
        }
    });
}

// Prevent form submission when "Enter" is pressed in the address input
document.getElementById('autocomplete').addEventListener('keydown', function(event) {
    if (event.key === "Enter") {
        event.preventDefault(); // Prevent the default form submission behavior
    }
});

function locateMe() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const latitude = position.coords.latitude;
                const longitude = position.coords.longitude;

                document.getElementById('latitude').value = latitude;
                document.getElementById('longitude').value = longitude;

                const geocoder = new google.maps.Geocoder();
                const latLng = {
                    lat: latitude,
                    lng: longitude
                };

                geocoder.geocode({
                    location: latLng
                }, (results, status) => {
                    if (status === "OK" && results[0]) {
                        document.getElementById('autocomplete').value = results[0].formatted_address;
                    } else {
                        alert("Unable to determine address. Please enter it manually.");
                    }
                });
            },
            () => {
                alert("Geolocation failed. Please allow location access.");
            }
        );
    } else {
        alert("Geolocation is not supported by this browser.");
    }
}
</script>
<script async
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCbFtWxrgq5P_RQ6X_Rinpnk1OnRyrXKWY&libraries=places&callback=initAutocomplete">
</script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>

</html>