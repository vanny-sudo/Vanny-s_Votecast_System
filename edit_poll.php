<?php
session_start();

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

if (!isset($_GET['poll_id'])) {
    die("Poll ID is required.");
}
$poll_id = intval($_GET['poll_id']);

// Fetch poll to check ownership
$stmt = $conn->prepare("SELECT * FROM polls WHERE id = ?");
$stmt->bind_param("i", $poll_id);
$stmt->execute();
$result = $stmt->get_result();
$poll = $result->fetch_assoc();
$stmt->close();

if (!$poll || $poll['user_id'] != $_SESSION['user_id']) {
    die("You are not authorized to edit this poll.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    if ($title === '') {
        $error = "Title cannot be empty.";
    } else {
        $stmt = $conn->prepare("UPDATE polls SET title = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssi", $title, $description, $poll_id);
        $stmt->execute();
        $stmt->close();
        header("Location: home.php");
        exit;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Edit Poll</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }

        .form-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            max-width: 400px;
            margin: 40px auto;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        input,
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            background: #007BFF;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
    <link href="bootstrap.min.css" rel="stylesheet">
    <link href="templatemo-topic-listing.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
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
                        <a class="nav-link click-scroll active" href="home.php" style="color: #333; font-weight: bold;">Polls</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link click-scroll" href="create_poll.php" style="color: #333;">Create New Poll</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link click-scroll" href="logout.php" style="color: #333;">Logout</a>
                    </li>
                </ul>

                <div class="d-none d-lg-block">
                    <span class="navbar-text" style="color:#555; font-weight:600;">
                        <?php if(isset($_SESSION['username'])): ?>
                            👤 <?= htmlspecialchars($_SESSION['username']) ?>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>
    </nav>

    <section class="mt-5">
        <div class="form-container">
            <h5>Edit Poll</h5>
            <?php if (!empty($error)) echo '<div class="error">' . htmlspecialchars($error) . '</div>'; ?>
            <form method="post">
                <label>Title:</label>
                <input type="text" name="title" value="<?= htmlspecialchars($poll['title'], ENT_QUOTES, 'UTF-8') ?>" required>
                <label>Description:</label>
                <textarea name="description" rows="4"><?= htmlspecialchars($poll['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                <button type="submit">Update Poll</button>
            </form>
            <a href="home.php" class="btn btn-link mt-2">&larr; Back to Polls</a>
        </div>
    </section>
</body>

</html>