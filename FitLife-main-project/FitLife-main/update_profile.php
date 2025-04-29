<?php
session_start();
require 'config.php'; // Contains your database connection

// Fetch user data from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT username, email, profile_img FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle form submission to update profile
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);

    // Profile image upload
    $profile_img = $user['profile_img']; // Default to current image

    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] == 0) {
        // Upload new profile image
        $image_name = $_FILES['profile_img']['name'];
        $image_tmp_name = $_FILES['profile_img']['tmp_name'];
        $image_size = $_FILES['profile_img']['size'];
        $image_extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        // Check for valid image extensions
        $valid_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($image_extension, $valid_extensions)) {
            // Define upload path
            $upload_dir = 'uploads/';
            $new_image_name = uniqid() . '.' . $image_extension;
            $upload_path = $upload_dir . $new_image_name;

            if (move_uploaded_file($image_tmp_name, $upload_path)) {
                // If upload is successful, update profile_img
                $profile_img = $upload_path;
            } else {
                echo "Error uploading image.";
            }
        } else {
            echo "Invalid image format.";
        }
    }

    // Update database with new data
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, profile_img = ? WHERE id = ?");
    $stmt->bind_param("sssi", $username, $email, $profile_img, $user_id);

    if ($stmt->execute()) {
        // If successful, update session data and redirect
        $_SESSION['username'] = $username;
        $_SESSION['profile_img'] = $profile_img;
        header("Location: profile.php");
        exit();
    } else {
        echo "Error updating profile.";
    }
    $stmt->close();
}
$conn->close();
?>
<?php
// Include the PHP code above to handle form processing and session data
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
    <div class="profile-container">
        <h2>Update Profile</h2>
        <form action="updated_profile.php" method="POST" enctype="multipart/form-data">
            <label for="username">Full Name</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label for="profile_img">Profile Image</label>
            <input type="file" id="profile_img" name="profile_img">

            <img src="<?php echo isset($user['profile_img']) ? $user['profile_img'] : 'uploads/default-profile.png'; ?>" alt="Profile" class="profile-preview">
            
            <input type="submit" value="Update Profile">
        </form>
    </div>
</body>
</html>
