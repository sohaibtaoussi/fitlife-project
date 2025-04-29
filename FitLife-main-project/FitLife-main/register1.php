<?php
session_start();
require "config1.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
//verification l'existence des parametres   
 if(!isset($_POST["username"]) || !isset($_POST["email"]) || !isset($_POST["password"]) || !isset($_POST["confirm_passsword"])) {
    die("tous les champs sont requis");
 }


 $username= trim($_POST["username"]);
 $email=trim($_POST["email"]);
 $password=$_POST["password"];
 $confirm_password=$_POST["confirm_password"];

 if($password!==$confirm_password){
    die("the password do not match ");
 }

 //validation d'email
 if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
     die("Format invalide d'email");
 }
 //validation password
 if (strlen($password) <8){
    die("password invalid");
 }


 //check if email already exists 
 $query="SELECT id FROM users WHERE email=?";
 $stmt=$conn->prepare($query);
 $stmt->bind_param("s",$email);
 $stmt->execute();
 $stmt->store_result();
 if ($stmt->get_result()->num_rows > 0) {
     die( "Cet email est déjà utilisé");
 }
 $stmt->close();

 $hashed_password=password_hash($password,PASSWORD_DEFAULT);

 //insert into database
 $query="INSERT INTO users (username,password,email) VALUES (?,?,?)";
 $stmt=$conn->prepare($query);
 $stmt->bind_param("sss",$username,$hashed_password,$email);
 if($stmt->execute()){
    header("Location:login.php");
    exit();
 }else{
    echo "ERROR:" . $stmt->error;
 }
 $stmt->close();

}
$conn->close() ;























































?>