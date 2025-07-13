<?php
require_once 'config/db_config.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?)");
        $stmt->execute([$email, $token]);

        $resetLink = "http://localhost/WP/reset_password.php?token=$token";

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'fmkwebdev@gmail.com'; // saját Gmail
        $mail->Password = 'tzga xndw snub tupn';     // app-jelszó
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('sajat.cimed@gmail.com', 'Dog Walk');
        $mail->addAddress($email);
        $mail->Subject = 'Jelszó visszaállítás';
        $mail->Body = "Kattints a linkre a jelszavad visszaállításához: $resetLink";

        if ($mail->send()) {
            $success = "E-mail elküldve. Nézd meg a postaládád.";
        } else {
            $error = "Hiba történt az e-mail küldésekor.";
        }
    } else {
        $error = "Nem található felhasználó ezzel az e-mail címmel.";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Elfelejtett jelszó</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">🔐 Elfelejtett jelszó</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="card p-4 shadow-sm" style="max-width: 500px;">
        <div class="mb-3">
            <label class="form-label">E-mail cím</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Jelszó visszaállító link küldése</button>
    </form>
</div>
</body>
</html>
