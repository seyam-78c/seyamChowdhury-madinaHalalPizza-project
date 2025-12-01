<?php
require_once 'header.php';
require_once 'connection.php';

// Get temp_id from URL
$temp_id = isset($_GET['temp_id']) ? $_GET['temp_id'] : '';

if (empty($temp_id)) {
    header("Location: cart.php");
    exit();
}
// Fetch order details
$order_query = "SELECT * FROM temp_orders WHERE temp_id = ?";
$stmt = $mysqli->prepare($order_query);
$stmt->bind_param("s", $temp_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order_data = $order_result->fetch_assoc();

// Fetch customer details
$customer_query = "SELECT * FROM customers WHERE customer_id = ?";
$stmt = $mysqli->prepare($customer_query);
$stmt->bind_param("i", $order_data['customer_id']); // Assuming customer_id is in temp_orders
$stmt->execute();
$customer_result = $stmt->get_result();
$customer_data = $customer_result->fetch_assoc();

// Fetch order items
$items_query = "SELECT * FROM temp_order_details WHERE temp_id = ?";
$stmt = $mysqli->prepare($items_query);
$stmt->bind_param("s", $temp_id);
$stmt->execute();
$items_result = $stmt->get_result();

// Fetch tax percentage
$tax_query = "SELECT tax_percentage FROM tax LIMIT 1";
$tax_result = $mysqli->query($tax_query);
$tax_data = $tax_result->fetch_assoc();
$tax_percentage = $tax_data['tax_percentage'];
?>

<div class="breadcumb-section">
    <div class="breadcumb-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="breadcumb-content">
                        <h1 class="breadcumb-title">Checkout</h1>
                        <ul class="breadcumb-menu">
                            <li><a href="index.html">Home</a></li>
                            <li class="text-white">/</li>
                            <li class="active">Checkout</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="th-checkout-wrapper section-padding fix">
    <div class="container">
        <?php if (!isset($_SESSION['customer_id'])): ?>
        <div class="woocommerce-form-login-toggle">
            <div class="woocommerce-info">Returning customer? <a href="signin.php" class="showlogin">Click here to
                    login</a>
            </div>
        </div>
        <?php endif; ?>
        <!-- Customer Details Section -->
        <div class="customer-details-section">
            <div class="container">
                <h3 class="section-title">Customer Details</h3>
                <div class="contact-info-wrapper">
                    <div class="contact-info">
                        <h5 class="contact-info-title">Name</h5>
                        <p class="contact-info-text"><?php echo htmlspecialchars($customer_data['customer_name']); ?>
                        </p>
                    </div>
                    <div class="contact-info">
                        <h5 class="contact-info-title">Email</h5>
                        <p class="contact-info-text"><?php echo htmlspecialchars($customer_data['email']); ?></p>
                    </div>
                    <div class="contact-info">
                        <h5 class="contact-info-title">Phone</h5>
                        <p class="contact-info-text"><?php echo htmlspecialchars($customer_data['phone']); ?></p>
                    </div>
                    <div class="contact-info">
                        <h5 class="contact-info-title">Delivery Address</h5>
                        <p class="contact-info-text">
                            <?php 
                                    // Check if additional_address exists in the temp_orders table
                                    if (!empty($order_data['additional_address'])) {
                                         echo htmlspecialchars($order_data['additional_address']);
                                         } else {
                                           echo htmlspecialchars($customer_data['address']);
                                             }
                                          ?>
                        </p>
                    </div>

                </div>
            </div>
        </div>

        <style>
        .customer-details-section .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .order-summary {
            margin-top: 20px;
            /* Adjust the margin as needed */
        }

        .section-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Contact Info Wrapper Styling */
        .contact-info-wrapper {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        /* Contact Info Styling */
        .contact-info {
            background-color: #fff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
        }

        .contact-info:hover {
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
        }

        .contact-info-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .contact-info-text {
            font-size: 16px;
            color: #666;
            margin: 0;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .contact-info-wrapper {
                grid-template-columns: 1fr;
            }
        }
        </style>


        <div class="order-summary">
            <h3 class="section-title">Order Details</h3>
            <form action="process_order.php" method="POST" class="woocommerce-cart-form">
                <input type="hidden" name="temp_id" value="<?php echo htmlspecialchars($temp_id); ?>">
                <table class="cart_table mb-20">
                    <thead>
                        <tr>
                            <th class="cart-col-image">Product Title</th>
                            <th class="cart-colname">Toppings</th>
                            <th class="cart-col-price">Sauces</th>
                            <th class="cart-col-quantity">Pops</th>
                            <th class="cart-col-total">Quantity</th>
                            <th class="cart-col-total">Price</th>
                            <th class="cart-col-total">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $items_result->fetch_assoc()) { ?>
                        <tr class="cart_item">
                            <td data-title="Product">
                                <?php echo htmlspecialchars($item['product_title']); ?>
                            </td>
                            <td data-title="Toppings" style="white-space: pre-line; font-weight: normal;">
                                <?php 
                                $toppings_display = htmlspecialchars($item['toppings']);
                                // Make headers bold
                                $toppings_display = preg_replace('/(CHOICE OF PIZZA \d+ TOPPINGS)/', '<strong>$1</strong>', $toppings_display);
                                echo nl2br($toppings_display); 
                                ?>
                            </td>
                            <td data-title="Sauces" style="white-space: pre-line;">
                                <?php 
                                echo nl2br(htmlspecialchars($item['sauces'])); 
                                ?>
                            </td>
                            <td data-title="Pops" style="white-space: pre-line;">
                                <?php 
                                echo nl2br(htmlspecialchars($item['pops'])); 
                                ?>
                            </td>
                            <td data-title="Quantity">
                                <strong
                                    class="product-quantity"><?php echo htmlspecialchars($item['quantity']); ?></strong>
                            </td>
                            <td data-title="Price">
                                <span
                                    class="amount"><bdi><span>$</span><?php echo htmlspecialchars($item['price']); ?></bdi></span>
                            </td>
                            <td data-title="Total">
                                <span
                                    class="amount"><bdi><span>$</span><?php echo htmlspecialchars($item['total_amount']); ?></bdi></span>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot class="checkout-ordertable">
                        <tr class="cart-subtotal">
                            <th>Subtotal</th>
                            <td data-title="Subtotal" colspan="6">
                                <span class="woocommerce-Price-amount amount">
                                    <bdi><span class="woocommerce-Price-currencySymbol">$</span>
                                        <?php echo htmlspecialchars($order_data['total_amount'] - $order_data['delivery_charge']); ?>
                                    </bdi>
                                </span>
                            </td>
                        </tr>
                        <tr class="cart-tax">
                            <th>Tax (<?php echo htmlspecialchars($tax_percentage); ?>%)</th>
                            <td data-title="Tax" colspan="6">
                                <span class="woocommerce-Price-amount amount">
                                    <bdi><span class="woocommerce-Price-currencySymbol">$</span>
                                        <?php 
                $tax_amount = ($order_data['total_amount'] - $order_data['delivery_charge']) * ($tax_percentage / 100);
                echo htmlspecialchars(number_format($tax_amount, 2)); 
                ?>
                                    </bdi>
                                </span>
                            </td>
                        </tr>
                        <tr class="woocommerce-shipping-totals shipping">
                            <th>Delivery Charge</th>
                            <td data-title="Delivery Charge" colspan="6">
                                <span class="woocommerce-Price-amount amount">
                                    <bdi><span class="woocommerce-Price-currencySymbol">$</span>
                                        <?php echo htmlspecialchars($order_data['delivery_charge']); ?>
                                    </bdi>
                                </span>
                            </td>
                        </tr>
                        <tr class="woocommerce-shipping-totals shipping">
                            <th>Tips</th>
                            <td data-title="Tips" colspan="6">
                                <input type="number" name="tips" class="form-control" placeholder="Tips for the driver"
                                    min="0" step="0.01" value="0">
                            </td>
                        </tr>
                        <tr class="order-total">
                            <th>Total</th>
                            <td data-title="Total" colspan="6">
                                <strong>
                                    <span class="woocommerce-Price-amount amount">
                                        <bdi><span class="woocommerce-Price-currencySymbol">$</span>
                                            <?php 
                    $subtotal = $order_data['total_amount'] - $order_data['delivery_charge'];
                    $tax_amount = $subtotal * ($tax_percentage / 100);
                    $total = $subtotal + $tax_amount + $order_data['delivery_charge'];
                    echo htmlspecialchars(number_format($total, 2)); 
                    ?>
                                        </bdi>
                                    </span>
                                </strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <div class="col-12 form-group">
                    <textarea name="order_notes" cols="20" rows="5" class="form-control"
                        placeholder="Notes about your order, e.g. special notes for delivery."></textarea>
                </div>
                <div class="mt-lg-3 mb-30">
                    <div class="woocommerce-checkout-payment">
                        <ul class="wc_payment_methods payment_methods methods">
                            <li class="wc_payment_method payment_method_bacs">
                                <input id="payment_method_bacs" type="radio" class="input-radio" name="payment_method"
                                    value="cash" checked="checked">
                                <label for="payment_method_bacs">Cash</label>
                            </li>
                            <li class="wc_payment_method payment_method_cheque">
                                <input id="payment_method_cheque" type="radio" class="input-radio" name="payment_method"
                                    value="card">
                                <label for="payment_method_cheque">Card</label>
                            </li>
                        </ul>

                        <!-- Add this HTML just before the place-order div in checkout.php -->
                        <div id="cardPaymentForm" style="display: none;" class="card-payment-form mb-4">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="card_holder">Cardholder Name:</label>
                                    <input type="text" id="card_holder" name="card_holder" class="form-control">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="card_number">Card Number:</label>
                                    <input type="text" id="card_number" name="card_number" class="form-control"
                                        maxlength="16" pattern="\d{16}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="expiry_month">Expiry Month:</label>
                                    <select id="expiry_month" name="expiry_month" class="form-control">
                                        <?php for($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?= sprintf('%02d', $i) ?>"><?= sprintf('%02d', $i) ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="expiry_year">Expiry Year:</label>
                                    <select id="expiry_year" name="expiry_year" class="form-control">
                                        <?php 
                $currentYear = date('Y');
                for($i = $currentYear; $i <= $currentYear + 10; $i++): 
                ?>
                                        <option value="<?= substr($i, -2) ?>"><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="cvv">CVV:</label>
                                    <input type="text" id="cvv" name="cvv" class="form-control" maxlength="4"
                                        pattern="\d{3,4}">
                                </div>
                                <input type="hidden" id="payment_amount" name="amount"
                                    value="<?php echo htmlspecialchars($order_data['total_amount']); ?>">
                                <div class="col-12">
                                    <button type="button" id="payButton" class="theme-btn">Pay
                                        $<?php echo htmlspecialchars($order_data['total_amount']); ?></button>
                                    <div id="paymentStatus" style="display: none;" class="mt-3"></div>
                                </div>
                            </div>
                        </div>

                        <div id="paymentSuccessMessage" style="display: none;" class="alert alert-success mb-4">
                            <strong>Your transaction is successful!</strong> Transaction ID: <span
                                id="transactionId"></span>
                        </div>

                        <div class="form-row place-order">
                            <button type="submit" class="theme-btn">Place order</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Update both total display and pay button amount when tips change
document.querySelector('input[name="tips"]').addEventListener('input', function() {
    let subtotal = <?php echo ($order_data['total_amount'] - $order_data['delivery_charge']); ?>;
    let tax_percentage = <?php echo $tax_percentage; ?>;
    let tax = subtotal * (tax_percentage / 100);
    let delivery = <?php echo $order_data['delivery_charge']; ?>;
    let tips = parseFloat(this.value) || 0;
    let total = subtotal + tax + delivery + tips;

    // Update the displayed total
    document.querySelector('.order-total .woocommerce-Price-amount bdi').innerHTML =
        '<span class="woocommerce-Price-currencySymbol">$</span>' + total.toFixed(2);

    // Update the pay button text and hidden amount input
    if (document.getElementById('payment_amount')) {
        document.getElementById('payment_amount').value = total.toFixed(2);
        document.getElementById('payButton').textContent = 'Pay $' + total.toFixed(2);
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const paymentMethodInputs = document.querySelectorAll('input[name="payment_method"]');
    const cardPaymentForm = document.getElementById('cardPaymentForm');
    const payButton = document.getElementById('payButton');
    const placeOrderButton = document.querySelector('button[type="submit"]');
    let paymentSuccess = false;

    // Add form validation for card payment
    function toggleCardPaymentForm() {
        const selectedPayment = document.querySelector('input[name="payment_method"]:checked').value;
        const cardFields = ['card_holder', 'card_number', 'cvv'];

        if (selectedPayment === 'card') {
            cardPaymentForm.style.display = 'block';
            payButton.style.display = 'block';
            placeOrderButton.style.display = 'none';

            // Add required attribute when card is selected
            cardFields.forEach(field => {
                document.getElementById(field).setAttribute('required', 'required');
            });
        } else {
            cardPaymentForm.style.display = 'none';
            payButton.style.display = 'none';
            placeOrderButton.style.display = 'block';

            // Remove required attribute when cash is selected
            cardFields.forEach(field => {
                document.getElementById(field).removeAttribute('required');
            });
        }
    }

    // Add event listeners to payment method radios
    paymentMethodInputs.forEach(input => {
        input.addEventListener('change', toggleCardPaymentForm);
    });

    // Handle pay button click
    if (payButton) {
        payButton.addEventListener('click', function(e) {
            e.preventDefault();

            let cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
            let cvv = document.getElementById('cvv').value;

            if (!validateCardNumber(cardNumber)) {
                alert('Invalid card number');
                return;
            }
            if (!/^\d{3,4}$/.test(cvv)) {
                alert('Invalid CVV');
                return;
            }

            // Get form data
            const formData = new FormData();
            formData.append('card_holder', document.getElementById('card_holder').value);
            formData.append('card_number', cardNumber);
            formData.append('expiry_month', document.getElementById('expiry_month').value);
            formData.append('expiry_year', document.getElementById('expiry_year').value);
            formData.append('cvv', cvv);
            formData.append('amount', document.getElementById('payment_amount').value);

            // Process payment
            payButton.disabled = true;
            payButton.textContent = 'Processing...';

            fetch('process_payment.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const statusDiv = document.getElementById('paymentStatus');
                    const successMessageDiv = document.getElementById('paymentSuccessMessage');
                    const transactionIdElement = document.getElementById('transactionId');
                    if (data.success) {
                        paymentSuccess = true;
                        successMessageDiv.style.display = 'block';
                        transactionIdElement.textContent = data.transaction_id;

                        statusDiv.innerHTML =
                            `Payment successful! Transaction ID: ${data.transaction_id}`;
                        statusDiv.className = 'alert alert-success';
                        placeOrderButton.style.display = 'block';
                        payButton.style.display = 'none';

                        // Add hidden input for transaction ID
                        const transactionInput = document.createElement('input');
                        transactionInput.type = 'hidden';
                        transactionInput.name = 'transaction_id';
                        transactionInput.value = data.transaction_id;
                        document.querySelector('form.woocommerce-cart-form').appendChild(
                            transactionInput);

                        // Hide card payment form after success
                        cardPaymentForm.style.display = 'none';

                        // SweetAlert Toast for Success
                        Swal.fire({
                            icon: 'success',
                            title: 'Payment Successful!',
                            text: `Transaction ID: ${data.transaction_id}`,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true,
                            customClass: {
                                popup: 'custom-toast',
                                title: 'swal2-title',
                                htmlContainer: 'swal2-text',
                                timerProgressBar: 'custom-progress-bar'
                            }
                        });


                    } else {
                        statusDiv.innerHTML = 'Payment failed: ' + data.message;
                        statusDiv.className = 'alert alert-danger';
                        payButton.disabled = false;
                        payButton.textContent = 'Try Payment Again';

                        // SweetAlert Toast for Failure
                        Swal.fire({
                            icon: 'error',
                            title: 'Payment Failed!',
                            text: data.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true,
                            customClass: {
                                popup: 'custom-toast',
                                title: 'swal2-title',
                                htmlContainer: 'swal2-text',
                                timerProgressBar: 'custom-progress-bar'
                            }
                        });
                    }
                    statusDiv.style.display = 'block';
                })
                .catch(error => {
                    document.getElementById('paymentStatus').innerHTML =
                        'Error processing payment. Please try again.';
                    document.getElementById('paymentStatus').className = 'alert alert-danger';
                    document.getElementById('paymentStatus').style.display = 'block';
                    payButton.disabled = false;
                    payButton.textContent = 'Try Payment Again';
                });
        });
    }

    // Prevent order submission if card payment wasn't successful
    document.querySelector('form.woocommerce-cart-form').addEventListener('submit', function(e) {
        const selectedPayment = document.querySelector('input[name="payment_method"]:checked')
            .value;
        if (selectedPayment === 'card' && !paymentSuccess) {
            e.preventDefault();
            alert('Please complete the payment process before placing the order.');
        }
    });

    // Card number validation function
    function validateCardNumber(number) {
        let sum = 0;
        let isEven = false;
        for (let i = number.length - 1; i >= 0; i--) {
            let digit = parseInt(number.charAt(i));
            if (isEven) {
                digit *= 2;
                if (digit > 9) {
                    digit -= 9;
                }
            }
            sum += digit;
            isEven = !isEven;
        }
        return (sum % 10) === 0;
    }
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
</style>


<?php
require_once 'footer.php';
require_once 'script.php';
?>
</body>

</html>




<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>