<?php
session_start();
include 'includes/db.php'; // Include the database connection

// Fetch products from the database
$stmt = $conn->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: pages/login.php");
    exit();
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Store</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- Header Section -->
    <header>
        <div class="header-container">
            <h1>Welcome to Our Store</h1>
            <nav>
                <?php if ($is_logged_in) : ?>
                    <a href="pages/cart.php" class="cart-link">
                        <img src="images/cart-icon.png" alt="Cart" class="cart-icon"> Cart
                    </a>
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="logout" class="logout-button">Logout</button>
                    </form>
                <?php else : ?>
                    <a href="pages/login.php">Login</a>
                    <a href="pages/register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Main Section -->
    <div class="main-container">
        <main>
            <h2>Products</h2>
            <div class="product-list">

                <?php if (empty($products)) : ?>
                    <p>No products available.</p>
                <?php else : ?>
                    <?php foreach ($products as $product) : ?>
                        <div class="product">
                            <h3><?= htmlspecialchars($product['name']); ?></h3>
                            <p>Price: $<?= number_format($product['price'], 2); ?></p>
                            <p><?= htmlspecialchars($product['description']); ?></p>

                            <!-- Display Product Image -->
                            <?php if (!empty($product['image']) && file_exists("images/" . $product['image'])) : ?>
                                <img src="images/<?= htmlspecialchars($product['image']); ?>" alt="<?= htmlspecialchars($product['name']); ?>" class="product1">
                            <?php else : ?>
                                <img src="images/default.png" alt="Default Image" class="product1">
                            <?php endif; ?>

                            <!-- Add to Cart Form -->
                            <form method="POST" action="pages/cart.php">
                                <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                                <button type="submit" name="add_to_cart" class="add-to-cart-button">Add to Cart</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </main>
    </div>

    <!-- Footer Section -->
    <footer>
        <p>&copy; <?= date('Y'); ?> online store</p>
    </footer>

</body>

</html>
