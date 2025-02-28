<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$friend_id = $_GET['friend_id'];

$mysqli = new mysqli("localhost", "root", "", "faceapp");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Update the status to accepted
$query = "UPDATE friends SET status = 'accepted' WHERE user_id = ? AND friend_id = ? AND status = 'pending'";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ii", $friend_id, $user_id);

if ($stmt->execute()) {
    header("Location: friends.php");
    exit;
} else {
    echo "Error: " . $stmt->error;
}
?>
