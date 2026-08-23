<?php 
$con = mysqli_connect("localhost","root","","CivicComplaintsDB") or die("Connection Failed");
session_start();
?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" >
    <input type="text" name="username_php" placeholder="username"/>
    <input type="password" name="pswd_php" placeholder="password" />
    <input type="submit" name="sub" value="submit"/>
    <input type="submit" name="sign_up" value="Sign Up"  />
    
</form>
<?php
    if(isset($_REQUEST["sub"]))
        {
            $ver_username = $_REQUEST["username_php"];
            $ver_pswd = $_REQUEST["pswd_php"];

            $q="SELECT * FROM `users` WHERE username='$ver_username'";
            if($p=mysqli_query($con,$q))
                {   
                    if(mysqli_num_rows($p))
                        {
                            $array=mysqli_fetch_array($p);
                            if($ver_username && password_verify($ver_pswd,$array['password']))
                                {
                                    $_SESSION['userid']=$array['user_id'];
                                    $_SESSION['role']=$array['role'];
                                    $_SESSION['user']=$array['name'];
                                    header("location: feed.php");
                                }
                                else{
                                    echo "username or password mismatch";
                                }
                        }
                    else{
                        echo "username or password mismatch";
                    }
                }
        }
    
    if(isset($_REQUEST["sign_up"]))
        {
            $_SESSION['signup'] = 1;
            header("location: signup.php");
        }
?>