<?php
session_start();
$con = new mysqli("localhost", "root", "", "CivicComplaintsDB");
if ($con->connect_error) die("Connection Failed: " . $con->connect_error);

// officers get their own dashboard; guests and citizens see this feed
if (isset($_SESSION['role']) && $_SESSION['role'] == 'officer') {
    header('location: officer_dashboard.php');
    exit;
}

// one row per complaint, with at most one preview image, instead of the old
// LEFT JOIN that duplicated a complaint once per attached image
$q = "SELECT c.*,
        (SELECT image_path FROM complaint_images WHERE complaint_id = c.complaint_id AND type = 'complaint' LIMIT 1) AS image_path
      FROM `complaints` c
      ORDER BY c.created_at DESC";
$res = mysqli_query($con, $q);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed - Civic Complaints</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="feed.php">Civic Complaints</a>
    <nav>
        <?php if (isset($_SESSION['user'])): ?>
            <a href="post_complaint.php">Post a complaint</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login to post</a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">
    <?php if (isset($_SESSION['user'])): ?>
        <p class="meta">Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?></p>
    <?php endif; ?>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="message"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
    <?php endif; ?>

    <h1>Complaints Feed</h1>

    <?php while ($row = mysqli_fetch_assoc($res)): ?>
        <div class="complaint-card">
            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
            <p><?php echo htmlspecialchars($row['description']); ?></p>
            <p class="meta">
                Category: <?php echo htmlspecialchars($row['category']); ?> |
                City: <?php echo htmlspecialchars($row['city']); ?> |
                <span class="status status-<?php echo htmlspecialchars($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span>
            </p>

            <?php if (!is_null($row['image_path'])): ?>
                <img src="complaints/photos/<?php echo htmlspecialchars($row['image_path']); ?>" width="200">
            <?php else: ?>
                <p class="meta">No image provided</p>
            <?php endif; ?>

            <p><a href="inspect_complaint.php?id=<?php echo (int) $row['complaint_id']; ?>">Inspect</a></p>
        </div>
    <?php endwhile; ?>
</div>
</body>
</html>
