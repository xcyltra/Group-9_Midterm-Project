<?php 

$con = mysqli_connect('localhost', 'root', '', 'kasirku');

if (!$con) {
    die("Tidak dapat terhubung ke db").mysqli_connect_errno();
}

?>