<?php
session_start();
include 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION["user_id"];
$username = $_SESSION["username"];

$error = '';
$success = '';

// Fetch last 3 passwords
function getLastPasswords($conn, $userId) {
    $stmt = $conn->prepare("SELECT password FROM password_history WHERE user_id = ? ORDER BY changed_at DESC LIMIT 3");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current = $_POST["current_password"];
    $new = $_POST["new_password"];
    $confirm = $_POST["confirm_password"];

    if ($new !== $confirm) {
        $error = "New password and confirm password do not match.";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($currentHash);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($current, $currentHash)) {
            $error = "Current password is incorrect.";
        } else {
            $history = getLastPasswords($conn, $userId);
            $isReused = false;
            while ($row = $history->fetch_assoc()) {
                if (password_verify($new, $row['password'])) {
                    $isReused = true;
                    break;
                }
            }

            if ($isReused) {
                $error = "You cannot reuse your last 3 passwords.";
            } else {
                $newHash = password_hash($new, PASSWORD_BCRYPT);

                // Update current password
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $newHash, $userId);
                $stmt->execute();

                // Insert into history
                $stmt = $conn->prepare("INSERT INTO password_history (user_id, password) VALUES (?, ?)");
                $stmt->bind_param("is", $userId, $newHash);
                $stmt->execute();

                $success = "Password changed successfully.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f2f2f2;
            padding: 40px;
        }
        .container {
            max-width: 450px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .msg {
            padding: 10px;
            margin-top: 10px;
            border-radius: 6px;
        }
        .error { background-color: #ffdede; color: #d60000; }
        .success { background-color: #ddffdd; color: #007a00; }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: #007bff;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Change Password</h2>

    <?php if ($error): ?>
        <div class="msg error"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="msg success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Current Password</label>
        <input type="password" name="current_password" required>

        <label>New Password</label>
        <input type="password" name="new_password" required>

        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" required>

        <button type="submit">Change Password</button>
    </form>

    <a class="back-link" href="dashboard.php">← Back to Dashboard</a>
</div>

</body>
</html>
