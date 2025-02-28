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

// Fetch current user data
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();

// Update the profile
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $birthdate = $_POST['birthdate'];
    $profile_picture = $_FILES['profile_picture']['name'];

    // Validate inputs (you can add more validation here)
    if (empty($full_name) || empty($email)) {
        $error_message = "Full Name and Email are required.";
    } else {
        // Handle profile picture upload
        if (!empty($profile_picture)) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($profile_picture);
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                $profile_picture = $target_file;
            } else {
                $profile_picture = $user_data['profile_picture']; // Keep the old picture if upload fails
            }
        } else {
            $profile_picture = $user_data['profile_picture']; // Keep the old picture if no new one is uploaded
        }

        // Update the database
        $update_query = "UPDATE users SET full_name = ?, email = ?, birthdate = ?, profile_picture = ? WHERE user_id = ?";
        $stmt = $mysqli->prepare($update_query);
        $stmt->bind_param("ssssi", $full_name, $email, $birthdate, $profile_picture, $user_id);
        if ($stmt->execute()) {
            $success_message = "Profile updated successfully!";
        } else {
            $error_message = "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - FaceApp</title>
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
        <h2>Edit Profile</h2>
        <?php if (isset($error_message)) { ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php } ?>
        <?php if (isset($success_message)) { ?>
            <div class="alert alert-success"><?= $success_message ?></div>
        <?php } ?>

        <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="full_name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="full_name" name="full_name" value="<?= $user_data['full_name'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= $user_data['email'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="birthdate" class="form-label">Birthdate</label>
                <input type="date" class="form-control" id="birthdate" name="birthdate" value="<?= $user_data['birthdate'] ?>">
            </div>
            <div class="mb-3">
                <label for="profile_picture" class="form-label">Profile Picture</label>
                <input type="file" class="form-control" id="profile_picture" name="profile_picture">
                <small class="text-muted">Leave blank to keep your current profile picture.</small>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
