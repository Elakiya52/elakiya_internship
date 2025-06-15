<?php
include 'db.php';
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$uid = $_SESSION["user_id"];
$username = $_SESSION["username"];

if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f2f2f2;
            padding: 40px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h2, h3 {
            text-align: center;
            color: #333;
        }
        .top-links {
            text-align: center;
            margin-bottom: 20px;
        }
        .top-links a {
            margin: 0 10px;
            text-decoration: none;
            color: #007bff;
        }
        .top-links a:hover {
            text-decoration: underline;
        }
        textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        select, button {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-right: 10px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .post {
            background-color: #fafafa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 6px;
        }
        .post small {
            display: block;
            margin-top: 5px;
            color: #666;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Welcome, <?= htmlspecialchars($username) ?> 👋</h2>
    <div class="top-links">
        <a href="change_password.php">Change Password</a> |
        <a href="?logout">Logout</a>
    </div>

    <h3>Write a New Post</h3>
    <form action="save_post.php" method="POST">
        <textarea name="content" required placeholder="Write your post..."></textarea><br>
        <select name="visibility">
            <option value="public">Public</option>
            <option value="private">Private</option>
        </select>
        <button type="submit">Post</button>
    </form>

    <h3>Your Posts</h3>
    <?php
    $stmt = $conn->prepare("SELECT content, visibility, created_at FROM posts WHERE user_id=? OR visibility='public' ORDER BY created_at DESC");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()):
    ?>
        <div class="post">
            <p><?= htmlspecialchars($row['content']) ?></p>
            <small><?= ucfirst($row['visibility']) ?> | <?= $row['created_at'] ?></small>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>
