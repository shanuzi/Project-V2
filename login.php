    <?php

    if (session_status() === PHP_SESSION_NONE) session_start();

    require_once 'config/db.php'; // Uses the $pdo object

    $error = '';

    // ---- Handle login POST submission --------------------------
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $error = 'Please enter both username and password.';
        } else {
            try {
                /* Look up the user by username using PDO */
                $sql  = "SELECT * FROM users WHERE user_name = ? LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$username]);
                $user = $stmt->fetch(); // This returns an associative array or false

                if ($user && $user['user_password'] === hash('sha256', $password)) {
                    
                    /* Store user info in session */
                    $_SESSION['user_id']   = $user['user_id'];
                    $_SESSION['user_name'] = $user['user_name'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['user_role'] = $user['user_role'];

                    header('Location: index.php');
                    exit;   
                } else {
                    $error = 'Invalid username or password.';
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }

    // If already logged in, skip the login page
    if (!empty($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Login – USJ-R SMS</title>
        <link rel="stylesheet" href="assets/style.css">
    </head>
    <body class="login-page">

    <div class="login-card">
        <h1>USJ-R School Management System</h1>
        <p class="subtitle">V1.01 &nbsp;–&nbsp; Please sign in to continue</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    autocomplete="username" autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-green btn-login">Login</button>
        </form>
    </div>

    </body>
    </html>