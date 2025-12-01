<?php
require_once 'header.php';
require_once 'banner.php';
require_once 'connection.php';

// Fetch categories
$categoriesQuery = "SELECT * FROM categories";
$categoriesResult = $mysqli->query($categoriesQuery);

?>

<!-- Popular Dishes Section   S T A R T -->
<section class="popular-dishes-section fix section-padding">
    <div class="popular-dishes-wrapper style1">
        <div class="shape1 d-none d-xxl-block"><img src="assets/img/shape/popularDishesShape1_1.png" alt="shape">
        </div>
        <div class="shape2 float-bob-y d-none d-xxl-block"><img src="assets/img/shape/popularDishesShape1_2.png"
                alt="shape"></div>


        <div class="container">
            <div class="title-area">
                <div class="sub-title text-center wow fadeInUp" data-wow-delay="0.5s">
                    <img class="me-1" src="assets/img/icon/titleIcon.svg" alt="icon">POPULAR Items<img class="ms-1"
                        src="assets/img/icon/titleIcon.svg" alt="icon">
                </div>
                <h2 class="title wow fadeInUp" data-wow-delay="0.7s">Best Selling Items</h2>
            </div>
            <div class="dishes-card-wrap style1">
                <?php
                $query = "SELECT * FROM products WHERE product_availability = 'available' LIMIT 10";
                $result = mysqli_query($mysqli, $query);

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($product = mysqli_fetch_assoc($result)) {
                        $images = explode(';', $product['image_path']);
                        $firstImage = $images[0];
                        ?>



                <div class="dishes-card style1 wow fadeInUp" data-wow-delay="0.2s">
                    <div class="dishes-thumb">
                        <img src="<?php echo $firstImage; ?>" alt="thumb"
                            style="width: 163px; height: 162px; object-fit: cover;">
                    </div>

                    <!-- Updated Link to Shop Details -->
                    <a href="shop-details.php?id=<?php echo $product['id']; ?>">
                        <h3><?php echo htmlspecialchars($product['product_title']); ?></h3>
                    </a>
                    <p><?php echo htmlspecialchars($product['product_description']); ?></p>
                    <h6>$<?php echo number_format($product['product_price'], 2); ?></h6>
                    <div class="social-profile">
                        <ul>
                            <li> <a href="shop-details.php?id=<?php echo $product['id']; ?>"><i
                                        class="fa-regular fa-basket-shopping-simple"></i></a></li>
                        </ul>
                    </div>
                </div>

                <?php
                    }
                } else {
                    echo "<p>No popular items available.</p>";
                }
                ?>
            </div>
            <div class="btn-wrapper wow fadeInUp" data-wow-delay="0.9s">
                <a class="theme-btn" href="shop.php">VIEW ALL ITEM <i
                        class="fa-sharp fa-regular fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>

<?php
require_once 'cta.php';
?>

<!-- Food Menu Section  S T A R T -->
<section class="food-menu-section fix section-padding">
    <div class="burger-shape">
        <img src="assets/img/shape/ctaShape2_5.png" alt="img">
    </div>
    <div class="fry-shape">
        <img src="assets/img/shape/ctaShape2_5.png" alt="img">
    </div>
    <div class="food-menu-wrapper style1">
        <div class="container">
            <div class="food-menu-tab-wrapper style-bg">
                <div class="title-area">
                    <div class="sub-title text-center wow fadeInUp" data-wow-delay="0.5s">
                        <img class="me-1" src="assets/img/icon/titleIcon.svg" alt="icon">FOOD MENU<img class="ms-1"
                            src="assets/img/icon/titleIcon.svg" alt="icon">
                    </div>
                    <h2 class="title wow fadeInUp" data-wow-delay="0.7s">Madina Halal Pizza & Wings</h2>
                </div>
                <div class="food-menu-tab">
                    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                        <?php
                        $categoryQuery = "SELECT * FROM categories";
                        $categoryResult = mysqli_query($mysqli, $categoryQuery);
                        $isFirst = true;

                        if ($categoryResult && mysqli_num_rows($categoryResult) > 0) {
                            while ($category = mysqli_fetch_assoc($categoryResult)) {
                                ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $isFirst ? 'active' : ''; ?>"
                                id="pills-<?php echo $category['category_id']; ?>-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-<?php echo $category['category_id']; ?>" type="button" role="tab"
                                aria-controls="pills-<?php echo $category['category_id']; ?>"
                                aria-selected="<?php echo $isFirst ? 'true' : 'false'; ?>">
                                <?php echo htmlspecialchars($category['category_title']); ?>
                            </button>
                        </li>
                        <?php
                                $isFirst = false;
                            }
                        }
                        ?>
                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        <?php
                        $categoryResult = mysqli_query($mysqli, $categoryQuery); // Reset query pointer
                        $isFirst = true;

                        if ($categoryResult && mysqli_num_rows($categoryResult) > 0) {
                            while ($category = mysqli_fetch_assoc($categoryResult)) {
                                ?>
                        <div class="tab-pane fade <?php echo $isFirst ? 'show active' : ''; ?>"
                            id="pills-<?php echo $category['category_id']; ?>" role="tabpanel"
                            aria-labelledby="pills-<?php echo $category['category_id']; ?>-tab" tabindex="0">
                            <div class="row gx-60">
                                <?php
                                        $productQuery = "SELECT * FROM products WHERE category_id = " . $category['category_id'] . " AND product_availability = 'available'";
                                        $productResult = mysqli_query($mysqli, $productQuery);

                                        if ($productResult && mysqli_num_rows($productResult) > 0) {
                                            while ($product = mysqli_fetch_assoc($productResult)) {
                                                $images = explode(';', $product['image_path']);
                                                $firstImage = $images[0];
                                                ?>
                                <div class="col-lg-6">
                                    <div class="single-menu-items">
                                        <div class="details">
                                            <div class="menu-item-thumb">
                                                <a href="shop-details.php?id=<?php echo $product['id']; ?>">
                                                    <img src="<?php echo $firstImage; ?>" alt="thumb"
                                                        style="width: 163px; height: 162px; object-fit: cover;">
                                                </a>
                                            </div>

                                            <div class="menu-content"><a
                                                    href="shop-details.php?id=<?php echo $product['id']; ?>">
                                                    <h3><?php echo htmlspecialchars($product['product_title']);
                                            ?></h3>
                                                </a>
                                                <p><?php echo htmlspecialchars($product['product_description']);
                                            ?></p>
                                            </div>
                                        </div>
                                        <h6>$<?php echo number_format($product['product_price'], 2);
                                            ?></h6>
                                    </div>
                                </div><?php
                                            }
                                            }

                                            else {
                                                echo "<p>No items available in this category.</p>";
                                            }

                                            ?>
                            </div>
                        </div><?php $isFirst=false;
                                            }
                                            }

                                            ?>
                    </div>
                </div>
            </div>
        </div>
    </div><?php require_once 'wrapper.php';
                                            ?>
</section><?php require_once 'footer.php';
                                            require_once 'script.php';
                                            ?></body>

</html>