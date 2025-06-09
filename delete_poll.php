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
    die("You are not authorized to delete this poll.");
}

// Delete voters associated with the poll
$stmt = $conn->prepare("DELETE FROM voters WHERE poll_id = ?");
$stmt->bind_param("i", $poll_id);
$stmt->execute();
$stmt->close();

// Delete options first (to maintain referential integrity)
$stmt = $conn->prepare("DELETE FROM options WHERE poll_id = ?");
$stmt->bind_param("i", $poll_id);
$stmt->execute();
$stmt->close();

// Delete the poll
$stmt = $conn->prepare("DELETE FROM polls WHERE id = ?");
$stmt->bind_param("i", $poll_id);
$stmt->execute();
$stmt->close();

$conn->close();

header("Location: home.php");
exit;
