<?php
session_start();
require_once 'db_connect.php';

$error = "";

if (isset($_REQUEST["sub"])) {
    $ver_username = trim($_REQUEST["username_php"]);
    $ver_pswd     = $_REQUEST["pswd_php"];

    $q = $con->prepare("SELECT * FROM `users` WHERE username=?");
    $q->bind_param("s", $ver_username);
    $q->execute();
    $p = $q->get_result();

    if ($p && mysqli_num_rows($p)) {
        $array = mysqli_fetch_array($p);
        if (password_verify($ver_pswd, $array['password'])) {
            $_SESSION['userid'] = $array['user_id'];
            $_SESSION['role']   = $array['role'];
            $_SESSION['user']   = $array['name'];
            $_SESSION['pfp']    = $array['pfp'];   // ← added
            header("location: feed.php");
            exit;
        } else {
            $error = "Username or password mismatch.";
        }
    } else {
        $error = "Username or password mismatch.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Civic Complaints</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="feed.php">Civic Complaints</a>
</header>

<div class="container">
    <h1>Login</h1>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="login.php" method="post">
        <input type="text" name="username_php" placeholder="username" required/>
        <input type="password" name="pswd_php" placeholder="password" required/>
        <input type="submit" name="sub" value="Login"/>
    </form>
    <a href="signup.php"><button type="button">Sign Up</button></a>
</div>
</body>
</html>
