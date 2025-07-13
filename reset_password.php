<?php
require_once 'config/db_config.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $newPassword = $_POST['password'] ?? '';

    if (strlen($newPassword) < 6) {
        $error = "A jelszónak legalább 6 karakter hosszúnak kell lennie.";
    } else {
        // Lekérjük az emailt a token alapján
        $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND created_at > NOW() - INTERVAL 1 HOUR");
        $stmt->execute([$token]);
        $email = $stmt->fetchColumn();

        if ($email) {
            // Frissítjük a jelszót
            $hash = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$hash, $email]);

            // Töröljük a reset tokent
            $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

            $success = "A jelszó sikeresen megváltozott. <a href='login.php'>Bejelentkezés</a>";
        } else {
            $error = "A jelszó-visszaállítási link érvénytelen vagy lejárt.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Jelszó visszaállítása</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">🔐 Jelszó visszaállítása</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php else: ?>
        <form method="POST" class="card p-4 shadow-sm w-100" style="max-width: 500px;">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div class="mb-3">
                <label for="password" class="form-label">Új jelszó</label>
                <input type="password" name="password" id="password" class="form-control" required minlength="6">
            </div>

            <button type="submit" class="btn btn-primary">Jelszó mentése</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
