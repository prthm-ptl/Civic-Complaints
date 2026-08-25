<?php
session_start();
$con=mysqli_connect("localhost","root","","CivicComplaintsDB");
if(!isset($_SESSION['userid']))
{
    echo "<a href='login.php'>login first</a>";
}
else{

echo "<a href='feed.php'>";
$backpath = "/dashboard/CivicComplaints/Civic-Complaints/photos/" . "back.jpg";
echo "<img src='$backpath' width='50px'></a>";

?>

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


if(isset($_REQUEST['sub']))
    {
        $title = $_REQUEST['title_php'];
        $desc = $_REQUEST['desc_php'];
        $category = $_REQUEST['category_php'];
        $city = $_REQUEST['city_php'];
        
        $userid = $_SESSION['userid'];
        $image = $_FILES['image']['name'];
        if(strlen($image)!=0)
        {
            $path="/opt/lampp/htdocs/dashboard/CivicComplaints/Civic-Complaints/complaints/images/";
            $newpath = $path . $image ;
            move_uploaded_file($_FILES["image"]["tmp_name"],$newpath);
        }
        else
            {
            $image="No image";
            }
        

        $q="INSERT INTO `complaints` (`user_id`, `title`, `description`, `category`, `city`) VALUES ('$userid','$title','$desc','$category','$city')";
        if(!mysqli_query($con,$q))
            {
                echo "Error: " . mysqli_error($con);
            }
        else{
            echo "Complaint posted successfully";
        }
        
        $complaint_id = mysqli_insert_id($con);

        $qimage="INSERT INTO `complaint_images` (`complaint_id`, `image_path`, `type`, `uploaded_by`, `uploaded_at`) VALUES ('$complaint_id', '$image', 'complaint', NULL)";
        if(!mysqli_query($con,$qimage))
            {
                echo "Error: " . mysqli_error($con);    //not working
            }
    }
} 
?>