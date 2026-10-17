<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'officer') {
    header('location: feed.php');
    exit;
}

$officer_id = $_SESSION['userid'];

// ---- handle officer comment ----
if (isset($_REQUEST['post_comment'])) {
    $complaint_id = (int) $_REQUEST['complaint_id'];
    $comment_text = trim($_REQUEST['comment_text']);
    $comment_image = null;

    if (!empty($_FILES['comment_image']['name'])) {
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        $allowed_mimes      = ['image/jpeg', 'image/png'];
        $file_ext  = strtolower(pathinfo($_FILES['comment_image']['name'], PATHINFO_EXTENSION));
        $file_mime = mime_content_type($_FILES['comment_image']['tmp_name']);

        if (in_array($file_ext, $allowed_extensions) && in_array($file_mime, $allowed_mimes)) {
            $comment_image = uniqid() . '.' . $file_ext;
            move_uploaded_file($_FILES['comment_image']['tmp_name'], UPLOAD_DIR . $comment_image);
        }
    }

    if ($comment_text !== '' || $comment_image !== null) {
        $qc = $con->prepare("INSERT INTO `complaint_comments` (`complaint_id`, `user_id`, `comment_text`, `image_path`, `is_pinned`) VALUES (?, ?, ?, ?, 1)");
        $qc->bind_param("iiss", $complaint_id, $officer_id, $comment_text, $comment_image);
        $qc->execute();
        $_SESSION['officer_message'] = "Comment added to complaint #$complaint_id.";
    }
    header("location: officer_dashboard.php");
    exit;
}

// ---- handle status update + resolution photo ----
if (isset($_REQUEST['update_status'])) {
    $complaint_id = (int) $_REQUEST['complaint_id'];
    $new_status   = $_REQUEST['new_status'];

    $allowed_statuses = ['open', 'in_progress', 'resolved'];
    if (!in_array($new_status, $allowed_statuses)) die("Invalid status.");

    if ($new_status === 'resolved') {
        if (empty($_FILES['resolution_image']['name'])) {
            $_SESSION['officer_message'] = "Resolution photo is required to mark a complaint as resolved.";
            header("location: officer_dashboard.php");
            exit;
        }

        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        $allowed_mimes      = ['image/jpeg', 'image/png'];
        $file_ext  = strtolower(pathinfo($_FILES['resolution_image']['name'], PATHINFO_EXTENSION));
        $file_mime = mime_content_type($_FILES['resolution_image']['tmp_name']);

        if (!in_array($file_ext, $allowed_extensions) || !in_array($file_mime, $allowed_mimes))
            die("Error: Only JPG and PNG images are allowed.");

        $image   = uniqid() . '.' . $file_ext;
        $newpath = UPLOAD_DIR . $image;

        if (!move_uploaded_file($_FILES["resolution_image"]["tmp_name"], $newpath))
            die("Error: Could not save resolution image.");

        $qimg = $con->prepare("INSERT INTO `complaint_images` (`complaint_id`, `image_path`, `type`, `uploaded_by`) VALUES (?, ?, 'resolution', ?)");
        $qimg->bind_param("isi", $complaint_id, $image, $officer_id);
        if (!$qimg->execute()) die("Error saving resolution image: " . $con->error);
    }

    $qstatus = $con->prepare("UPDATE `complaints` SET `status` = ? WHERE `complaint_id` = ?");
    $qstatus->bind_param("si", $new_status, $complaint_id);
    if (!$qstatus->execute()) die("Error updating status: " . $con->error);

    $_SESSION['officer_message'] = "Complaint #$complaint_id updated to '$new_status'.";
    header("location: officer_dashboard.php");
    exit;
}

// ---- filters ----
$filter_city     = isset($_REQUEST['filter_city'])     ? trim($_REQUEST['filter_city'])     : '';
$filter_category = isset($_REQUEST['filter_category']) ? $_REQUEST['filter_category']       : '';
$filter_status   = isset($_REQUEST['filter_status'])   ? $_REQUEST['filter_status']         : '';

$query  = "SELECT * FROM `complaints` WHERE 1=1";
$params = [];
$types  = "";

if ($filter_city !== '')     { $query .= " AND city = ?";     $params[] = $filter_city;     $types .= "s"; }
if ($filter_category !== '') { $query .= " AND category = ?"; $params[] = $filter_category; $types .= "s"; }
if ($filter_status !== '')   { $query .= " AND status = ?";   $params[] = $filter_status;   $types .= "s"; }
$query .= " ORDER BY created_at DESC";

$stmt = $con->prepare($query);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Officer Dashboard - Civic Complaints</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="officer_dashboard.php">Civic Complaints</a>
    <nav><a href="logout.php">Logout</a></nav>
</header>

