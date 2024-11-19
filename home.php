<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Safely access the username
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CinnaShop Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-image: url('Images/backg.png'); 
            background-size: cover;
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
            background-color: rgba(173, 216, 230, 0.8);
            border-radius: 15px; 
            padding: 20px;
            margin: 20px; 
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
<div class="header">
    <div class="logo">
        <img src="images/Stellar.png" alt="Logo">
        <h1 style="margin: 0; font-size: 24px; color: lightskyblue;" >Stellar Shop</h1>
    </div>
    <nav class="menu">
        <a href="items.php">Items</a>
        <a href="add_to_cart.php">Add to Cart</a>
        <a href="orders.php">Orders</a>
        <a href="profile.php">Profile</a>
        <a href="out.php">Logout</a>
    </nav>


</div>

<div class="content-container">
<h2>Welcome to Stellar Shop, <?php echo $username; ?>!</h2> 
    <p>Your one-stop shop for all things sanrio.</p>
</div>

</php>
</body>
</html>