
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-image: url('Images/backg.png'); 
            background-size: center;
            background-position: center;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .logo {
            display: flex;
            align-items: center;
        }
        .logo img {
            max-width: 100px; 
            height: auto; 
            margin-right: 10px; 
        }
        .menu {
            display: flex;
            gap: 20px;
        }
        .menu a {
            text-decoration: none;
            color: lightskyblue;
            font-weight: bold;
        }
        .menu a:hover {
            color: #0056b3; 
        }
        .content-container {
            display: none;
            background-color: rgba(173, 216, 230, 0.8);
            border-radius: 15px; 
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }   
        .cart-container {
            margin: 20px;
        }
        .cart-container table {
            width: 100%;
            border-collapse: collapse;
        }
        .cart-container th, .cart-container td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .total-price {
            margin-top: 20px;
            text-align: right;
            font-size: 18px;
        }
        .cart-buttons {
            margin-top: 20px;
            text-align: center;
        }
        .cart-buttons button {
            background-color: #a0d3e8;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
        }
        .cart-buttons button:hover {
            background-color: #8dc7e1;
        }
        .cart-buttons a {
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="header">
    <div class="logo">
        <img src="images/Stellar.png" alt="CinnaShop Logo">
        <h1 style="margin: 0; font-size: 24px; color: lightskyblue;">Stellar Shop</h1>
    </div>
    <nav class="menu">
        <a href="items.php">Items</a>
        <a href="home.php">Home</a>
        <a href="add_to_cart.php">Add to Cart</a>
        <a href="orders.php">Orders</a>
        <a href="profile.php">Profile</a>
    </nav>
</div>
<?php
session_start(); // Start the session

// Include database connection
include 'db.php';  

// Initialize total price
$total_price = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $mode = "online"; // Example mode; can be dynamically set or from user input
    $status = "pending"; // Initial status

    echo "<h1>Checkout Confirmation</h1>";
    echo "<p>Review your order before finalizing:</p>";

    // Process each item in the cart
    foreach ($_SESSION['cart'] as $cart_item) {
        $item_id = $cart_item['id'];
        $item_name = $cart_item['name'];
        $item_price = $cart_item['price'];
        $quantity = $cart_item['quantity']; // Quantity of the item
        $total_item_price = $item_price * $quantity; // Calculate total item price

        // Add the item total price to the overall total
        $total_price += $total_item_price;

        // Insert the order into the database
        $sql = "INSERT INTO orders (ItemID, item, price, mode, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $item_id, $item_name, $item_price, $mode, $status);

        if ($stmt->execute()) {
            echo "<p>Item: <strong>{$item_name}</strong> - Quantity: <strong>{$quantity}</strong> - Total Price: $<strong>" . number_format($total_item_price, 2) . "</strong> added to your order.</p>";
        } else {
            echo "<p>Error processing item: {$item_name}. Please try again.</p>";
        }
    }

    // Clear the session cart after checkout
    unset($_SESSION['cart']);

    // Display the total of all items
    echo "<p>Total for all items: <strong>$" . number_format($total_price, 2) . "</strong></p>";
    echo "<p>Your order has been placed successfully!</p>";
    echo "<a href='orders.php'>View Your Orders</a>";
} else {
    echo "<p>No items in your cart to checkout.</p>";
    echo "<a href='items.php'>Continue Shopping</a>";
}
?>
</body>
</html>
