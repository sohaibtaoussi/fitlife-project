<?php
session_start();
require 'config.php'; // Contains your database connection

// Initialize variables
$error = '';
$old_email = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $old_email = $email; // Preserve the email if login fails

    // Prepare and execute the query
    $stmt = $conn->prepare("SELECT id, username, password, profile_img FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // ✅ Valid login: set session variables and redirect
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['profile_img'] = $user['profile_img'] ?? null;
            header("Location: user_dashboard.php");
            exit();
        } else {
            // ❌ Invalid password
            $error = "❌ Invalid password.";
        }
    } else {
        // ⚠️ No user found
        $error = "⚠️ No user found with that email.";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        /* Corps de la page */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #f3f4f6, #e1efff);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        /* Effet de cercle dans l'arrière-plan */
        .background-decorations {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .background-decorations span {
            position: absolute;
            display: block;
            width: 200px;
            height: 200px;
            background: rgba(173, 81, 11, 0.3);
            border-radius: 50%;
            animation: float 6s infinite ease-in-out;
        }

        .background-decorations span:nth-child(1) { top: 20%; left: 10%; animation-delay: 0s; }
        .background-decorations span:nth-child(2) { top: 70%; left: 30%; animation-delay: 1s; }
        .background-decorations span:nth-child(3) { top: 40%; left: 80%; animation-delay: 2s; }
        .background-decorations span:nth-child(4) { top: 10%; left: 60%; animation-delay: 3s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(20px); }
        }

        /* Conteneur du formulaire */
        .login-container {
            background-color: #fff;
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            z-index: 1;
            text-align: center;
        }

        .login-container h2 {
            color: #088178;
            margin-bottom: 20px;
            font-size: 1.8rem;
        }

        .login-container form {
            display: flex;
            flex-direction: column;
        }

        .login-container input[type="text"],
        .login-container input[type="password"],
        .login-container input[type="email"],
        .login-container input[type="submit"] {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            width: 100%;
        }

        .login-container input[type="submit"] {
            background-color: #088178;
            color: white;
            border: none;
            padding: 12px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .login-container input[type="submit"]:hover {
            background-color: #065f59;
        }

        .login-container .register-link {
            margin-top: 15px;
            font-size: 0.9rem;
        }

        .login-container .register-link a {
            color: #088178;
            text-decoration: none;
        }

        .login-container .register-link a:hover {
            text-decoration: underline;
        }

        /* ✅ Style pour le message d'erreur */
        .error-message {
            color: #fff;
            background-color: #f44336;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Arrière-plan décoratif -->
    <div class="background-decorations">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>

    <!-- Contenu principal -->
    <div class="login-container">
        <h2>Login</h2>

        <!-- ✅ Error message display -->
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required 
                   value="<?php echo htmlspecialchars($old_email); ?>">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>

            <input type="submit" value="Login">
        </form>        
        <div class="register-link">
            Don't have an account? <a href="register.html">Register here</a>
        </div>
    </div>
</body>
</html>
