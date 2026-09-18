<?php 

    session_start();
    $con=mysqli_connect("localhost","root","","CivicComplaintsDB");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>inspect</title>
</head>
<body>

            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data" >   
                <input type="text" name="cmnt" placeholder="commment"/>
                <input type="file" name="image_cmnt" />
                <input type="submit" name="sub_cmnnt" />
            </form>

    <?php

    $cid = $_REQUEST['id'];//passed id

    $q="SELECT c.*,ci.image_path FROM `complaints` as `c` LEFT JOIN `complaint_images` as `ci` ON c.complaint_id=ci.complaint_id ";
            
        $res=mysqli_query($con,$q);
        while($row=mysqli_fetch_assoc($res))
            {
                echo "<span>";
                if($row['complaint_id']==$cid)
                    {//to show the right row and not all
                        echo "<hr>";
                        echo "<h1>" . "Title:  " . $row['title'] . "</h1>";
                        echo "<p>" .  "Desc:  " . $row['description'] . "</p>";
                        echo "<p>" . "Category:  " . $row['category'] . "</p>";
                        echo "<p>" . "City:  " . $row['city'] . "</p>";
                        if(!is_null($row['image_path']))
                        {
                            echo "<img src='/dashboard/pratham/images/{$row['image_path']}' width='250' height='auto'> <br>";
                        }
                        else
                        {
                            echo "no image provided <br>";

                        }
                        echo "<hr>";
                    }
                echo "</span>";
            }
        if(isset($_REQUEST['sub_cmnt']))
            {
                $cmnt_text=$_REQUEST['cmnt'];
                $cmnt_image=$_REQUEST['image_cmnt'];

                // $qr=
            }
        

        echo "<a href='feed.php'>";
        $backpath = "/dashboard/CivicComplaints/Civic-Complaints/assets/" . "back.jpg";
        echo "<img src='$backpath' width='50px'></a>";
    ?>
</body>
</html> 