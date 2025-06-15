<?php include 'db.php'; session_start();
if (!isset($_SESSION["user_id"])) die("Unauthorized");

$uid = $_SESSION["user_id"];
$content = $_POST["content"];
$visibility = $_POST["visibility"];

$stmt = $conn->prepare("INSERT INTO posts (user_id, content, visibility) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $uid, $content, $visibility);
$stmt->execute();
header("Location: dashboard.php");
