<?php
session_start();
require_once 'db_connect.php';

$error = "";

if (isset($_REQUEST['sub'])) {
    $name     = ucfirst(trim($_REQUEST['name']));
    $username = trim($_REQUEST['username_php']);
    $pswd     = password_hash($_REQUEST['pswd_php'], PASSWORD_BCRYPT);
    $role     = 'citizen';
    $photoname = $name[0] . ".png";

    $check = $con->prepare("SELECT user_id FROM `users` WHERE username=?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "That username is already taken.";
    } else {
        $q = $con->prepare("INSERT INTO `users` (`username`, `password`, `name`, `role`, `pfp`) VALUES (?,?,?,?,?)");
        $q->bind_param("sssss", $username, $pswd, $name, $role, $photoname);

        if (!$q->execute()) {
            $error = "Error creating account: " . $con->error;
        } else {
            $_SESSION['userid'] = $con->insert_id;
            $_SESSION['role']   = $role;
            $_SESSION['user']   = $name;
            header("location: feed.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Civic Complaints</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="feed.php">Civic Complaints</a>
</header>

<div class="container">
    <h1>Sign Up</h1>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="signup.php" method="post">
        <input type="text" name="name" placeholder="your name" pattern="[a-zA-Z]+" required/>
        <input type="text" name="username_php" placeholder="username" pattern="[a-zA-Z0-9]+" required/>
        <input type="password" name="pswd_php" placeholder="password" required/>
        <input type="submit" name="sub" value="Create account"/>
    </form>

    <p class="back-link"><a href="login.php">Already have an account? Login</a></p>
</div>
</body>
</html>
