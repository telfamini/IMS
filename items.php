<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'user'; 
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT ItemID, Item, Price, img FROM items";
$result = $conn->query($sql);
$items = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($_SESSION['role'] !== 'admin') {
        die("Unauthorized access.");
    }

    if (isset($_POST['add'])) {

        $itemId = $_POST['itemid'];
        $itemName = $_POST['itemname'];
        $price = $_POST['price'];
        $photo = $_FILES['photo']['name'];
        $photoTmp = $_FILES['photo']['tmp_name'];

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'jfif'];
        $file_extension = strtolower(pathinfo($photo, PATHINFO_EXTENSION));

        if (in_array($file_extension, $allowed_extensions)) {
            $upload_path = $photo;
            if (move_uploaded_file($photoTmp, $upload_path)) {
                $sql = "INSERT INTO items (ItemID, Item, Price, img) VALUES ('$itemId', '$itemName', '$price', '$photo')";
                if ($conn->query($sql) === TRUE) {
                    echo "Item added successfully!";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            } else {
                echo "Error uploading the image.";
            }
        } else {
            echo "Invalid file format. Only jpg, jpeg, png, gif, and jfif are allowed.";
        }
    }

    if (isset($_POST['update'])) {
  
        $itemId = $_POST['itemid'];
        $itemName = $_POST['itemname'];
        $price = $_POST['price'];
        $photo = $_FILES['photo']['name'];
        $photoTmp = $_FILES['photo']['tmp_name'];

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'jfif'];
        $file_extension = strtolower(pathinfo($photo, PATHINFO_EXTENSION));

        if ($photo == '' || in_array($file_extension, $allowed_extensions)) {
            $sql = "UPDATE items SET Item='$itemName', Price='$price'";
            if ($photo) {
                $filePath = $photo;
                if (move_uploaded_file($photoTmp, $filePath)) {
                    $sql .= ", img='$photo'";
                } else {
                    echo "Failed to upload the image.";
                    exit;
                }
            }
            $sql .= " WHERE ItemID='$itemId'";
            if ($conn->query($sql) === TRUE) {
                echo "Item updated successfully!";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo "Invalid file format.";
        }
    }

    if (isset($_POST['delete'])) {
 
        $itemId = $_POST['itemid'];
        $sql = "DELETE FROM items WHERE ItemID='$itemId'";
        if ($conn->query($sql) === TRUE) {
            echo "Item deleted successfully!";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CinnaShop</title>
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
        .dropdown-container {
            display: flex;
            flex-direction: column; 
            margin: 20px; 
        }
        .dropdown {
            margin-bottom: 10px; 
        }
        .content-container {
            display: none; 
            background-color: rgba(173, 216, 230, 0.8);
            border-radius: 15px; 
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            display: grid;
            grid-template-columns: repeat(4, 1fr); 
            gap: 20px; 
        }   
        .product {
            background-color: lightblue;
            border-radius: 15px; 
            padding: 10px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center; 
        }
        .product img {
            max-width: 100%;
            max-height: 100px; 
            border-radius: 10px;
        }
        .product p {
            margin: 5px 0; 
        }
        input[type="submit"] {
            background-color: #a0d3e8;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px; 
            cursor: pointer;
            margin-top: 5px; 
        }
        button[type="submit"]{
            background-color: #a0d3e9;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px; 
            cursor: pointer;
            margin-top: 5px; 
        }
        button[type="submit"]:hover{
            background-color: #8dc9e1;
        }
        input[type="submit"]:hover {
            background-color: #8dc7e1;
        }
        .toggle-button {
            background-color: #a0d3e8;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px;
            cursor: pointer;
            width: 100%; 
        }
        .toggle-button:hover {
            background-color: #8dc7e1;
        }
        .item-manage {
            background-color: lightblue;
            border-radius: 15px; 
            padding: 10px;
            text-align: center;
            display: flex;
            flex-direction: column; 
            align-items: center;  
        }
        .item-manage a {
            background-color: #8dc7e1;
            color: darkslategrey;
          
        }
               
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px; 
            padding: 20px;
            box-sizing: border-box;
            margin: 20px;
            background-color: rgba(173, 216, 230, 0.8);
            border-radius: 15px; 
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

      
        .product-item {
            border: 1px solid #ccc;
            padding: 15px;
            text-align: center;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product-item img {
            width: 100%;
            max-height: 200px;
            object-fit: cover; 
        }

        .product-item:hover {
            transform: scale(1.05); 
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2); 
        }

        .product-item p {
            margin: 10px 0;
        }

        

        .product-item input[type="submit"]:hover {
            background-color: #8dc7e1;
         
        }


        .footer {
    background: #ffffff;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    bottom: 0;
    width: 100%;
    box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    font-size: 14px;
    color: #555;
    box-sizing: border-box;
}

.footer p {
    margin: 0;
    font-size: 14px;
    color: #555;
}

.footer .admin-text {
    margin-left: auto;
    font-weight: bold;
    color: lightskyblue;
    font-size: 14px;
    text-decoration: underline;
    cursor: pointer;
}

.footer .admin-text:hover {
    color: #0056b3;
}

    </style>
</head>
<body>

<div class="header">
    <div class="logo">
        <img src="images/Stellar.png" alt="CinnaShop Logo">
        <h1 style="color: lightskyblue;">Stellar Shop</h1>
    </div>
    <nav class="menu">
        <a href="home.php">Home</a>
        <a href="items.php">Items</a>
        <a href="add_to_cart.php">Add to Cart</a>
        <a href="orders.php">Orders</a>
        <a href="profile.php">Profile</a>
    </nav>
</div>

<div class="product-grid">
    <?php

    $sql = "SELECT ItemID, Item, Price, img FROM items";  
    $result = $conn->query($sql);


    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            
            echo '<div class="product-item">';
           
            echo '<img src="' . htmlspecialchars($row['img']) . '" alt="' . htmlspecialchars($row['Item']) . '">';
            echo '<p><b>' . htmlspecialchars($row['Item']) . '</b></p>';
            echo '<p>$' . number_format($row['Price'], 2) . '</p>';
            
            echo '<form method="POST" action="add_to_cart.php">';
            echo '<input type="hidden" name="item_id" value="' . $row['ItemID'] . '">';
            echo '<input type="hidden" name="item_name" value="' . htmlspecialchars($row['Item']) . '">';
            echo '<input type="hidden" name="item_price" value="' . $row['Price'] . '">';
            echo '<input type="hidden" name="item_img" value="' . htmlspecialchars($row['img']) . '">';
            
            echo ' <input type="submit" value="Add To Cart">';
            echo '</form>';
            
            echo '</div>';
        }
    } else {
        echo "Product not found.";
    }
    ?>
