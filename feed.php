<?php 
    session_start();
    if(isset($_SESSION['user']))
        {
            echo "welcome, MR" . $_SESSION['user'];
        }
    else{
        echo "yu ain welcome bud, go to heaven";
    }
?>