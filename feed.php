<?php
session_start();
require_once 'db_connect.php';

if (isset($_SESSION['role']) && $_SESSION['role'] == 'officer') {
    header('location: officer_dashboard.php');
    exit;
}

// Join users so we can get pfp for the logged-in user's own complaints
$q = "SELECT c.*,
        (SELECT image_path FROM complaint_images WHERE complaint_id = c.complaint_id AND type = 'complaint' LIMIT 1) AS image_path,
        u.pfp AS poster_pfp,
        u.name AS poster_name
      FROM `complaints` c
      JOIN `users` u ON c.user_id = u.user_id
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
    <style>
        .pfp {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            vertical-align: middle;
            margin-right: 8px;
            border: 2px solid #2a6f6f;
        }
        .welcome-row {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
        }
        .complaint-poster {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            font-size: 0.9em;
            color: #444;
        }
        .complaint-poster .pfp {
            width: 28px;
            height: 28px;
            margin-right: 6px;
        }
    </style>
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
        <div class="welcome-row">
            <img
                src="assets/<?php echo htmlspecialchars($_SESSION['pfp']); ?>"
                alt="<?php echo htmlspecialchars($_SESSION['user']); ?>"
                class="pfp"
            >
            <span class="meta">Welcome, <strong><?php echo htmlspecialchars($_SESSION['user']); ?></strong></span>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="message"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
    <?php endif; ?>

    <h1>Complaints Feed</h1>

    <?php while ($row = mysqli_fetch_assoc($res)): ?>
        <div class="complaint-card">

            <?php
            // Only show poster identity to the person who filed the complaint
            $is_own = isset($_SESSION['userid']) && (int)$_SESSION['userid'] === (int)$row['user_id'];
            if ($is_own):
            ?>
                <div class="complaint-poster">
                    <img
                        src="assets/<?php echo htmlspecialchars($row['poster_pfp']); ?>"
                        alt="You"
                        class="pfp"
                    >
                    <span>You posted this</span>
                </div>
            <?php endif; ?>

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
