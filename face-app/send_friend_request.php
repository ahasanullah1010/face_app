<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$friend_id = $_POST['friend_id'];

$mysqli = new mysqli("localhost", "root", "", "faceapp");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check if the friend request already exists
$query = "SELECT * FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // No existing request, so send a new request
    $query = "INSERT INTO friends (user_id, friend_id, status) VALUES (?, ?, 'pending')";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $user_id, $friend_id);

    if ($stmt->execute()) {
        header("Location: find_friends.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
} else {
    echo "Friend request already sent or you are already friends.";
}
?>
