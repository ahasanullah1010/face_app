<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$mysqli = new mysqli("localhost", "root", "", "faceapp");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Fetch pending friend requests
$query = "SELECT u.user_id, u.full_name, u.profile_picture FROM friends f JOIN users u ON f.user_id = u.user_id WHERE f.friend_id = ? AND f.status = 'pending'";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$requests_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friend Requests - FaceApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="timeline.php">FaceApp</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="timeline.php">Timeline</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Friend Requests</h2>
        <div class="row">
            <?php while ($request = $requests_result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card">
                        <img src="<?= $request['profile_picture'] ?: 'default_profile.jpg' ?>" alt="Profile Picture" class="card-img-top rounded-circle" width="100" height="100">
                        <div class="card-body">
                            <h5 class="card-title"><?= $request['full_name'] ?></h5>
                            <a href="accept_friend_request.php?friend_id=<?= $request['user_id'] ?>" class="btn btn-success">Accept</a>
                            <a href="reject_friend_request.php?friend_id=<?= $request['user_id'] ?>" class="btn btn-danger">Reject</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; if($requests_result->num_rows<=0) echo "You don't have any friend request!" ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
