<?php
#to allow admin only to access the file

if(empty($_SESSION['role']) || $_SESSION['role'] != "admin"){
    die("<script>alert('please login');
    window.location.href='logout.php';</script>")
}

?>