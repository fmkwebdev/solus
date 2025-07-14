<?php
require_once 'config/db_config.php';
require_once 'classes/User.php';
require_once 'config/bootstrap.php';


use PHPMailer\PHPMailer\PHPMailer;

$user = new User($pdo);
$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $isWalker = isset($_POST['is_walker']) && $_POST['is_walker'] === '1';

    if ($user->register($email, $username, $password, $isWalker)) {
        $success = "Sikeres regisztráció! Nézd meg az emailed a megerősítő linkért.";
    } else {
        $error = "Az email vagy a felhasználónév már létezik.";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Regisztráció</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="mb-4">Regisztráció</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="w-50">
            <div class="mb-3">
                <label>Email cím</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Felhasználónév</label>
                <input type="text" name="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Jelszó</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" name="is_walker" value="1" class="form-check-input" id="walkerCheck">
                <label class="form-check-label" for="walkerCheck">Szeretnék kutyasétáltató lenni</label>
            </div>

            <button type="submit" class="btn btn-success">Regisztráció</button>
            <a href="login.php" class="btn btn-link">Már van fiókod?</a>
        </form>
    </div>
</body>
</html>
