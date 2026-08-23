<?php
session_start();
$con=mysqli_connect("localhost","root","","CivicComplaintsDB");
if(!isset($_SESSION['signup']))
{
    echo "invalid access";
}
else{
?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data" >
    <input type="text" name="name" placeholder="your name" pattern="[a-zA-Z]+" required/>
    <input type="text" name="username_php" placeholder="username" pattern="[a-zA-Z0-9]+" required/>
    <input type="password" name="pswd_php" placeholder="password" required/>
    <select name="role_php" >
        <option value="citizen" selected>citizen</option>
        <option value="officer">officer</option>
    </select>
    <input type="submit" name="sub"/>
</form>

<?php
    echo "<a href='login.php'>login insted</a>";
                   
    if(isset($_REQUEST['sub']))
        {
            $name = ucfirst($_REQUEST['name']); //to capitalize the first letter of the name
            $username = $_REQUEST['username_php'];
            $pswd = password_hash($_REQUEST['pswd_php'],PASSWORD_BCRYPT);
            $role = $_REQUEST['role_php'];
            $photoname = $name[0] . ".png"; //to set the profile picture name as the first letter of the name with .png extension
            
            $q="INSERT INTO `users` (`username`, `password`, `name`, `role`, `pfp`) VALUES ('$username','$pswd','$name','$role','$photoname')";
            if(!mysqli_query($con,$q))
                {
                    echo "Error: " . mysqli_error($con);
                }
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
                                    unset($_SESSION['signup']);
                                    header("location: feed.php");
                                }
                                else{
                                    echo "error: username or password mismatch";
                                }
                        }
                    else{
                        echo "error: username or password mismatch";
                    }
                }
        }
}
?>