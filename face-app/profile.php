<?php
session_start();

include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$uid = $_GET['user_id'];
// Fetch user information
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch user posts
$post_query = "SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC";
$post_stmt = $mysqli->prepare($post_query);
$post_stmt->bind_param("i", $uid);
$post_stmt->execute();
$posts = $post_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - FaceApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5; /* Light gray background */
            color: #333;
        }
        .navbar {
            background-color: #ffffff; /* White navbar */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow */
        }
        .navbar-brand {
            color: #333;
        }
        .container {
            margin-top: 60px;
        }
        .profile-card {
            background-color: #ffffff; /* White card */
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
       
        .profile-card h2 {
            margin-top: 20px;
        }
        .profile-card .btn {
            background-color: #007bff; /* Blue button */
            color: white;
        }
        .profile-card .btn:hover {
            background-color: #0056b3;
        }
        .post-card {
            background-color: #ffffff; /* White posts */
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="timeline.php">FaceApp</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="timeline.php">Timeline</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Profile Content -->
    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <!-- Profile Card -->
                 <?php if($user_id == $uid){ ?>
                <div class="profile-card text-center">
                <img src="<?= $user['profile_picture'] ?: 'default_profile.jpg' ?>" alt="Profile Picture" class="rounded-circle" width="150" height="150">
                    <h2><?= $user['full_name'] ?></h2>
                    <p><strong>Email:</strong> <?= $user['email'] ?></p>
                    <p><strong>Birthdate:</strong> <?= date("F j, Y", strtotime($user['birthdate'])) ?></p>
                    <a href="edit_profile.php" class="btn">Edit Profile</a>
                </div>
                <?php } else { ?>
                    <div class="profile-card text-center">
                    <img src="<?= $user['profile_picture'] ?: 'default_profile.jpg' ?>" alt="Profile Picture" class="rounded-circle" width="150" height="150">
                    <h2><?= $user['full_name'] ?></h2>
                    <p><strong>Email:</strong> <?= $user['email'] ?></p>
                    <p><strong>Birthdate:</strong> <?= date("F j, Y", strtotime($user['birthdate'])) ?></p>
                    
                </div>
                <?php }  ?>

                <!-- User Posts -->
                <h3 class="mt-5">Your Posts</h3>
                <?php while ($post = $posts->fetch_assoc()): ?>
                    <div class="post-card">
                        <p><strong><?= $user['full_name'] ?></strong> | <?= date("F j, Y, g:i a", strtotime($post['created_at'])) ?></p>
                        <p><?= $post['content'] ?></p>
                        <?php if ($post['media_url']): ?>
                            <img src="<?= $post['media_url'] ?>" alt="Post Media" class="img-fluid mt-3 rounded">
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
