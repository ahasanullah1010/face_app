<?php
session_start();

include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Search functionality
$search_query = "";
if (isset($_POST['search'])) {
    $search_query = $_POST['search'];
    $query = "SELECT * FROM users WHERE full_name LIKE ? AND user_id != ?";
    $stmt = $mysqli->prepare($query);
    $search_param = "%" . $search_query . "%";
    $stmt->bind_param("si", $search_param, $user_id);
} else {
    $query = "SELECT * FROM users WHERE user_id != ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$result = $stmt->get_result();

// Check if the user is already friends or has a pending request
// $friend_query = "SELECT * FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)";
// $friend_stmt = $mysqli->prepare($friend_query);
// $friend_stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Friends - FaceApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            height: 350px; /* Set fixed height for uniformity */
            max-width: 100%; /* Ensure cards do not exceed their container width */
            overflow: hidden; /* Prevent overflow for consistent sizing */
        }
        .card-img-top {
            height: 150px; /* Consistent height for images */
            object-fit: cover; /* Crop or scale images to fit the defined dimensions */
        }
        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between; /* Space between content and button */
        }
        .btn {
            width: 100%; /* Full-width button */
        }
    </style>
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
        <h2>Find Friends</h2>
        
        <!-- Search Form -->
        <form method="POST" action="find_friends.php" class="d-flex mb-3">
            <input type="text" name="search" class="form-control me-2" placeholder="Search for users..." value="<?= $search_query ?>" />
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <!-- Friend Cards -->
        <div class="row">
            <?php while ($user = $result->fetch_assoc()) { 
                $friend_id = $user['user_id'];
$friend_query = "SELECT * FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)";
$friend_stmt = $mysqli->prepare($friend_query);
$friend_stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
                $friend_stmt->execute();
                $friend_result = $friend_stmt->get_result();
                $friend_status = $friend_result->num_rows;
                $friend_data = $friend_result->fetch_assoc();
            ?>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <img src="<?= $user['profile_picture'] ? $user['profile_picture'] : 'default-profile.jpg' ?>" class="card-img-top" alt="Profile Picture">
                        <div class="card-body">
                            <h5 class="card-title"><?= $user['full_name'] ?></h5>
                            <p class="card-text"><?= $user['email'] ?></p>
                            <form action="send_friend_request.php" method="POST">
                                <input type="hidden" name="friend_id" value="<?= $user['user_id'] ?>" />
                                <?php if ($friend_status > 0 && $friend_data['status'] == "accepted") { ?>
                                    <button class="btn btn-secondary" disabled>Already Friends</button>
                                <?php } else if ($friend_status > 0 && $friend_data['status'] == "pending") { ?>
                                    <button class="btn btn-secondary" disabled>Pending</button>
                                <?php } else { ?>
                                    <button type="submit" class="btn btn-success">Add Friend</button>
                                <?php } ?>
                            </form>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
