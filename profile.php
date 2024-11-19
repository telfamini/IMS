<?php

session_start(); // Start the session
include 'db.php';

try {
    $pdo = new PDO("mysql:localhost=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}

function get_all_users() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, username, email, status, role, photo FROM users");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$errorMessages = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['admin_action'])) {
        $userId = $_POST['user_id'] ?? null;
        $action = $_POST['action'] ?? null;

        if (!$userId || !$action) {
            $errorMessages[] = "Error: Missing user ID or action.";
        } else {
            if ($action === 'deactivate') {
                $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && $user['status'] === 'inactive') {
                    $errorMessages[] = "Error: User is already inactive.";
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
                    $stmt->execute([$userId]);
                    $errorMessages[] = "User successfully deactivated!";
                }
            } elseif ($action === 'activate') {
                $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
                $stmt->execute([$userId]);
                $errorMessages[] = "User successfully activated!";
            } elseif ($action === 'update_role') {
                $role = $_POST['role'] ?? null;

                if (!$role) {
                    $errorMessages[] = "Error: Role not provided.";
                } elseif (!in_array($role, ['user', 'admin'], true)) {
                    $errorMessages[] = "Error: Invalid role selected.";
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                    $stmt->execute([$role, $userId]);
                    $errorMessages[] = "User role successfully updated to '$role'!";
                }
            }
        }
    }
}

$users = get_all_users();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stellar Shop</title>
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
        .profile-img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
    </style>
</head>
<body>

<div class="header">
    <div class="logo">
        <img src="images/Stellar.png" alt="Logo">
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
    <h2>User Profile</h2>

    <div id="register">
        <h3>User Registration</h3>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="register" value="true">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br><br>
            <button type="button" class="show-password" onclick="togglePassword()">Show Password</button><br><br>

            <label for="photo">Upload Photo:</label>
            <input type="file" id="photo" name="photo"><br><br>

            <button type="submit">Register</button>
        </form>
    </div>

    <div id="update">
        <h3>Update Your Profile</h3>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="update" value="true">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br><br>

            <label for="photo">Upload New Photo:</label>
            <input type="file" id="photo" name="photo"><br><br>

            <button type="submit">Update</button>
        </form>
    </div>

    <div id="admin">
    <h3>Manage Users (Admin)</h3>
    <table>
        <thead>
            <tr>
                <th>Profile</th>
                <th>&nbsp; &nbsp; Username</th>
                <th>&nbsp; &nbsp; Email</th>
                <th>&nbsp;  &nbsp; Status</th>
                <th>&nbsp; &nbsp; Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <?php if (!empty($user['photo'])): ?>
                            <img src="<?= $user['photo'] ?>" alt="Profile" class="profile-img">
                        <?php else: ?>
                            <img src="images/default-avatar.png" alt="Default Avatar" class="profile-img">
                        <?php endif; ?>
                    </td>
                    <td>&nbsp; &nbsp; <strong><?= $user['username'] ?></strong></td>
                    <td>&nbsp; &nbsp; <?= $user['email'] ?></td>
                    <td>&nbsp; &nbsp; <strong> <?= ucfirst($user['status']) ?></strong></td>
                    <td>
                        <form action="" method="POST">
                            <input type="hidden" name="admin_action" value="true">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            &nbsp; &nbsp;
                            <button type="submit" name="action" value="<?= $user['status'] === 'active' ? 'deactivate' : 'activate' ?>">
                                <?= $user['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                            </button>
                            <select name="role">
                                <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                                <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                            <button type="submit" name="action" value="update_role">Update Role</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
if (!empty($errorMessages)) {
    foreach ($errorMessages as $message) {
        echo "<script>alert('$message');</script>";
    }
}
?>

</body>
</html>
