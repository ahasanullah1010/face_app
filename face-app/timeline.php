
<?php
session_start();

include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = $_POST['content'];
    $media_url = NULL;

    if (!empty($_FILES['media']['name'])) {
        $media_name = time() . "_" . $_FILES['media']['name'];
        $media_path = "uploads/" . $media_name;
        if (move_uploaded_file($_FILES['media']['tmp_name'], $media_path)) {
            $media_url = $media_path;
        }
    }

    $query = "INSERT INTO posts (user_id, content, media_url) VALUES (?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('iss', $user_id, $content, $media_url);
    $stmt->execute();
    header("Location: timeline.php");
    exit;
}

// Handle Like Toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_post_id'])) {
    $post_id = $_POST['like_post_id'];

    // Check if already liked
    $like_query = "SELECT * FROM likes WHERE user_id = ? AND post_id = ?";
    $stmt = $mysqli->prepare($like_query);
    $stmt->bind_param('ii', $user_id, $post_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Remove like
        $unlike_query = "DELETE FROM likes WHERE user_id = ? AND post_id = ?";
        $stmt = $mysqli->prepare($unlike_query);
        $stmt->bind_param('ii', $user_id, $post_id);
        $stmt->execute();
    } else {
        // Add like
        $like_query = "INSERT INTO likes (user_id, post_id) VALUES (?, ?)";
        $stmt = $mysqli->prepare($like_query);
        $stmt->bind_param('ii', $user_id, $post_id);
        $stmt->execute();
    }
    header("Location: timeline.php");
    exit;
}

// Handle Comment Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_post_id'], $_POST['comment_text'])) {
    $post_id = $_POST['comment_post_id'];
    $comment_text = $_POST['comment_text'];

    $comment_query = "INSERT INTO comments (user_id, post_id, content) VALUES (?, ?, ?)";
    $stmt = $mysqli->prepare($comment_query);
    $stmt->bind_param('iis', $user_id, $post_id, $comment_text);
    $stmt->execute();

   header("Location: timeline.php");
   exit;
}

// Fetch Posts
$query = "
    SELECT p.*, u.full_name, u.profile_picture 
    FROM posts p 
    JOIN users u ON p.user_id = u.user_id 
    WHERE p.user_id = $user_id 
       OR p.user_id IN (
           SELECT friend_id 
           FROM friends 
           WHERE user_id = $user_id AND status = 'accepted'
           UNION 
           SELECT user_id 
           FROM friends 
           WHERE friend_id = $user_id AND status = 'accepted'
       ) 
    ORDER BY p.created_at DESC";

$result = $mysqli->query($query);

