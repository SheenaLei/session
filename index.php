<?php
session_start();
require_once "db.php";

if (isset($_SESSION["user_id"], $_SESSION["role"])) {
    header("Location: " .
        ($_SESSION["role"] === "admin" ? "home2.php" : "home.php"));
    exit;
}

$error = "";
$success = "";
$page = $_GET["page"] ?? "login";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    // =========================
    // REGISTRATION
    // =========================
    if ($action === "register") {
        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";
        $confirm = $_POST["confirm_password"] ?? "";
        $page = "register";

        if ($username === "" || $password === "" || $confirm === "") {
            $error = "All fields are required.";
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
            $error = "Username must be 3-50 characters using letters, numbers, or underscores.";
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters.";
        } elseif ($password !== $confirm) {
            $error = "Passwords do not match.";
        } else {
            $check = $conn->prepare(
                "SELECT id FROM users WHERE username = ? LIMIT 1"
            );
            $check->bind_param("s", $username);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $error = "Username already exists.";
            } else {
                $hashedPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $stmt = $conn->prepare(
                    "INSERT INTO users (username, password, role)
                     VALUES (?, ?, 'user')"
                );
                // Naka-ayos na ang bind_param para sa username at hashed password (ss)
                $stmt->bind_param("ss", $username, $hashedPassword);

                if ($stmt->execute()) {
                    $success = "Registration successful. You can now log in.";
                    $page = "login";
                } else {
                    $error = "Registration failed. Please try again.";
                }

                $stmt->close();
            }

            $check->close();
        }

    // =========================
    // LOGIN
    // =========================
    } elseif ($action === "login") {
        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";
        $page = "login";

        if ($username === "" || $password === "") {
            $error = "Please fill in all fields.";
        } else {
            $stmt = $conn->prepare(
                "SELECT id, username, password, role
                 FROM users
                 WHERE username = ?
                 LIMIT 1"
            );
            $stmt->bind_param("s", $username);
            $stmt->execute();

            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user["password"])) {
                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];

                header("Location: " .
                    ($user["role"] === "admin" ? "home2.php" : "home.php"));
                exit;
            } else {
                $error = "Invalid username or password.";
            }

            $stmt->close();
        }
    } else {
        $error = "Invalid request.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Registration</title>
    <style>
        :root {
            --olive-dark: #556B2F;
            --olive-primary: #6B8E23;
            --olive-light: #8FBC8F;
            --bg-olive: #4F5D2F;
            --bg-card-left: #5B6B2F;
            --bg-card-right: #F7F7F2;
            --text-dark: #2B3A1A;
            --text-muted: #667055;
            --input-bg: #EFEFE9;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--bg-olive);
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(139, 157, 110, 0.4) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(85, 107, 47, 0.6) 0%, transparent 40%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            display: flex;
            width: 900px;
            max-width: 100%;
            min-height: 520px;
            background: var(--bg-card-right);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        /* Left Side (Banner) */
        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #5B6B2F 0%, #3B481D 100%);
            color: #ffffff;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
        }

        .left-panel .logo-icon {
            width: 60px;
            height: 60px;
            margin-bottom: 15px;
            fill: #D3DCB8;
        }

        .left-panel h1 {
            font-size: 2.2rem;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .left-panel p {
            font-size: 1rem;
            color: #D3DCB8;
            line-height: 1.5;
            max-width: 280px;
        }

        /* Right Side (Form) */
        .right-panel {
            flex: 1.1;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .right-panel h2 {
            font-size: 2rem;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .right-panel .subtitle {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 25px;
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 20px;
            text-align: center;
        }
        .alert-error {
            background-color: #fce8e6;
            color: #c5221f;
            border: 1px solid #f5c2c0;
        }
        .alert-success {
            background-color: #e6f4ea;
            color: #137333;
            border: 1px solid #ceead6;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .input-box {
            width: 100%;
            padding: 14px 16px;
            background-color: var(--input-bg);
            border: 1px solid #E0E0D8;
            border-radius: 10px;
            font-size: 0.95rem;
            color: var(--text-dark);
            outline: none;
            transition: border 0.2s ease;
        }

        .input-box:focus {
            border-color: var(--olive-primary);
        }

        .btn-primary {
            width: 100%;
            padding: 14px;
            background-color: #556B2F;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
            margin-top: 10px;
        }

        .btn-primary:hover {
            background-color: #435424;
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: var(--text-muted);
            margin: 20px 0;
            font-size: 0.85rem;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #DCDCCF;
        }

        .divider span {
            padding: 0 10px;
        }

        .btn-secondary {
            display: block;
            width: 100%;
            padding: 12px;
            text-align: center;
            border: 1.5px solid #556B2F;
            color: #556B2F;
            background: transparent;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            background-color: #EFEFE9;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .left-panel {
                padding: 30px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Left Banner -->
    <div class="left-panel">
        <!-- SVG Leaf Icon (Walang emoji) -->
        <svg class="logo-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M17.33 3.67c-3.12 0-6.14 1.34-8.33 3.67-2.19-2.33-5.21-3.67-8.33-3.67-.34 0-.67.03-1 .08v14.75c.33-.05.66-.08 1-.08 2.75 0 5.37 1.05 7.33 2.92 1.96-1.87 4.58-2.92 7.33-2.92.34 0 .67.03 1 .08V3.75c-.33-.05-.66-.08-1-.08zm-1.33 13.58c-2.21 0-4.32.74-6 2.05-1.68-1.31-3.79-2.05-6-2.05V5.5c1.94 0 3.82.63 5.39 1.79l.61.45.61-.45c1.57-1.16 3.45-1.79 5.39-1.79v11.75z"/>
        </svg>
        <h1>Welcome Back!</h1>
        <p>Log in to continue and manage your tasks efficiently.</p>
    </div>

    <!-- Right Form Section -->
    <div class="right-panel">

        <?php if ($error !== ""): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <?php if ($success !== ""): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <?php if ($page === "register"): ?>

            <h2>Create Account</h2>
            <p class="subtitle">Enter your details to create a new account.</p>

            <form method="POST" action="index.php?page=register">
                <input type="hidden" name="action" value="register">

                <div class="form-group">
                    <input type="text" name="username" class="input-box" placeholder="Username" minlength="3" maxlength="50" required>
                </div>

                <div class="form-group">
                    <input type="password" name="password" class="input-box" placeholder="Password (min 8 chars)" minlength="8" required>
                </div>

                <div class="form-group">
                    <input type="password" name="confirm_password" class="input-box" placeholder="Confirm Password" minlength="8" required>
                </div>

                <button type="submit" class="btn-primary">Register</button>
            </form>

            <div class="divider"><span>or</span></div>

            <a href="index.php" class="btn-secondary">Log In Existing Account</a>

        <?php else: ?>

            <h2>Login</h2>
            <p class="subtitle">Enter your credentials to access your account.</p>

            <form method="POST" action="index.php">
                <input type="hidden" name="action" value="login">

                <div class="form-group">
                    <input type="text" name="username" class="input-box" placeholder="Username" maxlength="50" required>
                </div>

                <div class="form-group">
                    <input type="password" name="password" class="input-box" placeholder="Password" required>
                </div>

                <button type="submit" class="btn-primary">Log In</button>
            </form>

            <div class="divider"><span>or</span></div>

            <a href="index.php?page=register" class="btn-secondary">Create an Account</a>

        <?php endif; ?>

    </div>
</div>

</body>
</html>