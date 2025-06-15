<?php
session_start();
include 'db.php';


$error = '';
$success = '';

// 🔓 Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// 🧠 Handle Form Submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 🔐 LOGIN
    if (isset($_POST["login"])) {
        $username = trim($_POST["login_username"]);
        $password = $_POST["login_password"];

        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($uid, $hashedPassword);
            $stmt->fetch();

            if (password_verify($password, $hashedPassword)) {
                $_SESSION["user_id"] = $uid;
                $_SESSION["username"] = $username;
                header("Location: dashboard.php"); // redirect to self
                exit;
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "User not found.";
        }
    }

    // 📝 SIGNUP
    elseif (isset($_POST["signup"])) {
        $username = trim($_POST["signup_username"]);
        $email = trim($_POST["signup_email"]);
        $password = $_POST["signup_password"];

        // Check if user already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed);
            if ($stmt->execute()) {
                $success = "Signup successful! Please log in.";
            } else {
                $error = "Signup failed. Try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login / Signup</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php if (isset($_SESSION["user_id"])): ?>
    <h2>Welcome, <?= htmlspecialchars($_SESSION["username"]) ?>!</h2>
    <p><a href="?logout=1">Logout</a></p>
<?php else: ?>
    <h2 style="text-align: center;">Login / Signup</h2>

    <?php if ($error): ?>
        <div class="message error"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="message success"><?= $success ?></div>
    <?php endif; ?>

    <div class="container">
        
        <!-- 🔐 Login Form -->
        <form method="POST">
            <h3>Login</h3>
            <input type="text" name="login_username" required placeholder="Username">
            <input type="password" name="login_password" required placeholder="Password">
            <button name="login">Login</button>
        </form>

        <!-- 📝 Signup Form -->
        <form method="POST">
            <h3>Signup</h3>
            <input type="text" name="signup_username" required placeholder="Username">
            <input type="email" name="signup_email" required placeholder="Email">
            <input type="password" name="signup_password" required placeholder="Password">
            <button name="signup">Signup</button>
        </form>

    </div>
<?php endif; ?>

</body>
</html>
