<?php
session_start();

if (!isset($_SESSION["user_id"], $_SESSION["role"])) {
    header("Location: index.php");
    exit;
}

if ($_SESSION["role"] !== "user") {
    header("Location: home2.php");
    exit;
}

if (isset($_GET["logout"])) {
    $_SESSION = [];
    session_destroy();

    header("Location: index.php");
    exit;
}

$username = htmlspecialchars(
    $_SESSION["username"],
    ENT_QUOTES,
    "UTF-8"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Homepage</title>
    <style>
        :root {
            --olive-dark: #556B2F;
            --olive-primary: #6B8E23;
            --bg-olive: #4F5D2F;
            --bg-card: #F7F7F2;
            --text-dark: #2B3A1A;
            --text-muted: #667055;
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

        .dashboard-container {
            width: 500px;
            max-width: 100%;
            background: var(--bg-card);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            text-align: center;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            margin: 0 auto 15px auto;
            fill: #556B2F;
        }

        h1 {
            font-size: 1.8rem;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        p {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 8px;
        }

        .role-badge {
            display: inline-block;
            background-color: #EFEFE9;
            color: var(--olive-dark);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin: 15px 0 25px 0;
            border: 1px solid #DCDCCF;
        }

        .btn-logout {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #a93226;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s ease;
        }

        .btn-logout:hover {
            background-color: #922b21;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <svg class="logo-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M17.33 3.67c-3.12 0-6.14 1.34-8.33 3.67-2.19-2.33-5.21-3.67-8.33-3.67-.34 0-.67.03-1 .08v14.75c.33-.05.66-.08 1-.08 2.75 0 5.37 1.05 7.33 2.92 1.96-1.87 4.58-2.92 7.33-2.92.34 0 .67.03 1 .08V3.75c-.33-.05-.66-.08-1-.08zm-1.33 13.58c-2.21 0-4.32.74-6 2.05-1.68-1.31-3.79-2.05-6-2.05V5.5c1.94 0 3.82.63 5.39 1.79l.61.45.61-.45c1.57-1.16 3.45-1.79 5.39-1.79v11.75z"/>
    </svg>
    
    <h1>Welcome, <?= $username ?>!</h1>
    <p>You are logged in as a regular user.</p>
    
    <div>
        <span class="role-badge">Role: user</span>
    </div>

    <a href="home.php?logout=1" class="btn-logout">Logout</a>
</div>

</body>
</html>