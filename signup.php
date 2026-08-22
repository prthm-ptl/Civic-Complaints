<?php
session_start();
$con=mysqli_connect("localhost","root","","CivicComplaintsDB");
?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
    <input type="text" name="name" placeholder="your name"/>
    <input type="text" name="username_php" placeholder="username"/>
    <input type="password" name="pswd_php" placeholder="password"/>
    <select name="role_php">
        <option>citizen</option>
        <option>officer</option>
    </select>
    <input type="file" name="pfp_php" />
    <input type="submit" name="sub"/>
</form>

<?php
                   

    if(isset($_REQUEST['sub']))
        {
            $name = $_REQUEST['name'];
            $username = $_REQUEST['username_php'];
            $pswd = password_hash($_REQUEST['pswd_php'],PASSWORD_BCRYPT);
            $role = $_REQUEST['role_php'];

            $photoname = $_FILES["pfp_php"]["name"];

            if(strlen($photoname) == 0){
                $photoname = "default.jpg";
            }
            else{
                $path = "/opt/lampp/htdocs/dashboard/CivicComplaints/Civic-Complaints/photos/";
                $newpath = $path . $photoname ;
                if(!move_uploaded_file($_FILES["pfp_php"]["tmp_name"], $newpath)){
                echo "error";
                }
            }
            
            $newpath = "/dashboard/CivicComplaints/Civic-Complaints/photos/" . $photoname;
            $q="INSERT INTO `users` (`username`, `password`, `name`, `role`, `pfp`) VALUES ('$username','$pswd','$name','$role','$newpath')";
            if(mysqli_query($con,$q))
                {
                    echo "done";
                }
                else{
                    echo "error";
                }
        }
?>