<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Add product to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = 1;

    // Check if the product already exists in the cart
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cart_item) {
        // Update quantity if product already exists
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
    } else {
        // Insert new product into cart
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }

    header("Location: cart.php");
    exit();
}

// Update quantity in the cart
if (isset($_POST['update_quantity'])) {
    $cart_id = $_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];

    if ($quantity > 0) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$quantity, $cart_id, $user_id]);
    }

    header("Location: cart.php");
    exit();
}

// Remove product from the cart
if (isset($_POST['remove_from_cart'])) {
    $cart_id = $_POST['cart_id'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);

    header("Location: cart.php");
    exit();
}

// Fetch user's cart items
$stmt = $conn->prepare("SELECT cart.id AS cart_id, products.name, products.price, cart.quantity 
                        FROM cart 
                        JOIN products ON cart.product_id = products.id 
                        WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_cost = 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>

    <header>
        <h1>Your Cart</h1>
        <a href="products.php">Continue Shopping</a>
    </header>

    <main>
        <?php if (empty($cart_items)): ?>
            <p>Your cart is empty.</p>
        <?php else: ?>
            <table border="1">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <?php $item_total = $item['price'] * $item['quantity']; ?>
                        <?php $total_cost += $item_total; ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']); ?></td>
                            <td>$<?= number_format($item['price'], 2); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="cart_id" value="<?= $item['cart_id']; ?>">
                                    <input type="number" name="quantity" value="<?= $item['quantity']; ?>" min="1">
                                    <button type="submit" name="update_quantity">Update</button>
                                </form>
                            </td>
                            <td>$<?= number_format($item_total, 2); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="cart_id" value="<?= $item['cart_id']; ?>">
                                    <button type="submit" name="remove_from_cart">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h3>Total Cost: $<?= number_format($total_cost, 2); ?></h3>
            <a href="checkout.php">Proceed to Checkout</a>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?= date('Y'); ?> Online Store. All rights reserved.</p>
    </footer>

</body>

</html>
