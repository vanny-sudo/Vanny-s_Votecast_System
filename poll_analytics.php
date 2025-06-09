<?php
session_start();

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

$poll_id = isset($_GET['poll_id']) ? intval($_GET['poll_id']) : 0;
$user_id = $_SESSION['user_id'];

// Fetch poll and check ownership
$stmt = $conn->prepare("SELECT id, title, description, created_at, user_id, views FROM polls WHERE id = ?");
$stmt->bind_param("i", $poll_id);
$stmt->execute();
$stmt->bind_result($id, $title, $description, $created_at, $poll_user_id, $views);
$stmt->fetch();
$stmt->close();

if (!$id || $poll_user_id != $user_id) {
    die("You are not authorized to view analytics for this poll.");
}

// Get total votes
$stmt = $conn->prepare("SELECT COUNT(*) FROM voters WHERE poll_id = ?");
$stmt->bind_param("i", $poll_id);
$stmt->execute();
$stmt->bind_result($total_votes);
$stmt->fetch();
$stmt->close();

// Get options and their votes
$options = [];
$stmt = $conn->prepare("SELECT option_text, votes FROM options WHERE poll_id = ?");
$stmt->bind_param("i", $poll_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $options[] = $row;
}
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Poll Analytics</title>
    <link href="bootstrap.min.css" rel="stylesheet">
    <link href="templatemo-topic-listing.css" rel="stylesheet">
    <style>
        body { background: #f4f4f4; font-family: Arial, sans-serif; padding: 20px; }
        .poll { background: white; padding: 20px; margin-bottom: 15px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .poll h3 { margin: 0; }
        .poll p { margin: 5px 0; color: #666; }
        .analytics { color: #007BFF; margin: 10px 0; }
        .option-bar { background: #e0e0e0; border-radius: 5px; overflow: hidden; height: 20px; margin-top: 5px; }
        .option-bar-inner { background: #007BFF; height: 100%; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="home.php">
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
                <span class="navbar-text" style="color:#555; font-weight:600;">
                    <?php if(isset($_SESSION['username'])): ?>
                        👤 <?= htmlspecialchars($_SESSION['username']) ?>
                    <?php endif; ?>
                </span>
            </div>
        </div>
    </div>
</nav>
<section class="container mt-5">
    <div class="poll">
        <h3><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h3>
        <p><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>
        <small>Created on: <?= date("F j, Y, g:i a", strtotime($created_at)) ?></small><br>
        <div class="analytics">
            <strong>Total Votes:</strong> <?= $total_votes ?>
            <span style="margin-left: 15px;">Views: <?= $views ?></span>
        </div>
        <h5 class="mt-3">Options:</h5>
        <?php
        foreach ($options as $opt):
            $percent = $total_votes > 0 ? round(($opt['votes'] / $total_votes) * 100, 2) : 0;
        ?>
            <div style="margin-bottom: 10px;">
                <strong><?= htmlspecialchars($opt['option_text'], ENT_QUOTES, 'UTF-8') ?>:</strong> <?= $percent ?>% (<?= $opt['votes'] ?> votes)
                <div class="option-bar">
                    <div class="option-bar-inner" style="width: <?= $percent ?>%;"></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <a href="home.php" class="btn btn-link mt-2">&larr; Back to Polls</a>
</section>
</body>
</html>
