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

$polls = [];
$stmt = $conn->prepare("SELECT id, title, description, created_at, user_id FROM polls ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $polls[] = $row;
}

$stmt->close();

foreach ($polls as $key => $poll) {
    $poll_id = $poll['id'];
    $options = [];
    $stmt = $conn->prepare("SELECT option_text, votes FROM options WHERE poll_id = ?");
    $stmt->bind_param("i", $poll_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $options[] = $row;
    }
    $stmt->close();

    // Get total votes and views for analytics
    $stmt = $conn->prepare("SELECT COUNT(*) as total_votes FROM voters WHERE poll_id = ?");
    $stmt->bind_param("i", $poll_id);
    $stmt->execute();
    $stmt->bind_result($total_votes);
    $stmt->fetch();
    $stmt->close();

    // Get views from polls table
    $stmt = $conn->prepare("SELECT views FROM polls WHERE id = ?");
    $stmt->bind_param("i", $poll_id);
    $stmt->execute();
    $stmt->bind_result($views);
    $stmt->fetch();
    $stmt->close();

    $polls[$key]['options'] = $options;
    $polls[$key]['total_votes'] = $total_votes;
    $polls[$key]['views'] = $views;
}

$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Available Polls</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }

        .poll {
            background: white;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .poll h3 {
            margin: 0;
        }

        .poll p {
            margin: 5px 0;
            color: #666;
        }

        .poll a {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 15px;
            background: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .poll a:hover {
            background: #0056b3;
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
                        <a class="nav-link click-scroll" href="analytics.php" style="color: #333;">My Analytics</a>
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

    <section class="container mt-5">
        <?php if (!empty($message)): ?>
            <div style="background: #ffeeba; color: #856404; padding: 10px 20px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #ffeeba;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <a href="create_poll.php" style="display: inline-block; margin-bottom: 20px; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;">Create New Poll</a>

        <?php foreach ($polls as $poll): ?>
            <div class="poll border">
                <h3><?= htmlspecialchars($poll['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars($poll['description'], ENT_QUOTES, 'UTF-8') ?></p>
                <small>Created on: <?= date("F j, Y, g:i a", strtotime($poll['created_at'])) ?></small><br>

                <?php if (isset($_SESSION['user_id']) && $poll['user_id'] == $_SESSION['user_id']): ?>
                    <div style="display: flex; gap: 10px; margin: 10px 0;">
                        <a href="poll_analytics.php?poll_id=<?= urlencode($poll['id']) ?>" class="btn btn-sm btn-info" style="color: white; background: indigo">View Analytics</a>
                        <a href="edit_poll.php?poll_id=<?= urlencode($poll['id']) ?>" class="btn btn-sm btn-warning" style="color: white; background: blue">Edit</a>
                        <a href="delete_poll.php?poll_id=<?= urlencode($poll['id']) ?>" class="btn btn-sm btn-danger" style="color: white; background: red" onclick="return confirm('Are you sure you want to delete this poll? This action cannot be undone.');">Delete</a>
                    </div>
                <?php endif; ?>
                <hr>

                <h5 class="mt-3">Options:</h5>
                <?php
                $total_votes = array_sum(array_column($poll['options'], 'votes'));
                foreach ($poll['options'] as $opt):
                    $percent = $total_votes > 0 ? round(($opt['votes'] / $total_votes) * 100, 2) : 0;
                ?>
                    <div style="margin-bottom: 10px;">
                        <strong><?= htmlspecialchars($opt['option_text'], ENT_QUOTES, 'UTF-8') ?>:</strong> <?= $percent ?>% (<?= $opt['votes'] ?> votes)
                        <div style="background: #e0e0e0; border-radius: 5px; overflow: hidden; height: 20px; margin-top: 5px;">
                            <div style="background: #007BFF; width: <?= $percent ?>%; height: 100%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <a href="vote.php?poll_id=<?= urlencode($poll['id']) ?>" style="background: #007BFF; color: white; text-decoration: none; padding: 8px 15px; border-radius: 5px;">Vote Now</a>
            </div>
        <?php endforeach; ?>
    </section>
</body>

</html>