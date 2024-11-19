<?php
session_start();
include 'db.php';

function login($email, $password) {
    global $db;
    $query = "SELECT * FROM users WHERE email = ? LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ?message=You have been logged out.");
    exit();
}

$error_message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    if (!login($email, $password)) {
        $error_message = "Invalid email or password.";
    }
}

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    if (!isset($_POST['login'])) {
        echo "<script>window.location.href = '?login=true&message=Please log in to access this page.';</script>";
        exit();
    }
}

$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
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
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .login-container form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .login-container input {
            padding: 10px;
            font-size: 1em;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .login-container button {
            padding: 10px;
            font-size: 1em;
            font-weight: bold;
            background-color: lightskyblue;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .login-container button:hover {
            background-color: #0056b3;
            color: white;
        }
    </style>
</head>
<body>
    <?php if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']): ?>
        <!-- Login Form -->
        <div class="login-container">
            <form method="POST" action="">
                <h2>Login</h2>
                <p><?php echo $_GET['message'] ?? ''; ?></p>
                <p><?php echo $error_message ?? ''; ?></p>
                <label>Email:</label>
                <input type="email" name="email" required>
                <label>Password:</label>
                <input type="password" name="password" required>
                <button type="submit" name="login">Login</button>
            </form>
        </div>
    <?php else: ?>
        <!-- Main Content -->
        <div class="header">
            <div class="logo">
                <img src="images/Stellar.png" alt="Logo">
                <h1 style="margin: 0; font-size: 24px; color: lightskyblue;">Stellar Shop</h1>
            </div>
            <nav class="menu">
                <a href="?page=items">Items</a>
                <a href="?page=add_to_cart">Add to Cart</a>
                <a href="?page=orders">Orders</a>
                <a href="?page=profile">Profile</a>
                <?php if ($is_admin): ?>
                    <a href="?page=admin_page">Admin Page</a>
                <?php endif; ?>
                <a href="?logout=true">Logout</a>
            </nav>
        </div>

        <div class="content-container">
            <?php
            if (isset($_GET['page'])) {
                if ($_GET['page'] === 'admin_page' && !$is_admin) {
                    echo "<h2>Access Denied</h2><p>You do not have permission to view this page.</p>";
                } else {
                    echo "<h2>Welcome to Stellar Shop!</h2><p>Your one-stop shop for all things sanrio.</p>";
                }
            } else {
                echo "<h2>Welcome to Stellar Shop!</h2><p>Your one-stop shop for all things sanrio.</p>";
            }
            ?>
        </div>
    <?php endif; ?>
</body>
</html>
