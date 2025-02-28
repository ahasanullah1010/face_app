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

// Fetch friends
$query = "SELECT users.user_id, users.full_name, users.email, users.profile_picture 
          FROM friends 
          JOIN users ON friends.friend_id = users.user_id 
          WHERE friends.user_id = ? AND friends.status = 'accepted'
          UNION
          SELECT users.user_id, users.full_name, users.email, users.profile_picture 
          FROM friends 
          JOIN users ON friends.user_id = users.user_id 
          WHERE friends.friend_id = ? AND friends.status = 'accepted'";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends - FaceApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            height: 300px;
            max-width: 100%;
            overflow: hidden;
        }
        .card-img-top {
            height: 150px;
            object-fit: cover;
        }
        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
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
        <h2>Your Friends</h2>

        <div class="row">
            <?php while ($friend = $result->fetch_assoc()) { ?>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <img src="<?= $friend['profile_picture'] ? $friend['profile_picture'] : 'default-profile.jpg' ?>" class="card-img-top" alt="Profile Picture">
                        <div class="card-body">
                            <h5 class="card-title"><?= $friend['full_name'] ?></h5>
                            <p class="card-text"><?= $friend['email'] ?></p>
                            <a href="profile.php?user_id=<?= $friend['user_id'] ?>" class="btn btn-primary">View Profile</a>
                        </div>
                    </div>
                </div>
            <?php }  ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
