<?php
session_start();

// Check if the user is logged in for pages that require authentication
$currentFile = basename($_SERVER['PHP_SELF']);

// Pages that don't require authentication
$publicPages = ['index.php', 'shop.php', 'contact.php', 'shop-details.php', 'signup.php'];

if (!isset($_SESSION['customer_id']) && !in_array($currentFile, $publicPages)) {
    header('Location: signin.php');
    exit;
}


require_once 'connection.php'; // Ensure the database connection is included
$cartItems = [];
$subtotal = 0;

// Check if the customer is logged in
if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];

    // Query to fetch cart items and product details
    $stmt = $mysqli->prepare("
        SELECT 
            ci.id as cart_item_id,
            ci.quantity,
            ci.total_price,
            p.id as product_id,
            p.product_title,
            p.product_price,
            p.image_path
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.customer_id = ?
    ");

    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch cart items and calculate subtotal
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
        $subtotal += $row['total_price'];
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Madina Halal Pizza & Wings">
    <meta name="description" content="Madina Halal Pizza & Wings">
    <title>Madina Halal Pizza & Wings</title>
    <link rel="icon" href="https://res.cloudinary.com/dshdzqvuc/image/upload/v1734378840/icon_o7hcvf.png"
        type="image/png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/magnific-popup.css">
    <link rel="stylesheet" href="assets/css/meanmenu.css">
    <link rel="stylesheet" href="assets/css/swiper-bundle.min.css">
    <link rel="stylesheet" href="assets/css/nice-select.css">
    <link rel="stylesheet" href="assets/css/main.css">
</head>

<body class="bg-color2">
    <div id="preloader" class="preloader">
        <div class="animation-preloader">
            <div class="spinner">
            </div>
            <div class="txt-loading">
                <span data-text-preloader="M" class="letters-loading">
                    M
                </span>
                <span data-text-preloader="A" class="letters-loading">
                    A
                </span>
                <span data-text-preloader="D" class="letters-loading">
                    D
                </span>
                <span data-text-preloader="I" class="letters-loading">
                    I
                </span>
                <span data-text-preloader="N" class="letters-loading">
                    N
                </span>
                <span data-text-preloader="A" class="letters-loading">
                    A
                </span>
            </div>
            <p class="text-center">Halal Pizza & Wings</p>
        </div>
        <div class="loader">
            <div class="row">
                <div class="col-3 loader-section section-left">
                    <div class="bg"></div>
                </div>
                <div class="col-3 loader-section section-left">
                    <div class="bg"></div>
                </div>
                <div class="col-3 loader-section section-right">
                    <div class="bg"></div>
                </div>
                <div class="col-3 loader-section section-right">
                    <div class="bg"></div>
                </div>
            </div>
        </div>
    </div>

    <button id="back-top" class="back-to-top">
        <i class="fa-regular fa-arrow-up"></i>
    </button>

    <div class="fix-area">
        <div class="offcanvas__info">
            <div class="offcanvas__wrapper">
                <div class="offcanvas__content">
                    <div class="offcanvas__top mb-5 d-flex justify-content-between align-items-center">
                        <div class="offcanvas__logo">
                            <a href="index.php">
                                <img src="https://res.cloudinary.com/dshdzqvuc/image/upload/v1734378641/Madinah_Pizza_Logo_Animation_lg0fpf.gif"
                                    alt="logo-img">
                            </a>
                        </div>
                        <div class="offcanvas__close">
                            <button>
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <p class="text d-none d-lg-block">
                        Welcome To Madina Halal Pizza & Wings
                    </p>
                    <div class="offcanvas-gallery-area d-none d-xl-block">
                        <div class="offcanvas-gallery-items">
                            <a href="shop-details.php?id=72" class="offcanvas-image">
                                <img src="https://res.cloudinary.com/dshdzqvuc/image/upload/v1736015655/xhvfgvljkr3arb4kbjlg.jpg"
                                    alt="gallery-img" width="120" height="120">

                            </a>
                            <a href="shop-details.php?id=76" class="offcanvas-image">
                                <img src="https://res.cloudinary.com/dshdzqvuc/image/upload/v1736016000/jd5tmpj5ps2lxbm8bngq.webp"
                                    alt="gallery-img" width="120" height="120">

                            </a>
                            <a href="shop-details.php?id=83" class="offcanvas-image">
                                <img src="https://res.cloudinary.com/dshdzqvuc/image/upload/v1736017562/emjkvaprmsogfivm6023.jpg"
                                    alt="gallery-img" width="120" height="120">

                            </a>
                        </div>
                        <div class="offcanvas-gallery-items">
                            <a href="shop-details.php?id=85" class="offcanvas-image">
                                <img src="https://res.cloudinary.com/dshdzqvuc/image/upload/v1736018042/kbcbjtaquowxqedvg5q8.avif"
                                    alt="gallery-img" width="120" height="120">

                            </a>
                            <a href="shop-details.php?id=97" class="offcanvas-image">
                                <img src="https://res.cloudinary.com/dshdzqvuc/image/upload/v1736019233/urqwjlq8gem8r9pss84f.jpg"
                                    alt="gallery-img" width="120" height="120">

                            </a>
                            <a href="shop-details.php?id=93" class="offcanvas-image">
                                <img src="https://res.cloudinary.com/dshdzqvuc/image/upload/v1736018747/dwlpsyumzwrdiye9jkua.jpg"
                                    alt="gallery-img" width="120" height="120">

                            </a>
                        </div>
                    </div>
                    <div class="mobile-menu fix mb-3"></div>
                    <div class="offcanvas__contact">
                        <h4>Contact Info</h4>
                        <ul>
                            <li class="d-flex align-items-center">
                                <div class="offcanvas__contact-icon">
                                    <i class="fal fa-map-marker-alt"></i>
                                </div>
                                <div class="offcanvas__contact-text">
                                    <a target="_blank" href="#">1078 Danforth Ave,Toronto, Canada </a>
                                </div>
                            </li>
                            <li class="d-flex align-items-center">
                                <div class="offcanvas__contact-icon mr-15">
                                    <i class="fal fa-clock"></i>
                                </div>
                                <div class="offcanvas__contact-text">
                                    <a target="_blank" href="#">Everyday, 11am to Midnight</a>
                                </div>
                            </li>
                            <li class="d-flex align-items-center">
                                <div class="offcanvas__contact-icon mr-15">
                                    <i class="far fa-phone"></i>
                                </div>
                                <div class="offcanvas__contact-text">
                                    <a href="tel:416-462-9000">416-462-9000</a>
                                </div>
                            </li>
                        </ul>
                        <div class="header-button mt-4">
                            <a href="shop.php" class="theme-btn">
                                <span class="button-content-wrapper d-flex align-items-center justify-content-center">
                                    <span class="button-icon"><i
                                            class="fa-sharp fa-regular fa-cart-shopping bg-transparent text-white me-2"></i></span>
                                    <span class="button-text">ORDER NOW</span>
                                </span>
                            </a>
                        </div>
                        <div class="social-icon d-flex align-items-center">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-youtube"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="offcanvas__overlay"></div>

    <header class="header-section">
        <div class="black-bg"></div>
        <div class="red-bg"></div>
        <div class="container-fluid">
            <div class="main-header-wrapper">
                <div class="logo-image">
                    <a href="index.php">
                        <img src="https://res.cloudinary.com/dshdzqvuc/image/upload/v1736831090/Madina/lv2sdr37rcbese8atx3x.png"
                            alt="img" style="width: 167px; height: 58px;">
                    </a>
                </div>

                <div class="main-header-items">
                    <div class="header-top-wrapper">
                        <span><i class="fa-regular fa-clock"></i> 11:00 am to Midnight</span>
                        <div class="social-icon d-flex align-items-center">
                            <span>Follow Us:</span>
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-youtube"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    <div id="header-sticky" class="header-1">
                        <div class="mega-menu-wrapper">
                            <div class="header-main">
                                <div class="logo">
                                    <a href="index.php" class="header-logo">
                                        <img src="https://res.cloudinary.com/dshdzqvuc/image/upload/v1734378641/Madinah_Pizza_Logo_Animation_lg0fpf.gif"
                                            alt="logo-img" style="width: 200px; height: 120px;">
                                    </a>
                                </div>
                                <div class="header-left">
                                    <div class="mean__menu-wrapper">
                                        <div class="main-menu">
                                            <nav id="mobile-menu">
                                                <ul>
                                                    <li class="has-dropdown active menu-thumb">
                                                        <a href="index.php">
                                                            Home
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="shop.php">
                                                            Shop
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="cart.php">
                                                            Cart
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="contact.php">
                                                            Contact Us
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="signin.php">
                                                            My Account
                                                        </a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>


                                <div class="header-right d-flex justify-content-end align-items-center">

                                    <div class="header__cart">
                                        <i class="fa-sharp fa-regular fa-cart-shopping"></i>
                                        <span class="cart-count">0</span> <!-- Cart count placeholder -->

                                        <div class="header__right__dropdown__wrapper">
                                            <div class="header__right__dropdown__inner">
                                                <p>Cart Empty...</p> <!-- Placeholder content -->
                                            </div>

                                            <div class="cart-summary">
                                                <p>Subtotal: <span class="subtotal">$0.00</span></p>
                                                <p>Tax (13%): <span class="tax">$0.00</span></p>
                                                <p>Total: <span class="total">$0.00</span></p>
                                            </div>
                                            <!-- Subtotal placeholder -->

                                            <div class="header__right__dropdown__button">
                                                <a href="cart.php" class="theme-btn mb-2">View Cart</a>
                                            </div>
                                        </div>
                                    </div>

                                    <style>
                                    .header__cart {
                                        position: relative;
                                        /* Ensure the cart count is positioned relative to the cart icon */
                                    }

                                    .cart-count {
                                        position: absolute;
                                        /* Position the count absolutely */
                                        top: -15px;
                                        /* Move it above the cart icon */
                                        right: -12px;
                                        /* Move it to the right of the cart icon */
                                        background-color: #ff5722;
                                        /* Example background color (orange) */
                                        color: #fff;
                                        /* Text color (white) */
                                        border-radius: 50%;
                                        /* Make it circular */
                                        padding: 5px 8px;
                                        /* Padding for better appearance */
                                        font-size: 12px;
                                        /* Font size */
                                        font-weight: bold;
                                        /* Make the text bold */
                                        line-height: 1;
                                        /* Ensure the text is centered vertically */
                                        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
                                        /* Add a subtle shadow */
                                    }

                                    .header__right__dropdown__inner {
                                        max-height: 300px;
                                        /* Adjust the height as needed */
                                        overflow-y: auto;
                                        /* Enables vertical scrolling */
                                        scrollbar-width: thin;
                                        /* Makes the scrollbar thin (for Firefox) */
                                        scrollbar-color: #Fc791a #F5dcc7;
                                        /* Red scrollbar on a dark background */
                                    }

                                    /* Customize scrollbar for Chrome, Edge, and Safari */
                                    .header__right__dropdown__inner::-webkit-scrollbar {
                                        width: 6px;
                                    }

                                    .header__right__dropdown__inner::-webkit-scrollbar-track {
                                        background: #F5dcc7;
                                        /* Dark background */
                                    }

                                    .header__right__dropdown__inner::-webkit-scrollbar-thumb {
                                        background: #Fc791a;
                                        /* Red scrollbar */
                                        border-radius: 4px;
                                    }

                                    .header__right__dropdown__inner::-webkit-scrollbar-thumb:hover {
                                        background: #cc0000;
                                        /* Darker red on hover */
                                    }
                                    </style>


                                    <?php if (isset($_SESSION['customer_id'])): ?>
                                    <span class="user-welcome-message">Welcome,
                                        <?= htmlspecialchars($_SESSION['customer_name']) ?>!</span>
                                    <a class="theme-btn" href="shop.php">ORDER NOW <i
                                            class="fa-sharp fa-regular fa-arrow-right"></i></a>
                                    <?php else: ?>
                                    <a class="theme-btn" href="signin.php">LOG IN <i
                                            class="fa-sharp fa-regular fa-arrow-right"></i></a>
                                    <?php endif; ?>

                                    <div class="header__hamburger d-xl-block my-auto">
                                        <div class="sidebar__toggle">
                                            <i class="fas fa-bars"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>


    <script>
    function updateCartUI() {
        fetch('fetch-cart.php')
            .then(response => response.json())
            .then(data => {
                // Update cart count
                document.querySelector('.cart-count').textContent = data.totalItems;

                // Update cart dropdown
                let cartDropdown = document.querySelector('.header__right__dropdown__inner');
                cartDropdown.innerHTML = ''; // Clear current content

                if (data.cartItems.length > 0) {
                    data.cartItems.forEach(item => {
                        let images = item.image_path.split(';');
                        let firstImage = images[0].trim();

                        cartDropdown.innerHTML += `
                        <div class="single__header__right__dropdown">
                            <div class="header__right__dropdown__img">
                                <a href="shop-details.php?id=${item.product_id}">
                                    <img loading="lazy" src="${firstImage}" alt="photo">
                                </a>
                            </div>
                            <div class="header__right__dropdown__content">
                                <a href="shop-details.php?id=${item.product_id}">${item.product_title}</a>
                                <p>${item.quantity} x <span class="price">$${parseFloat(item.total_price).toFixed(2)}</span></p>
                            </div>
                            <div class="header__right__dropdown__close">
                                <a href="#" onclick="removeFromCart(${item.cart_item_id})"><i class="icofont-close-line"></i></a>
                            </div>
                        </div>
                    `;
                    });

                    if (data.cartItems.length > 3) {
                        cartDropdown.style.maxHeight = "300px"; // Limit height
                        cartDropdown.style.overflowY = "auto"; // Enable scrolling
                    } else {
                        cartDropdown.style.maxHeight = "none"; // Remove limit
                        cartDropdown.style.overflowY = "visible"; // No scrolling
                    }
                } else {
                    cartDropdown.innerHTML = '<p>Your cart is empty.</p>';
                }

                // Update cart summary
                const subtotal = data.subtotal;
                const tax = subtotal * 0.13;
                const total = subtotal + tax;
                document.querySelector('.subtotal').textContent = `$${subtotal.toFixed(2)}`;
                document.querySelector('.tax').textContent = `$${tax.toFixed(2)}`;
                document.querySelector('.total').textContent = `$${total.toFixed(2)}`;
            })
            .catch(error => console.error('Error updating cart:', error));
    }

    function removeFromCart(cartItemId) {
        fetch('remove-from-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    cart_item_id: cartItemId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartUI();
                }
            })
            .catch(error => console.error('Error removing item:', error));
    }

    setInterval(updateCartUI, 1000);

    // Update cart UI on page load
    document.addEventListener('DOMContentLoaded', updateCartUI);
    </script>