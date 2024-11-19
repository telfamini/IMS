<?php
session_start(); // Start the session

// Include database connection
include 'db.php';  

// Handle item addition to the cart
if (isset($_POST['item_id'])) {
    $item_id = $_POST['item_id'];
    
    // Ensure the necessary values exist before using them
    if (isset($_POST['item_name'], $_POST['item_price'], $_POST['item_img'])) {
        $item_name = $_POST['item_name'];
        $item_price = $_POST['item_price'];
        $item_img = $_POST['item_img'];

        // Check if the item already exists in the session cart
        $item_exists = false;
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as &$cart_item) {
                if ($cart_item['id'] == $item_id) {
                    $cart_item['quantity']++; // Increment quantity if the item exists
                    $item_exists = true;
                    break;
                }
            }
        }

        // Add the item to the cart if it does not exist
        if (!$item_exists) {
            $_SESSION['cart'][] = [
                'id' => $item_id,
                'name' => $item_name,
                'price' => $item_price,
                'image' => $item_img,
                'quantity' => 1
            ];
        }

        // Database insertion for orders (make sure $mode and $status are defined)
        $mode = "example_mode"; // Replace with appropriate logic
        $status = "pending";

        $sql = "INSERT INTO orders (ItemID, item, price, img, mode, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssss", $item_id, $item_name, $item_price, $item_img, $mode, $status);
        $stmt->execute();

        // Redirect back to the cart page
        header("Location: add_to_cart.php");
        exit();
    } else {
        echo "Error: Missing item details.";
    }
}

// Handle item removal from the cart
if (isset($_POST['remove_item'])) {
    $item_id = $_POST['item_id'];

    // Loop through the session cart to find the item
    foreach ($_SESSION['cart'] as $key => &$cart_item) {
        if ($cart_item['id'] == $item_id) {
            if ($cart_item['quantity'] > 1) {
                // Decrease the quantity by 1 if it's greater than 1
                $cart_item['quantity']--;
            } else {
                // Remove the item from the cart if the quantity is 1
                unset($_SESSION['cart'][$key]);
            }
            break; // Exit the loop once the item is found and modified
        }
    }

    // Reindex the cart array to avoid gaps
    $_SESSION['cart'] = array_values($_SESSION['cart']);

    // Redirect to the cart page to reflect changes
    header('Location: add_to_cart.php');
    exit();
}

// Handle multiple item checkout
if (isset($_POST['checkout_items'])) {
    $selected_items = $_POST['selected_items'] ?? [];

    if (!empty($selected_items)) {
        $mode = "example_mode"; // Replace with appropriate logic
        $status = "pending"; // Set the initial status as pending

        // Process checkout for each selected item
        foreach ($selected_items as $item_id) {
            // Find the item in the session cart
            foreach ($_SESSION['cart'] as $cart_item) {
                if ($cart_item['id'] == $item_id) {
                    $item_name = $cart_item['name'];
                    $item_price = $cart_item['price'];

                    // Insert the item into the orders table
                    $sql = "INSERT INTO `orders` (ItemID, item, price, mode, status) 
                            VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("issss", $item_id, $item_name, $item_price, $mode, $status);
                    $stmt->execute();
                }
            }
        }
    } else {
        echo "No items selected for checkout.";
    }

    // Redirect to prevent form resubmission
    header('Location: checkout.php');
    exit();
}

// Display cart items
$cart_items = [];
$total_price = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $cart_item) {
        $item_id = $cart_item['id'];
        $item_quantity = $cart_item['quantity'];

        $sql = "SELECT ItemID, Item, Price, img FROM items WHERE ItemID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $cart_items[] = [
                'id' => $item_id,
                'name' => $row['Item'],
                'price' => $row['Price'],
                'image' => $row['img'],
                'quantity' => $item_quantity,
            ];

            $total_price += $row['Price'] * $item_quantity;
        }
    }
}
?>
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
            background-color: rgba(173, 216, 230, 0.8);
            border-radius: 15px; 
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
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
        .button{ background-color: #a0d3e8;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;}
        .button:hover {  background-color: #8dc7e1;}
    </style>
</head>
<body>
<div class="header">
    <div class="logo">
        <img src="images/Stellar.png" alt="CinnaShop Logo">
        <h1 style="margin: 0; font-size: 24px; color: lightskyblue;">Stellar Shop</h1>
    </div>
    <nav class="menu">
        <a href="home.php">Home</a>
        <a href="items.php">Items</a>  
        <a href="add_to_cart.php">Add to Cart</a>
        <a href="orders.php">Orders</a>
        <a href="profile.php">Profile</a>
    </nav>
</div>

<div class="content-container">
    <h1 style="margin: 0; font-size: 24px; color: white;">MY CART</h1>
</div>

<div class="cart-container">
    <?php if (empty($cart_items)): ?>
        <p>Your cart is empty. Add some items to your cart!</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $cart_item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cart_item['name']); ?></td>
                        <td>$<?php echo number_format($cart_item['price'], 2); ?></td>
                        <td><?php echo $cart_item['quantity']; ?></td>
                        <td>
                            <form method="POST" action="add_to_cart.php">
                                <input type="hidden" name="item_id" value="<?php echo $cart_item['id']; ?>">
                                <button type="submit" name="remove_item">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-price">
            <strong>Total Price: $<?php echo number_format($total_price, 2); ?></strong>
        </div>

        <div class="cart-buttons">
        
            <form action="checkout.php" method="POST">
            <a href="items.php" class= "button" type="button">Continue to Shop</a>
                 <button type="submit" name="checkout_items">Proceed to Checkout</button>
            </form>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
