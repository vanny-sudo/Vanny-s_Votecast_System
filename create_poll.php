<?php
session_start();


if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit;
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
} else {
    $message = "";
}

require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $options = array_filter($_POST['options'], fn($opt) => trim($opt) !== "");
    $user_id = $_SESSION['user_id'];

    if (empty($user_id)) {
        die("You must be logged in to create a poll.");
    }

    if ($title && count($options) >= 2) {
        $stmt = $conn->prepare("INSERT INTO polls (title, description, user_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $title, $description, $user_id);
        $stmt->execute();

        $poll_id = $stmt->insert_id;
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO options (poll_id, option_text) VALUES (?, ?)");

        foreach ($options as $opt) {
            $opt = trim($opt);
            $stmt->bind_param("is", $poll_id, $opt);
            $stmt->execute();
        }

        $stmt->close();

        $_SESSION['message'] = "Poll created successfully!";
        header("Location: home.php");
        exit;
    } else {
        $message = "Please provide a title and at least 2 options.";
        $_SESSION['message'] = $message;
        header("Location: create_poll.php");
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Create a Poll</title>
    <style>
        body {
            font-family: Arial;
            padding: 20px;
            background: #f4f4f4;
        }

        form {
            background: white;
            padding: 20px;
            max-width: 600px;
            margin: auto;
            border-radius: 10px;
        }

        h2 {
            text-align: center;
        }

        input[type="text"] {
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
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
                        <a class="nav-link click-scroll" href="home.php" style="color: #333;">Polls</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link click-scroll" href="create_poll.php" style="color: #333;">Create New Poll</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link click-scroll" href="logout.php" style="color: #333;">Logout</a>
                    </li>
                </ul>

                <div class="d-none d-lg-block">
                    <a href="#top" class="navbar-icon bi-person smoothscroll"></a>
                </div>
            </div>
        </div>
    </nav>

    <section class="mt-5">
        <h2>Create a New Poll</h2>
        <p style="color:green"><?= $message ?></p>

        <form method="post">
            <label>Poll Title:</label>
            <input type="text" name="title" required>

            <label>Description (optional):</label>
            <input type="text" name="description">

            <label>Options:</label>
            <input type="text" name="options[]" required placeholder="Option 1">
            <input type="text" name="options[]" required placeholder="Option 2">
            <input type="text" name="options[]" placeholder="Option 3">
            <input type="text" name="options[]" placeholder="Option 4">

            <button type="submit">Create Poll</button>
        </form>
    </section>

</body>

</html>