<?php
#to allow user that register only to access the file

if(empty($_SESSION['role']) || $_SESSION['role'] != "user"){
    die("<script>alert('please login');
    window.location.href='logout.php';</script>")
}

?>