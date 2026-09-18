<?php 

    session_start();
    $con=mysqli_connect("localhost","root","","CivicComplaintsDB");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>feed</title>
</head>
<body>
    <?php
        if(isset($_SESSION['user']))
            {
                echo "welcome, MR " . $_SESSION['user'] . "<br>";
                echo "<a href='post_complaint.php'>post a complaint</a>" . "<br>";
                echo "<a href='logout.php'>logout</a>";

                if(isset($_SESSION['message']))
                {
                echo "<p>" . $_SESSION['message'] . "</p>";//complaint posted message
                unset($_SESSION['message']); 
                }
            }
        else{
            echo "<a href='login.php'>login to post a complaint.</a>";
        }

        $q="SELECT c.*,ci.image_path
            FROM `complaints` as `c`
            LEFT JOIN `complaint_images` as `ci` ON c.complaint_id=ci.complaint_id";
            
        $res=mysqli_query($con,$q);
        echo "<h1 style='text-align: center;'>complaints feed</h1>";
        while($row=mysqli_fetch_assoc($res))
            {
                echo "<hr>";
                echo "<h3>" . "Title:  " . $row['title'] . "</h3>";
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
                echo "<a href='inspect_complaint.php?id={$row['complaint_id']}'>inspect</a>";
                echo "<hr>";
            }
    ?>

</body>
</html>