<div class="container">
    <h1>Officer Dashboard</h1>
    <p class="meta">Welcome, Officer <?php echo htmlspecialchars($_SESSION['user']); ?></p>

    <?php if (isset($_SESSION['officer_message'])): ?>
        <div class="message"><?php echo htmlspecialchars($_SESSION['officer_message']); unset($_SESSION['officer_message']); ?></div>
    <?php endif; ?>

    <div class="filter-form">
        <form class="inline" method="get">
            <input type="text" name="filter_city" placeholder="city" value="<?php echo htmlspecialchars($filter_city); ?>">
            <select name="filter_category">
                <option value="">all categories</option>
                <option value="road"       <?php if ($filter_category=='road')       echo 'selected'; ?>>road</option>
                <option value="drain"      <?php if ($filter_category=='drain')      echo 'selected'; ?>>drain</option>
                <option value="garbage"    <?php if ($filter_category=='garbage')    echo 'selected'; ?>>garbage</option>
                <option value="corruption" <?php if ($filter_category=='corruption') echo 'selected'; ?>>corruption</option>
                <option value="other"      <?php if ($filter_category=='other')      echo 'selected'; ?>>other</option>
            </select>
            <select name="filter_status">
                <option value="">all statuses</option>
                <option value="open"        <?php if ($filter_status=='open')        echo 'selected'; ?>>open</option>
                <option value="in_progress" <?php if ($filter_status=='in_progress') echo 'selected'; ?>>in_progress</option>
                <option value="resolved"    <?php if ($filter_status=='resolved')    echo 'selected'; ?>>resolved</option>
            </select>
            <input type="submit" value="Filter">
        </form>
    </div>

    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="complaint-card">
            <h3><?php echo htmlspecialchars($row['title']); ?> (#<?php echo (int)$row['complaint_id']; ?>)</h3>
            <p><?php echo htmlspecialchars($row['description']); ?></p>
            <p class="meta">
                Category: <?php echo htmlspecialchars($row['category']); ?> |
                City: <?php echo htmlspecialchars($row['city']); ?> |
                <span class="status status-<?php echo htmlspecialchars($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span>
            </p>
            <p class="meta">Filed: <?php echo htmlspecialchars($row['created_at']); ?></p>

            <?php
            $imgq = $con->prepare("SELECT image_path FROM complaint_images WHERE complaint_id = ? AND type = 'complaint'");
            $imgq->bind_param("i", $row['complaint_id']);
            $imgq->execute();
            $imgres = $imgq->get_result();
            while ($img = $imgres->fetch_assoc())
                echo "<img src='complaints/photos/" . htmlspecialchars($img['image_path']) . "' width='150'>";
            ?>

            <form class="inline" action="officer_dashboard.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="complaint_id" value="<?php echo (int)$row['complaint_id']; ?>">
                <select name="new_status">
                    <option value="open"        <?php if ($row['status']=='open')        echo 'selected'; ?>>open</option>
                    <option value="in_progress" <?php if ($row['status']=='in_progress') echo 'selected'; ?>>in_progress</option>
                    <option value="resolved"    <?php if ($row['status']=='resolved')    echo 'selected'; ?>>resolved</option>
                </select>
                <input type="file" name="resolution_image">
                <input type="submit" name="update_status" value="Update">
            </form>
            <p class="meta">Resolution photo required only when setting status to resolved.</p>

            <hr>
            <?php
            $cq = $con->prepare("
                SELECT cc.*, u.name, u.role
                FROM complaint_comments cc
                JOIN users u ON cc.user_id = u.user_id
                WHERE cc.complaint_id = ?
                ORDER BY cc.is_pinned DESC, cc.commented_at ASC
            ");
            $cq->bind_param("i", $row['complaint_id']);
            $cq->execute();
            $cres = $cq->get_result();
            if ($cres->num_rows > 0):
            ?>
                <p class="meta"><strong>Comments (<?php echo $cres->num_rows; ?>)</strong></p>
                <?php while ($c = $cres->fetch_assoc()): ?>
                    <div style="background:<?php echo $c['role']==='officer'?'#f0fafa':'#f9f9f9'; ?>;
                                border-left:4px solid <?php echo $c['role']==='officer'?'#2a6f6f':'#ddd'; ?>;
                                border-radius:4px; padding:8px 12px; margin-bottom:8px;">
                        <span class="meta">
                            <?php if ($c['role'] === 'officer'): ?>
                                <strong><?php echo htmlspecialchars($c['name']); ?></strong>
                                <span class="badge">Officer</span>
                            <?php else: ?>
                                Anonymous
                            <?php endif; ?>
                            &nbsp;·&nbsp; <?php echo htmlspecialchars($c['commented_at']); ?>
                        </span>
                        <?php if (!empty($c['comment_text'])): ?>
                            <p style="margin:4px 0;"><?php echo htmlspecialchars($c['comment_text']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($c['image_path'])): ?>
                            <img src="complaints/photos/<?php echo htmlspecialchars($c['image_path']); ?>" width="150" style="margin-top:4px;">
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="meta">No comments yet.</p>
            <?php endif; ?>
            <hr>
            <form action="officer_dashboard.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="complaint_id" value="<?php echo (int)$row['complaint_id']; ?>">
                <textarea name="comment_text" placeholder="Add an officer comment..." style="width:100%;min-height:60px;"></textarea>
                <input type="file" name="comment_image">
                <input type="submit" name="post_comment" value="Post comment">
            </form>
        </div>
    <?php endwhile; ?>
</div>
</body>
</html>