</div>

<footer class="footer">
    <p>&copy; 2024 Stellar Shop. All Rights Reserved.</p>
    <?php if ($_SESSION['role'] === 'admin'): ?>
        <button onclick="toggleDropdown('admin-management')"></button>
    <?php endif; ?>
</footer>

<?php if ($_SESSION['role'] === 'admin'): ?>
<div id="admin-management" style="display: none;">
    <div class="item-manage">
        <h1>Items Management</h1>
        <h2>Add an Item</h2>
        <form action="items.php" method="post" enctype="multipart/form-data">
            <input type="text" name="itemid" placeholder="Item ID" required>
            <input type="text" name="itemname" placeholder="Item Name" required>
            <input type="text" name="price" placeholder="Price" required>
            <input type="file" name="photo" id="photo"><br>
            <button type="submit" name="add">Add Item</button>
        </form>

        <h2>Update an Item</h2>
        <form action="items.php" method="post" enctype="multipart/form-data">
            <input type="text" name="itemid" placeholder="Item ID" required>
            <input type="text" name="itemname" placeholder="Item Name">
            <input type="text" name="price" placeholder="Price">
            <input type="file" name="photo" id="photo"><br>
            <button type="submit" name="update">Update Item</button>
        </form>

        <h2>Delete an Item</h2>
        <form action="items.php" method="post">
            <input type="text" name="itemid" placeholder="Item ID" required>
            <button type="submit" name="delete">Delete Item</button>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function toggleDropdown(id) {
    const element = document.getElementById(id);
    if (element.style.display === 'none' || element.style.display === '') {
        element.style.display = 'block';
    } else {
        element.style.display = 'none';
    }
}
</script>

</body>
</html>
