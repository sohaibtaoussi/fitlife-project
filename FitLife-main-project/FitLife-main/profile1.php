<?php
session_start();
require "config1.php";

if(!isset($_SESSION["user_id"])){
    header("Location :login1.php");
    exit();
}

//recuperer les infos d'user 
$userid=$_SESSION["user_id"];
$query="SELECT * FROM users Where id=?  ";
$stmt=$conn->prepare($query);
$stmt->bind_param("i",$userid);
$stmt->execute();
$result=$stmt->get_result();
$user=$result->fetch_assoc();

$username=$user["username"];
$email=$user["email"];
$profile_img=$user["profile_img"];

if($_SERVER["REQUEST_METHOD"] === "POST"){
    $username =$_POST["username"];
    $email =$_POST["email"];
    if (!empty($_FILES['profile_img']['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES['profile_img']['name']);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
         //check validation of the img 
        $check=getimagesize($_FILES["profile_img"]["tmp_name"]);
        if( $check === false){
           die("image is not valid ");
        
        }

        if(move_uploaded_file($_FILES["profile_img"]["tmp_name"],to: $target_dir)){
            $profile_img=basename($_FILES["profile_name"]["name"]);

        
          // update database 
          $updated_query="UPDATE users SET profile_img=? Where id=? ";
          $stmt=$conn->prepare($updated_query);
          $stmt->bind_param("si",$profile_img,$userid);
          if($stmt->execute()){
            $_SESSION["profile_img"]=$profile_img;
            header("Location :profile1.php");
            exit();
          }else {
            echo "image is not uploaded ";
          }
        } else {
        echo "Sorry, there was an error uploading your file.";
            exit();
        
        }  
    } else {
        // If no new image was uploaded, keep the existing one
        $profile_img = $user['profile_img']; 
    }

    $updated_query="UPDATE users SET usersname = ? , email = ? where id = ? ";
    $stmt=$conn->prepare($updated_query);
    $stmt->bind_param( "ssi" , $username ,$email ,$userid);
    $test = $stmt->execute();
    if($test){
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['profile_img'] = $profile_img;
        header(" Location : user_dashboard.php ");
        exit();
    }else {
        echo " error while executing";
    }

}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitLife - Profile</title>
    <style>
        /* 🌟 General Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f3f4f6, #e1efff);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        /* 🌐 Header */
        header {
            background-color: #333;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        header .logo {
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        header .profile-menu {
            position: relative;
            display: flex;
            align-items: center;
        }

        header .profile-menu .profile-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            border: 3px solid white;
            transition: transform 0.3s ease;
        }

        header .profile-menu .profile-icon:hover {
            transform: scale(1.1);
            border-color: #007bff;
        }

        header .profile-menu .dropdown-content {
            display: none;
            position: absolute;
            top: 60px;
            right: 0;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
            overflow: hidden;
            animation: fadeIn 0.3s;
        }

        header .profile-menu:hover .dropdown-content {
            display: block;
        }

        header .profile-menu .dropdown-content a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            font-size: 16px;
        }

        header .profile-menu .dropdown-content a:hover {
            background-color: #007bff;
            color: white;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* 💡 Profile Page Styles */
        .profile-container {
            max-width: 700px;
            margin: 50px auto;
            padding: 40px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .profile-container h2 {
            color: #088178;
            margin-bottom: 30px;
            font-size: 2rem;
        }

        .profile-container form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .profile-container label {
            font-weight: 600;
            font-size: 16px;
            text-align: left;
            color: #555;
        }

        .profile-container input[type="text"],
        .profile-container input[type="email"],
        .profile-container input[type="file"],
        .profile-container input[type="submit"] {
            padding: 12px;
            font-size: 16px;
            border-radius: 10px;
            border: 1px solid #ddd;
            width: 100%;
            transition: border-color 0.3s;
        }

        .profile-container input[type="text"]:focus,
        .profile-container input[type="email"]:focus,
        .profile-container input[type="file"]:focus {
            border-color: #088178;
            outline: none;
        }

        .profile-container input[type="submit"] {
            background-color: #088178;
            color: white;
            font-size: 1.2rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .profile-container input[type="submit"]:hover {
            background-color: #065f59;
        }

        .profile-container .profile-preview {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            margin: 10px auto;
            border: 4px solid #088178;
            transition: transform 0.3s;
        }

        .profile-container .profile-preview:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">FitLife</div>
        <nav>
            <div class="profile-menu">
                <img src="uploads/<?php echo $profile_img ? $profile_img : 'default-profile.png'; ?>"
                     alt="Profile" class="profile-icon">
                <div id="profileDropdown" class="dropdown-content">
                    <a href="purchases.php">My Purchases</a>
                    <a href="user_dashboard.php">Home Page</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </nav>
    </header>

    <div class="profile-container">
        <h2>Your Profile</h2>
        <form action="update_profile.php" method="POST" enctype="multipart/form-data">
            <label for="username">Username</label>
            <input type="text" name="username" value="<?php echo $username; ?>" required>

            <label for="email">Email</label>
            <input type="email" name="email" value="<?php echo $email; ?>" required>

            <label for="profile_img">Profile Image</label>
            <input type="file" name="profile_img" accept="image/*">
            <!-- Display the current profile image -->     
            <input type="submit" value="Update Profile">
        </form>
    </div>
</body>
