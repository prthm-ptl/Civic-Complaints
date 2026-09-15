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
    <?php

    $cid = $_REQUEST['id'];

    $q="SELECT c.*,ci.image_path FROM `complaints` as `c` LEFT JOIN `complaint_images` as `ci` ON c.complaint_id=ci.complaint_id ";
            
        $res=mysqli_query($con,$q);
        while($row=mysqli_fetch_assoc($res))
            {
                echo "<span>";
                if($row['complaint_id']==$cid){
                    echo "<hr>";
                    echo "<h1>" . "Title:  " . $row['title'] . "</h1>";
                    echo "<p>" .  "Desc:  " . $row['description'] . "</p>";
                    echo "<p>" . "Category:  " . $row['category'] . "</p>";
                    echo "<p>" . "City:  " . $row['city'] . "</p>";
                    echo "<img src='/dashboard/pratham/images/$row[image_path]' width='250' height='auto' ><br>";
                echo "<hr>";
                }
                echo "</span>";
            }
    ?>
</body>
</html>