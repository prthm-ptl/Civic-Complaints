<?php 
    session_start();
    if(isset($_SESSION['user']))
        {
            echo "welcome, MR" . $_SESSION['user'] . "<br>";
            echo "<a href='post_complaint.php'>post a complaint</a>";
        }
    else{
        echo "<a href='login.php'>login first</a>";
    }
?>