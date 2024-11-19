<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['register'])) {
        $register_username = $_POST['username'];  // username for display purposes
        $register_email = $_POST['email'];        // email as unique identifier
        $register_password = $_POST['password'];

        // Check if the email already exists in the database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $register_email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "<script>alert('Email already taken.');</script>";
        } else {
            // Hash the password before storing
            $hashed_password = password_hash($register_password, PASSWORD_DEFAULT);

            // Insert new user into the database
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, photo, status, role, created_at, updated_at) VALUES (?, ?, ?, '', '', '', '', '')");
            $stmt->bind_param("sss", $register_username, $register_email, $hashed_password);

            if ($stmt->execute()) {
                echo "<script>alert('Registration successful! You can now log in.'); window.location.href = 'index.php';</script>";
            } else {
                echo "<script>alert('Error: " . $stmt->error . "');</script>";
            }
        }

        $stmt->close();
    }

    elseif (isset($_POST['login'])) {
        $input_email = $_POST['email'];  // Use email as the login identifier
        $input_password = $_POST['password'];
        $register_username = $_POST['username'];  

        $stmt = $conn->prepare("SELECT password, username FROM users WHERE email = ?");
        $stmt->bind_param("s", $input_email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($hashed_password, $register_username);
            $stmt->fetch();
            if (password_verify($input_password, $hashed_password)) {
                $_SESSION['email'] = $input_email;
                $_SESSION['username'] = $register_username; // Add username to the session
                echo "<script>window.location.href = 'home.php';</script>";
            } else {
                echo "<script>alert('Invalid password.');</script>";
            }
        } else {
            echo "<script>alert('No user found with that email.');</script>";
        }   

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <style>
        body {  
            background-image: url('Images/backg.png');
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 300px; 
        }
        h2 {
            color: #82caff;
            text-align: center; 
        }
        input[type="text"], input[type="password"] {
            width: calc(100% - 3px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #b0e0e6;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button[type="submit"] {
            background-color: #a0d3e8;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        button[type="submit"]:hover {
            background-color: #8dc7e1;
        }
        img {
            max-width: 100%;
            height: 100px;
            margin-left: 60px;
        }
        p {
            color: gray;
            font-size: 12px;
            text-align: center;
            text-decoration: none;
        }
        .show-password {
            font-size: 12px;
            color: #007bff;
            cursor: pointer;
            text-align: left;
            display: block;
            margin-top: -8px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <img src="Images/Stellar.png" alt="Logo">
    <h2>Login</h2>

    <form action="index.php" method="post">
        <input type="text" name="email" placeholder="Email" required>
        <div>
            <input type="password" name="password" id="loginPassword" placeholder="Password" required>
            <span class="show-password" onclick="togglePassword('loginPassword')">Show Password</span>
        </div>

        <button type="submit" name="login">Login</button>
        
        <p>If you don't have an account, <a href="#registerForm">click here to Register</a></p>
    </form>

    <div id="registerForm" style="display:none;">
        <h2>Register</h2>
        <form action="index.php" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="text" name="email" placeholder="Email" required>
            <div>
                <input type="password" name="password" id="registerPassword" placeholder="Password" required>
                <span class="show-password" onclick="togglePassword('registerPassword')">Show Password</span>
            </div>
            
            <button type="submit" name="register">Register</button>
        </form>
    </div>
</div>

<script>
    document.querySelector('a[href="#registerForm"]').addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector('#registerForm').style.display = 'block';
    });

    function togglePassword(passwordFieldId) {
        const passwordField = document.getElementById(passwordFieldId);
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
        } else {
            passwordField.type = 'password';
        }
    }
</script>

</body>
</html>