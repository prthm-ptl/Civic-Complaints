<?php
session_start();
require_once 'db_connect.php';
require_once 'auth_guard.php';
require_login();
require_role('citizen');

$error = "";

if (isset($_POST['sub'])) {
    $title    = trim($_POST['title_php']);
    $desc     = trim($_POST['desc_php']);
    $category = $_POST['category_php'];
    $city     = trim($_POST['city_php']);
    $userid   = $_SESSION['userid'];

    $q = $con->prepare("INSERT INTO `complaints` (`user_id`, `title`, `description`, `category`, `city`) VALUES (?,?,?,?,?)");
    $q->bind_param("issss", $userid, $title, $desc, $category, $city);

    if (!$q->execute()) {
        $error = "Error posting complaint: " . $con->error;
    } else {
        $complaint_id = $con->insert_id;

        if (!empty($_FILES['image']['name'])) {
            $allowed_extensions = ['jpg', 'jpeg', 'png'];
            $allowed_mimes      = ['image/jpeg', 'image/png'];
            $file_ext  = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $file_mime = mime_content_type($_FILES['image']['tmp_name']);

            if (!in_array($file_ext, $allowed_extensions) || !in_array($file_mime, $allowed_mimes)) {
                $error = "Complaint posted, but the image was skipped: only JPG and PNG are allowed.";
            } else {
                $image   = uniqid() . '.' . $file_ext;
                $newpath = UPLOAD_DIR . $image;

                if (!move_uploaded_file($_FILES["image"]["tmp_name"], $newpath)) {
                    $error = "Complaint posted, but the image could not be saved.";
                } else {
                    $qimage = $con->prepare("INSERT INTO `complaint_images` (`complaint_id`, `image_path`, `type`, `uploaded_by`) VALUES (?, ?, 'complaint', ?)");
                    $qimage->bind_param("isi", $complaint_id, $image, $userid);
                    if (!$qimage->execute()) {
                        $error = "Complaint posted, but the image could not be linked: " . $con->error;
                    }
                }
            }
        }

        if ($error === "") {
            $_SESSION['message'] = "Complaint posted successfully!";
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
    <title>Post a Complaint - Civic Complaints</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="feed.php">Civic Complaints</a>
    <nav>
        <a href="feed.php">Back to feed</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h1>Post a Complaint</h1>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="post_complaint.php" method="post" enctype="multipart/form-data">
        <input type="text" name="title_php" placeholder="title of complaint" required/>
        <textarea name="desc_php" placeholder="description of complaint" required></textarea>
        <select name="category_php" required>
            <option value="road">road</option>
            <option value="drain">drain</option>
            <option value="garbage">garbage</option>
            <option value="corruption">corruption</option>
            <option value="other">other</option>
        </select>
        <input type="text" name="city_php" placeholder="city" required/>
        <input type="file" name="image"/>
        <input type="submit" name="sub" value="Post complaint"/>
    </form>
</div>
</body>
</html>