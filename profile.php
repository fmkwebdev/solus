<?php
session_start();
require_once 'config/db_config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user']['id'];
$errors = [];
$success = "";

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Hibás felhasználó.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $currentPassword = $_POST['current_password'] ?? '';

    if ($first_name === '' || $last_name === '') {
        $errors[] = "Keresztnév és vezetéknév megadása kötelező.";
    }

    if ($newPassword !== '') {
        if (strlen($newPassword) < 6) {
            $errors[] = "Az új jelszónak legalább 6 karakteresnek kell lennie.";
        }
        if (!password_verify($currentPassword, $user['password'])) {
            $errors[] = "Hibás jelenlegi jelszó.";
        }
    }

    if (empty($errors)) {
        if ($newPassword !== '') {
            $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone_number = ?, address = ?, password = ? WHERE id = ?");
            $stmt->execute([$first_name, $last_name, $phone, $address, $newHash, $userId]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone_number = ?, address = ? WHERE id = ?");
            $stmt->execute([$first_name, $last_name, $phone, $address, $userId]);
        }

        $success = "Profil sikeresen frissítve!";
        $_SESSION['user']['first_name'] = $first_name;
        $_SESSION['user']['last_name'] = $last_name;
        $_SESSION['user']['phone_number'] = $phone;
        $_SESSION['user']['address'] = $address;
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Profilom szerkesztése</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4"><a href="dashboard.php" class="btn btn-outline-dark btn-sm fs-5 text-black">←Vissza</a>
    <h2 class="mb-4">Profilom szerkesztése</h2>

    <?php foreach ($errors as $e): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label>Email (nem módosítható)</label>
            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
        </div>

        <div class="mb-3">
            <label>Keresztnév</label>
            <input type="text" name="first_name" class="form-control" required value="<?= htmlspecialchars($user['first_name']) ?>">
        </div>

        <div class="mb-3">
            <label>Vezetéknév</label>
            <input type="text" name="last_name" class="form-control" required value="<?= htmlspecialchars($user['last_name']) ?>">
        </div>

        <div class="mb-3">
            <label>Telefonszám</label>
            <input type="text" name="phone_number" class="form-control" value="<?= htmlspecialchars($user['phone_number']) ?>">
        </div>

        <div class="mb-3">
            <label>Cím</label>
            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address']) ?>">
        </div>

        <hr>
        <h5>Jelszó módosítása</h5>

        <div class="mb-3">
            <label>Jelenlegi jelszó</label>
            <input type="password" name="current_password" class="form-control">
        </div>

        <div class="mb-3">
            <label>Új jelszó</label>
            <input type="password" name="new_password" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Mentés</button>
    </form>
</div>
</body>
</html>