$sql = "SELECT * FROM users where user_id = $user_id";
$r = $mysqli->query($sql);
$own = $r->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline - FaceApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            height: 100vh;
            color: #333;
        }
        .navbar {
            background-color: #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            color: #333;
        }
        .post-card {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .post-btn {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
        }
        .post-btn:hover {
            background-color: #0056b3;
        }
        .like-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            color:rgb(0, 255, 34);
        }
        .like-btn.active {
            font-weight: bold;
            color: #0056b3;
        }
        .comment-section {
            margin-top: 15px;
        }
        .comment {
            margin-bottom: 10px;
        }
        .like-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #007bff;
            cursor: pointer;
            outline: none;
        }

        .like-btn.active {
            background-color: #0056b3;
            border-radius: 100%;
            font-weight: bold;
            color:rgb(232, 240, 232); /* Darker shade for active state */
            text-decoration: bold;
        }

    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <img style="width: 50px;" src="faceapp.jpg" alt="">
        <a class="navbar-brand" href="timeline.php">FaceApp</a>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="profile.php?user_id=<?= $user_id ?>">Profile</a></li>
            <li class="nav-item"><a class="nav-link" href="friends.php">Friends</a></li>
            <li class="nav-item"><a class="nav-link" href="view_friend_requests.php">Find Requests</a></li>
            <li class="nav-item"><a class="nav-link" href="find_friends.php">Find Friends</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            <li class="nav-item"><a class="nav-link" href="profile.php?user_id=<?= $user_id ?>">
                <img src="<?= $own['profile_picture'] ?: 'default_profile.jpg' ?>" alt="Profile Picture" class="rounded-circle" width="50" height="50">
            </a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <h2 class="text-center text-dark mb-4">Your Timeline</h2>

            <!-- Create Post Form -->
            <div class="post-card">
                <form action="timeline.php" method="POST" enctype="multipart/form-data">
                    <textarea class="form-control" name="content" rows="3" placeholder="What's on your mind?" required></textarea>
                    <div class="mt-3">
                        <input type="file" class="form-control" name="media" accept="image/*, video/*">
                    </div>
                    <button type="submit" class="btn post-btn mt-3 w-100">Post</button>
                </form>
            </div>

            <!-- Display Posts -->
            <?php while ($post = $result->fetch_assoc()): ?>
                <?php
                // Fetch Likes Count
                $likes_query = "SELECT COUNT(*) as count FROM likes WHERE post_id = ?";
                $stmt = $mysqli->prepare($likes_query);
                $stmt->bind_param('i', $post['post_id']);
                $stmt->execute();
                $likes_result = $stmt->get_result()->fetch_assoc();
                $likes_count = $likes_result['count'];

                // Check if User Liked Post
                $liked_query = "SELECT * FROM likes WHERE user_id = ? AND post_id = ?";
                $stmt = $mysqli->prepare($liked_query);
                $stmt->bind_param('ii', $user_id, $post['post_id']);
                $stmt->execute();
                $is_liked = $stmt->get_result()->num_rows > 0;

                // Fetch Comments
                $comments_query = "SELECT c.*, u.full_name, u.profile_picture FROM comments c JOIN users u ON c.user_id = u.user_id WHERE c.post_id = ? ORDER BY c.created_at ASC";
                $stmt = $mysqli->prepare($comments_query);
                $stmt->bind_param('i', $post['post_id']);
                $stmt->execute();
                $comments_result = $stmt->get_result();
                $comments_count = $comments_result->num_rows;
                ?>

                <div class="post-card">
                    <div class="d-flex align-items-center mb-3">
                        <img src="<?= $post['profile_picture'] ?: 'default_profile.jpg' ?>" alt="Profile Picture" class="rounded-circle" width="50" height="50">
                        <div class="ms
                        <div class="ms-3">
                            <h5 class="mb-0"><?= htmlspecialchars($post['full_name']) ?></h5>
                            <small class="text-muted"><?= date("F j, Y, g:i a", strtotime($post['created_at'])) ?></small>
                        </div>
                    </div>
                    <p><?= htmlspecialchars($post['content']) ?></p>
                    <?php if ($post['media_url']): ?>
                        <?php
                        $file_extension = pathinfo($post['media_url'], PATHINFO_EXTENSION);
                        if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                            <img src="<?= $post['media_url'] ?>" alt="Post Media" class="img-fluid mt-3 rounded">
                        <?php elseif (in_array($file_extension, ['mp4', 'webm', 'ogg'])): ?>
                            <video class="img-fluid mt-3 rounded" controls>
                                <source src="<?= $post['media_url'] ?>" type="video/<?= $file_extension ?>">
                                Your browser does not support the video tag.
                            </video>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Like and Comment Buttons -->
                    <div class="mt-3">
                        <form action="timeline.php" method="POST" class="d-inline">
                            <input type="hidden" name="like_post_id" value="<?= $post['post_id'] ?>">
                            <button type="submit" class="like-btn <?= $is_liked ? 'active' : '' ?>">
                                👍 <?= $likes_count ?>
                            </button>
                        </form>
                        <button onclick="" class="like-btn" type="button" data-bs-toggle="collapse" data-bs-target="#comments-<?= $post['post_id'] ?>">
                            💬 <?= $comments_count ?>
                        </button>
                    </div>

                    <!-- Comments Section -->
                    <div class="collapse comment-section mt-3" id="comments-<?= $post['post_id'] ?>">
                        <?php while ($comment = $comments_result->fetch_assoc()): ?>
                            <div class="comment">
                            <img src="<?= $comment['profile_picture'] ?: 'default_profile.jpg' ?>" alt="Profile Picture" class="rounded-circle" width="25" height="25">
                                <strong><?= htmlspecialchars($comment['full_name']) ?>:</strong>
                                <p><?= htmlspecialchars($comment['content']) ?></p>
                                <small class="text-muted"><?= date("F j, Y, g:i a", strtotime($comment['created_at'])) ?></small>
                            </div>
                        <?php endwhile; ?>

                        <!-- Add Comment Form -->
                        <form action="timeline.php" method="POST" class="mt-3">
                            <input type="hidden" name="comment_post_id" value="<?= $post['post_id'] ?>">
                            <textarea class="form-control" name="comment_text" rows="2" placeholder="Write a comment..." required></textarea>
                            <button type="submit" class="btn btn-primary mt-2">Post Comment</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const postId = btn.dataset.postId;
            fetch('handle_like.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `post_id=${postId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    if (data.action === 'liked') {
                        btn.classList.add('btn-primary');
                        btn.classList.remove('btn-outline-primary');
                    } else {
                        btn.classList.remove('btn-primary');
                        btn.classList.add('btn-outline-primary');
                    }
                    btn.nextElementSibling.textContent = parseInt(btn.nextElementSibling.textContent) + (data.action === 'liked' ? 1 : -1);
                }
            });
        });
    });

    document.querySelectorAll('.comment-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const postId = btn.dataset.postId;
            const commentBox = document.querySelector(`#comments-${postId}`);
            fetch(`handle_comment.php?post_id=${postId}`)
                .then(response => response.json())
                .then(comments => {
                    let commentsHTML = '';
                    comments.forEach(comment => {
                        commentsHTML += `<p><strong>${comment.full_name}:</strong> ${comment.content}</p>`;
                    });
                    commentBox.innerHTML = commentsHTML;
                });

            commentBox.style.display = commentBox.style.display === 'none' ? 'block' : 'none';
        });
    });

    document.querySelectorAll('.add-comment-form').forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const postId = form.dataset.postId;
            const content = form.querySelector('textarea').value;

            fetch('handle_comment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `post_id=${postId}&content=${content}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    form.querySelector('textarea').value = '';
                    form.previousElementSibling.click(); // Reload comments
                }
            });
        });
    });
</script>

<script>
    $(document).on('click', '.like-btn', function () {
        let postId = $(this).closest('.like-form').data('post-id'); // Get the post ID
        let likeButton = $(this); // Reference to the clicked button

        $.ajax({
            url: 'handle_like.php', // Backend script for handling likes
            type: 'POST',
            data: { post_id: postId },
            success: function (response) {
                let data = JSON.parse(response); // Parse JSON response
                if (data.status === 'liked') {
                    likeButton.addClass('active'); // Add active class
                } else if (data.status === 'unliked') {
                    likeButton.removeClass('active'); // Remove active class
                }
                // Update like count dynamically
                likeButton.find('.like-count').text(data.likes_count);
            },
            error: function () {
                alert('Error handling like. Please try again.');
            }
        });
    });
</script>


</body>
</html>








