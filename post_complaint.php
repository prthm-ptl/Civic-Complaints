<?php
session_start();
$con = new mysqli("localhost","root","","CivicComplaintsDB");
if($con->connect_error) die("Connection Failed: " . $con->connect_error);
if(!isset($_SESSION['userid']))
{
    echo "<a href='login.php'>login first</a>";
}
else{



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>post_complaint</title>
</head>
<body>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data" >
    <input type="text" name="title_php" placeholder="title of complaint" required/>
    <textarea name="desc_php" placeholder="description of complaint" required></textarea>
    <select name="category_php" required>
        <option value="road" >road</option>
        <option value="drain">drain</option>
        <option value="garbage">garbage</option>
        <option value="corruption">corruption</option>
        <option value="other">other</option>
    </select>
    <input type="text" name="city_php" placeholder="city" required/>
    <input type="file" name="image"/>
    <input type="submit" name="sub"/>
</form>

<?php
echo "<a href='feed.php'>";
$backpath = "/dashboard/CivicComplaints/Civic-Complaints/assets/" . "back.jpg";
echo "<img src='$backpath' width='50px'></a>";

if(isset($_REQUEST['sub']))
    {
        $title = $_REQUEST['title_php'];
        $desc = $_REQUEST['desc_php'];
        $category = $_REQUEST['category_php'];
        $city = $_REQUEST['city_php'];
        
        $userid = $_SESSION['userid'];
        $image = $_FILES['image']['name'];
        if (!empty($_FILES['image']['name']))
            {
                $allowed_extensions = ['jpg', 'jpeg', 'png'];
                $allowed_mimes = ['image/jpeg', 'image/png'];

                $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $file_mime = mime_content_type($_FILES['image']['tmp_name']); // checks actual file content, not just name

                if (!in_array($file_ext, $allowed_extensions) || !in_array($file_mime, $allowed_mimes))
                {
                    die("Error: Only JPG and PNG images are allowed.");
                }

                // rename file to avoid collisions and hide original name
                $image = uniqid() . '.' . $file_ext;
                $path = "/opt/lampp/htdocs/dashboard/CivicComplaints/Civic-Complaints/complaints/photos/";
                $newpath = $path . $image;

                if (!move_uploaded_file($_FILES["image"]["tmp_name"], $newpath))
                {
                    echo "Error: Could not save image.";
                }
            }
            else
            {
                $image = "No image";
            }
        

        $q = $con->prepare("INSERT INTO `complaints` (`user_id`, `title`, `description`, `category`, `city`) VALUES (?,?,?,?,?)");
        $q->bind_param("issss", $userid, $title, $desc, $category, $city);
        if(!$q->execute())
            {
                echo "Error: " . $con->error;
            }
        else{
            $complaint_id = $con->insert_id;
            $abc=1;
        }
        
        $qimage = $con->prepare("INSERT INTO `complaint_images` (`complaint_id`, `image_path`, `type`, `uploaded_by`) VALUES (?, ?, 'complaint', NULL)");
        $qimage->bind_param("is", $complaint_id, $image);
        if(!$qimage->execute())
            {
                echo "Error: " . $con->error;
            }

        if(isset($_REQUEST['sub']) and isset($abc))
        {
            $_SESSION['message'] = "Complaint posted successfully!";
            header("location:feed.php");
        }
    }
} 
?>
</body>
</html>

