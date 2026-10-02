<?php
session_start();
$con = new mysqli("localhost", "root", "", "CivicComplaintsDB");
if ($con->connect_error) die("Connection Failed: " . $con->connect_error);

$cid = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

$notice = "";
if (isset($_REQUEST['sub_cmnt'])) {
    // NOTE: there is no `comments` table in the current database schema yet,
    // so nothing is saved here. This just tells the user honestly instead of
    // silently doing nothing. Add a `comments` table to wire this up for real.
    $notice = "Comments aren't connected to the database yet - this is a placeholder.";
}

$stmt = $con->prepare("SELECT * FROM `complaints` WHERE complaint_id = ?");
$stmt->bind_param("i", $cid);
$stmt->execute();
$complaint = $stmt->get_result()->fetch_assoc();

if (!$complaint) {
    die("Complaint not found.");
}

$imgq = $con->prepare("SELECT image_path, type FROM complaint_images WHERE complaint_id = ?");
$imgq->bind_param("i", $cid);
$imgq->execute();
$images = $imgq->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspect Complaint - Civic Complaints</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="feed.php">Civic Complaints</a>
</header>

<div class="container">
    <div class="complaint-card">
        <h1><?php echo htmlspecialchars($complaint['title']); ?></h1>
        <p><?php echo htmlspecialchars($complaint['description']); ?></p>
        <p class="meta">
            Category: <?php echo htmlspecialchars($complaint['category']); ?> |
            City: <?php echo htmlspecialchars($complaint['city']); ?> |
            <span class="status status-<?php echo htmlspecialchars($complaint['status']); ?>"><?php echo htmlspecialchars($complaint['status']); ?></span>
        </p>

        <?php if ($images->num_rows > 0): ?>
            <?php while ($img = $images->fetch_assoc()): ?>
                <img src="complaints/photos/<?php echo htmlspecialchars($img['image_path']); ?>" width="200" title="<?php echo htmlspecialchars($img['type']); ?>">
            <?php endwhile; ?>
        <?php else: ?>
            <p class="meta">No images provided</p>
        <?php endif; ?>
    </div>

    <h3>Add a comment</h3>

    <?php if ($notice): ?>
        <div class="message"><?php echo htmlspecialchars($notice); ?></div>
    <?php endif; ?>

    <form action="inspect_complaint.php?id=<?php echo $cid; ?>" method="post" enctype="multipart/form-data">
        <input type="text" name="cmnt" placeholder="comment"/>
        <input type="file" name="image_cmnt"/>
        <input type="submit" name="sub_cmnt" value="Comment"/>
    </form>

    <p class="back-link"><a href="feed.php">&larr; Back to feed</a></p>
</div>
</body>
</html>
