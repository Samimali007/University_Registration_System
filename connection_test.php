<?php
$conn = mysqli_connect("localhost","root","","university_system");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "Connected successfully!";
?>