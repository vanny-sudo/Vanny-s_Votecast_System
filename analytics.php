<?php
session_start();

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

$user_id = $_SESSION['user_id'];

// Fetch all polls created by the user, including views in the initial query
$stmt = $conn->prepare("SELECT id, title, description, created_at, views FROM polls WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$polls = [];
$total_views = 0;
$total_votes_overall = 0;

while ($row = $result->fetch_assoc()) {
    $polls[] = $row;
}
$stmt->close();

if (count($polls) > 0) {
    foreach ($polls as $key => $poll) {
        $poll_id = $poll['id'];
        // Get total votes
        $stmt = $conn->prepare("SELECT COUNT(*) as total_votes FROM voters WHERE poll_id = ?");
        $stmt->bind_param("i", $poll_id);
        $stmt->execute();
        $stmt->bind_result($total_votes);
        $stmt->fetch();
        $stmt->close();
        $polls[$key]['total_votes'] = $total_votes;
        $total_votes_overall += $total_votes;
        // Get views from poll row
        $views = isset($poll['views']) ? (int)$poll['views'] : 0;
        $polls[$key]['views'] = $views;
        $total_views += $views;
        // Get options and their votes
        $options = [];
        $stmt = $conn->prepare("SELECT option_text, votes FROM options WHERE poll_id = ?");
        $stmt->bind_param("i", $poll_id);
        $stmt->execute();
        $result2 = $stmt->get_result();
        while ($row2 = $result2->fetch_assoc()) {
            $options[] = $row2;
        }
        $stmt->close();
        $polls[$key]['options'] = $options;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Poll Analytics</title>
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
    <h2>My Poll Analytics</h2>
    <div class="analytics" style="margin-bottom: 30px;">
        <strong>Total Views (All Polls):</strong> <?= $total_views ?>
        <span style="margin-left: 20px;"><strong>Total Votes (All Polls):</strong> <?= $total_votes_overall ?></span>
    </div>
    <?php if (empty($polls)): ?>
        <p>You have not created any polls yet.</p>
    <?php endif; ?>
    <?php foreach ($polls as $poll): ?>
        <div class="poll">
            <h3><?= htmlspecialchars($poll['title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars($poll['description'], ENT_QUOTES, 'UTF-8') ?></p>
            <small>Created on: <?= date("F j, Y, g:i a", strtotime($poll['created_at'])) ?></small><br>
            <div class="analytics">
                <strong>Total Votes:</strong> <?= $poll['total_votes'] ?>
            </div>
            <h5 class="mt-3">Options:</h5>
            <?php
            $total_votes = $poll['total_votes'];
            foreach ($poll['options'] as $opt):
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
    <?php endforeach; ?>
</section>
</body>
</html>
