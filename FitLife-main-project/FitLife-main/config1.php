<?php
$host="localhost";
$username="root";
$password="";
$database="user_auth";

$conn= new mysqli($host,$username,$password,$database);

//check connection 
if($conn->connect_error){
    die("connection failed " .$conn->connect_error);
}
echo "Connected successfuly ";
    







?>