<?php
session_start();
if(!isset($_SESSION['userid']))
{
    header("location: login.php");
}
else{


?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data" >
    <input type="text" name="title_php" placeholder="title of complaint" required/>
    <textarea name="desc_php" placeholder="description of complaint" required></textarea>
    <select name="category_php" required>
        <option value="road" >road</option>
        <option value="drain">drain</option>
        <option value="garbage">garbage</option>
        <option value="corruption">corruption</option>
        <option value="other">other</option>
    </select>
    <input type="text" name="city_php" placeholder="city" required/>
    <input type="file" name="image"/>
    <input type="submit" name="sub"/>
</form>

<?php
} //logic to be coded...
?>