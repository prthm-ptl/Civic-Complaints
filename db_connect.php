<?php
$con = new mysqli("localhost", "root", "", "CivicComplaintsDB");
if ($con->connect_error) die("Connection Failed: " . $con->connect_error);

// Define global upload path for images
define('UPLOAD_DIR', __DIR__ . '/complaints/photos/');
?>