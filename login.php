<?php
require_once 'config/db_config.php';
require_once 'classes/User.php';
session_start();

$user = new User($pdo);
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $loggedInUser = $user->login($email, $password);

    if ($loggedInUser) {
        $_SESSION['user'] = $loggedInUser;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Hibás email vagy jelszó.";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Bejelentkezés</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="mb-4">Bejelentkezés</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="w-50">
            <div class="mb-3">
                <label>Email cím</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Jelszó</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Belépés</button><br><br>
<a href="register.php" class="btn btn-success">Regisztáció</a> <br><br>
            <a href="forgot_password.php" class="btn btn-danger">Elfelejtett jelszó</a>
  

</form>
    </div>
</body>
</html>
