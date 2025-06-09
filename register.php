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
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $message = "Username or email already exists.";
        $_SESSION['message'] = $message;
        header("Location: register.php");
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);
        $stmt->execute();
        $_SESSION['user_id'] = $stmt->insert_id; // Automatically log in the user
        $_SESSION['username'] = $username;
        $_SESSION['logged_in'] = true;
        header("Location: home.php");
        exit;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
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
        }

        h2 {
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
        <h2>Register</h2>
        <p style="color:green"><?= $message ?></p>

        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>
        
    </section>

</body>

</html>