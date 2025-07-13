<?php
require_once 'config/db_config.php';

$token = $_GET['code'] ?? '';
$success = false;

if ($token) {
    $stmt = $pdo->prepare("UPDATE users SET is_active = 1, activation_code = NULL WHERE activation_code = ? AND is_active = 0");
    $stmt->execute([$token]);

    if ($stmt->rowCount()) {
        $success = true;
    }
}
?>


<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Fiók aktiválás</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5 text-center">
    <?php if ($success): ?>
        <div class="alert alert-success">
            ✅ A fiók sikeresen aktiválva lett!<br>
            <a href="login.php" class="btn btn-sm btn-outline-success mt-3">Bejelentkezés</a>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            ❌ Az aktiválás nem sikerült. A link lehet, hogy hibás vagy lejárt.
        </div>
    <?php endif; ?>
</div>
</body>
</html>
