<?php
session_start();
require_once 'db_connect.php';

$cid    = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
$notice = "";
$error  = "";

// ---- handle comment deletion ----
if (isset($_REQUEST['delete_comment']) && isset($_SESSION['userid'])) {
    $comment_id = (int) $_REQUEST['comment_id'];
    $uid        = $_SESSION['userid'];

    // Only delete if this user owns the comment
    $del = $con->prepare("DELETE FROM `complaint_comments` WHERE `comment_id` = ? AND `user_id` = ?");
    $del->bind_param("ii", $comment_id, $uid);
    $del->execute();

    header("location: inspect_complaint.php?id=$cid");
    exit;
}

// ---- handle citizen comment ----
if (isset($_REQUEST['sub_cmnt']) && isset($_SESSION['userid'])) {
    $comment_text  = trim($_REQUEST['cmnt']);
    $comment_image = null;

    if (!empty($_FILES['image_cmnt']['name'])) {
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        $allowed_mimes      = ['image/jpeg', 'image/png'];
        $file_ext  = strtolower(pathinfo($_FILES['image_cmnt']['name'], PATHINFO_EXTENSION));
        $file_mime = mime_content_type($_FILES['image_cmnt']['tmp_name']);

        if (in_array($file_ext, $allowed_extensions) && in_array($file_mime, $allowed_mimes)) {
            $comment_image = uniqid() . '.' . $file_ext;
            move_uploaded_file($_FILES['image_cmnt']['tmp_name'], UPLOAD_DIR . $comment_image);
        } else {
            $error = "Only JPG and PNG images are allowed.";
        }
    }

    if ($error === '' && ($comment_text !== '' || $comment_image !== null)) {
        $uid = $_SESSION['userid'];
        $qc  = $con->prepare("INSERT INTO `complaint_comments` (`complaint_id`, `user_id`, `comment_text`, `image_path`, `is_pinned`) VALUES (?, ?, ?, ?, 0)");
        $qc->bind_param("iiss", $cid, $uid, $comment_text, $comment_image);
        $qc->execute();
        $notice = "Comment posted.";
    }
}

// ---- fetch complaint ----
$stmt = $con->prepare("SELECT * FROM `complaints` WHERE complaint_id = ?");
$stmt->bind_param("i", $cid);
$stmt->execute();
$complaint = $stmt->get_result()->fetch_assoc();
if (!$complaint) die("Complaint not found.");

// ---- fetch complaint images ----
$imgq = $con->prepare("SELECT image_path, type FROM complaint_images WHERE complaint_id = ?");
$imgq->bind_param("i", $cid);
$imgq->execute();
$images = $imgq->get_result();

// ---- fetch comments: pinned (officer) first, then oldest ----
$cq = $con->prepare("
    SELECT cm.*, u.name, u.role, u.pfp
    FROM `complaint_comments` cm
    JOIN `users` u ON cm.user_id = u.user_id
    WHERE cm.complaint_id = ?
    ORDER BY cm.is_pinned DESC, cm.commented_at ASC
");
$cq->bind_param("i", $cid);
$cq->execute();
$comments = $cq->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspect Complaint - Civic Complaints</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .comment { background:#f9f9f9; border:1px solid #e0e0e0; border-radius:6px; padding:10px 14px; margin-bottom:10px; }
        .comment.officer { border-left:4px solid #2a6f6f; background:#f0fafa; }
        .comment-meta { font-size:0.85em; color:#666; margin-bottom:4px; display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
        .badge { display:inline-block; background:#2a6f6f; color:#fff; font-size:0.75em; padding:2px 7px; border-radius:10px; }
        .comment-pfp { width:26px; height:26px; border-radius:50%; object-fit:cover; border:2px solid #2a6f6f; flex-shrink:0; }
        .delete-btn {
            margin-left:auto;
            background: none;
            border: none;
            color: #b91c1c;
            font-size: 0.8em;
            cursor: pointer;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .delete-btn:hover { background:#fde8e8; }
    </style>
</head>
<body>
<header class="topbar">
    <a class="brand" href="feed.php">Civic Complaints</a>
    <nav>
        <?php if (isset($_SESSION['user'])): ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </nav>
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

    <h3>Comments</h3>

    <?php if ($comments->num_rows === 0): ?>
        <p class="meta">No comments yet.</p>
    <?php else: ?>
        <?php while ($c = $comments->fetch_assoc()):
            $is_own_comment = isset($_SESSION['userid']) && (int)$_SESSION['userid'] === (int)$c['user_id'];
        ?>
            <div class="comment <?php echo $c['role'] === 'officer' ? 'officer' : ''; ?>">
                <div class="comment-meta">

                    <?php if ($c['role'] === 'officer'): ?>
                        <!-- Officers are always identified publicly -->
                        <img src="assets/<?php echo htmlspecialchars($c['pfp']); ?>" class="comment-pfp" alt="<?php echo htmlspecialchars($c['name']); ?>">
                        <span><?php echo htmlspecialchars($c['name']); ?></span>
                        <span class="badge">Officer</span>

                    <?php elseif ($is_own_comment): ?>
                        <!-- Citizen sees their own identity only to themselves -->
                        <img src="assets/<?php echo htmlspecialchars($c['pfp']); ?>" class="comment-pfp" alt="You">
                        <span><?php echo htmlspecialchars($c['name']); ?> <em style="color:#999;font-size:0.9em;">(you)</em></span>

                    <?php else: ?>
                        <!-- Everyone else sees Anonymous -->
                        <span>Anonymous</span>

                    <?php endif; ?>

                    <span style="color:#bbb;">·</span>
                    <span><?php echo htmlspecialchars($c['commented_at']); ?></span>

                    <?php if ($is_own_comment): ?>
                        <!-- Delete button — only visible to the comment's author -->
                        <form method="post" action="inspect_complaint.php?id=<?php echo $cid; ?>"
                              style="margin:0; display:inline;"
                              onsubmit="return confirm('Delete this comment?');">
                            <input type="hidden" name="comment_id" value="<?php echo (int)$c['comment_id']; ?>">
                            <button type="submit" name="delete_comment" class="delete-btn" title="Delete comment">✕ Delete</button>
                        </form>
                    <?php endif; ?>

                </div>

                <?php if (!empty($c['comment_text'])): ?>
                    <p style="margin:4px 0;"><?php echo htmlspecialchars($c['comment_text']); ?></p>
                <?php endif; ?>
                <?php if (!empty($c['image_path'])): ?>
                    <img src="complaints/photos/<?php echo htmlspecialchars($c['image_path']); ?>" width="180" style="margin-top:6px;">
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['userid']) && $_SESSION['role'] === 'citizen'): ?>
        <h3>Add a comment</h3>
        <?php if ($notice): ?><div class="message"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
        <?php if ($error):  ?><div class="error"><?php echo htmlspecialchars($error);  ?></div><?php endif; ?>

        <form action="inspect_complaint.php?id=<?php echo $cid; ?>" method="post" enctype="multipart/form-data">
            <textarea name="cmnt" placeholder="Write a comment..."></textarea>
            <input type="file" name="image_cmnt"/>
            <input type="submit" name="sub_cmnt" value="Post comment"/>
        </form>
    <?php elseif (!isset($_SESSION['userid'])): ?>
        <p class="meta"><a href="login.php">Login</a> to leave a comment.</p>
    <?php endif; ?>

    <p class="back-link"><a href="feed.php">&larr; Back to feed</a></p>
</div>
</body>
</html>
