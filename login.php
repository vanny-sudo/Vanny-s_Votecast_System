<?php
session_start();

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
} else {
    $message = "";
}

require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['logged_in'] = true; // Add session flag
            header("Location: home.php");
            exit;
        } else {
            $message = "Incorrect password.";
            $_SESSION['message'] = $message;
            header("Location: login.php");
        }
    } else {
        $message = "User not found.";
        $_SESSION['message'] = $message;
        header("Location: login.php");
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <style>
        body {
            font-family: Arial;
            padding: 20px;
            background: #007BFF;
        }

        form {
            max-width: 400px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        h2 {
            text-align: center;
        }

        p {
            text-align: center;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
        }

        button {
            padding: 10px 20px;
        }
    </style>
    <link href="bootstrap.min.css" rel="stylesheet">
    <link href="templatemo-topic-listing.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <i class="bi-back"></i>
                <span>Voting System</span>
            </a>

            <div class="d-lg-none ms-auto me-4">
                <a href="#top" class="navbar-icon bi-person smoothscroll"></a>
            </div>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-lg-5 me-lg-auto">
                    <li class="nav-item">
                        <a class="nav-link click-scroll" href="index.php" style="color: #333;">Home</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link click-scroll" href="register.php" style="color: #333;">Create Account</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link click-scroll" href="login.php" style="color: #333;">Login</a>
                    </li>
                </ul>

                <div class="d-none d-lg-block">
                    <a href="#top" class="navbar-icon bi-person smoothscroll"></a>
                </div>
            </div>
        </div>
    </nav>
    
    <section style="margin-top: 50px;">
        <h2>Login</h2>
        <p style="color:red"><?= $message ?></p>

        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" style="margin-top: 10px;">Login</button>
        </form>
        
        <p>Don't have an account? <a href="register.php">Register here</a>.</p>
    </section>
</body>

</html>