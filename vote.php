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

$poll_id = isset($_GET['poll_id']) ? intval($_GET['poll_id']) : 0;
$voter_ip = $_SESSION['user_id'] ?? $_SERVER['REMOTE_ADDR'];

$creator_id = null;
$stmt = $conn->prepare("SELECT user_id FROM polls WHERE id = ?");
$stmt->bind_param("i", $poll_id);
$stmt->execute();
$stmt->bind_result($creator_id);
$stmt->fetch();
$stmt->close();

if ($_SESSION['user_id'] == $creator_id) {
    $message = "You cannot vote on your own poll.";
    $already_voted = true;
    $_SESSION['message'] = $message;
    header("Location: home.php");
    exit;
}

$already_voted = false;
$stmt = $conn->prepare("SELECT id FROM voters WHERE poll_id = ? AND user_id = ?");
$stmt->bind_param("is", $poll_id, $voter_ip);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $already_voted = true;
    $message = "You have already voted on this poll.";
    $_SESSION['message'] = $message;
    header("Location: home.php");
    exit;
}

$stmt->close();

// Handle vote submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$already_voted) {
    $option_id = intval($_POST['option']);

    // Increment vote count
    $stmt = $conn->prepare("UPDATE options SET votes = votes + 1 WHERE id = ? AND poll_id = ?");
    $stmt->bind_param("ii", $option_id, $poll_id);
    $stmt->execute();
    $stmt->close();

    // Record voter IP
    $stmt = $conn->prepare("INSERT INTO voters (poll_id, user_id) VALUES (?, ?)");
    $stmt->bind_param("is", $poll_id, $voter_ip);
    $stmt->execute();
    $stmt->close();

    $already_voted = true;
    $message = "Your vote has been recorded!";
    $_SESSION['message'] = $message;
    header("Location: vote.php?poll_id=$poll_id");
}

// Increment poll views
if ($poll_id) {
    $stmt = $conn->prepare("UPDATE polls SET views = views + 1 WHERE id = ?");
    $stmt->bind_param("i", $poll_id);
    $stmt->execute();
    $stmt->close();
}

// Get poll and options
$stmt = $conn->prepare("SELECT title, description FROM polls WHERE id = ?");
$stmt->bind_param("i", $poll_id);
$stmt->execute();
$stmt->bind_result($title, $description);
$stmt->fetch();
$stmt->close();

$options = [];
$result = $conn->query("SELECT id, option_text, votes FROM options WHERE poll_id = $poll_id");
while ($row = $result->fetch_assoc()) {
    $options[] = $row;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Vote on Poll</title>
    <style>
        body {
            font-family: Arial;
            padding: 20px;
            background: #f9f9f9;
        }

        .poll-box {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            max-width: 600px;
            margin: auto;
        }

        .option {
            margin-bottom: 10px;
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
        <div class="poll-box">
            <h2><?= htmlspecialchars($title) ?></h2>
            <p><?= htmlspecialchars($description) ?></p>

            <?php if ($already_voted): ?>
                <p style="color:green"><?= $message ?: "You have already voted in this poll." ?></p>
                <h3>Results:</h3>
                <?php
                $total_votes = array_sum(array_column($options, 'votes'));
                foreach ($options as $opt):
                    $percent = $total_votes > 0 ? round(($opt['votes'] / $total_votes) * 100, 2) : 0;
                ?>
                    <p><?= htmlspecialchars($opt['option_text']) ?>: <?= $percent ?>% (<?= $opt['votes'] ?> votes)</p>
                <?php endforeach; ?>
            <?php else: ?>
                <form method="post">
                    <?php foreach ($options as $opt): ?>
                        <div class="option">
                            <label>
                                <input type="radio" name="option" value="<?= $opt['id'] ?>" required>
                                <?= htmlspecialchars($opt['option_text']) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit">Submit Vote</button>
                </form>
            <?php endif; ?>
        </div>
    </section>

</body>

</html>