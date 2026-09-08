<?php
session_start();
$con = new mysqli("localhost","root","","CivicComplaintsDB");
if($con->connect_error) die("Connection Failed: " . $con->connect_error);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>signup</title>
</head>
<body>
    

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
    echo "<a href='login.php'>";
    $backpath = "/dashboard/CivicComplaints/Civic-Complaints/photos/" . "back.jpg";
    echo "<img src='$backpath' width='50px'></a>";
                   
    if(isset($_REQUEST['sub']))
        {
            $name = ucfirst($_REQUEST['name']); //to capitalize the first letter of the name
            $username = $_REQUEST['username_php'];
            $pswd = password_hash($_REQUEST['pswd_php'],PASSWORD_BCRYPT);
            $role = $_REQUEST['role_php'];
            $photoname = $name[0] . ".png"; //to set the profile picture name as the first letter of the name with .png extension
            
            $q = $con->prepare("INSERT INTO `users` (`username`, `password`, `name`, `role`, `pfp`) VALUES (?,?,?,?,?)");
            $q->bind_param("sssss", $username, $pswd, $name, $role, $photoname);
            if(!$q->execute())
                {
                    echo "Error: " . $con->error;
                }
            $ver_username = $_REQUEST["username_php"];
            $ver_pswd = $_REQUEST["pswd_php"];

            $q = $con->prepare("SELECT * FROM `users` WHERE username=?");
            $q->bind_param("s", $ver_username);
            $q->execute();
            $p = $q->get_result();
            if($p)
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
                                    echo "error: username or password mismatch";
                                }
                        }
                    else{
                        echo "error: username or password mismatch";
                    }
                }
        }

?>
</body>
</html>
