<?php 
    session_start();
    $con=mysqli_connect("localhost","root","","CivicComplaintsDB");
    if(isset($_SESSION['user']))
        {
            echo "welcome, MR" . $_SESSION['user'] . "<br>";
            echo "<a href='post_complaint.php'>post a complaint</a>" . "<br>";
            echo "<a href='logout.php'>logout</a>";

            if(isset($_SESSION['message']))
            {
            echo "<p>" . $_SESSION['message'] . "</p>";
            unset($_SESSION['message']); 
            }
        }
    else{
        echo "<a href='login.php'>login to post a complaint.</a>";
    }
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
        
        $q="SELECT * 
            FROM `complaints` as `c`
            JOIN `complaint_images` as `ci` ON c.complaint_id=ci.complaint_id";
        $res=mysqli_query($con,$q);
        echo "<h1 style='text-align: center;'>complaints feed</h1>";
        while($row=mysqli_fetch_assoc($res))
            {
                echo "<hr>";
                echo "<h3>" . "Title:  " . $row['title'] . "</h3>";
                echo "<p>" .  "Desc:  " . $row['description'] . "</p>";
                echo "<p>" . "Category:  " . $row['category'] . "</p>";
                echo "<p>" . "City:  " . $row['city'] . "</p>";
                echo "<hr>";
            }
    ?>

</body>
</html>