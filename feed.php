<?php 
    session_start();
    $con=mysqli_connect("localhost","root","","CivicComplaintsDB");
    if(isset($_SESSION['user']))
        {
            echo "welcome, MR" . $_SESSION['user'] . "<br>";
            echo "<a href='post_complaint.php'>post a complaint</a>" . "<br>";
            echo "<a href='logout.php'>logout</a>";
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
        
        $q="SELECT * FROM `complaints`";
        $res=mysqli_query($con,$q);
        echo "<h1 style='text-align: center;'>complaints feed</h1>";
        while($row=mysqli_fetch_row($res))
            {
                echo "<hr>";
                echo "<h3>" . "Title:  " . $row[2] . "</h3>";
                echo "<p>" .  "Desc:  " . $row[3] . "</p>";
                echo "<p>" . "Category:  " . $row[4] . "</p>";
                echo "<p>" . "City:  " . $row[5] . "</p>";
                echo "<hr>";
            }
    ?>

</body>
</html>