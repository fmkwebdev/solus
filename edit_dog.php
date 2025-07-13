<?php
session_start();
require_once 'config/db_config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user']['id'];
$dogId = (int)($_GET['id'] ?? 0);

// Lekérdezzük a kutyát
$stmt = $pdo->prepare("SELECT * FROM dogs WHERE id = ? AND user_id = ?");
$stmt->execute([$dogId, $userId]);
$dog = $stmt->fetch();

if (!$dog) {
    die("A kutya nem található vagy nem szerkeszthető.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $breed = trim($_POST['breed'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $age = (int)($_POST['age'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if ($name && $breed && in_array($gender, ['male', 'female']) && $age > 0) {
        $stmt = $pdo->prepare("UPDATE dogs SET name = ?, breed = ?, gender = ?, age = ?, description = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$name, $breed, $gender, $age, $description, $dogId, $userId]);
        $success = "A kutya adatai frissítve.";
        // újra lekérjük frissítve
        $stmt = $pdo->prepare("SELECT * FROM dogs WHERE id = ? AND user_id = ?");
        $stmt->execute([$dogId, $userId]);
        $dog = $stmt->fetch();
    } else {
        $error = "Minden mezőt kötelező kitölteni helyesen.";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Kutya szerkesztése</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">🐾 Kutya adatainak szerkesztése</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" class="card p-4 shadow-sm" style="max-width: 600px;">
        <div class="mb-3">
            <label class="form-label">Kutya neve</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($dog['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Fajta</label>
            <input type="text" name="breed" class="form-control" value="<?= htmlspecialchars($dog['breed']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Nem</label>
            <select name="gender" class="form-select" required>
                <option value="male" <?= $dog['gender'] === 'male' ? 'selected' : '' ?>>Hím</option>
                <option value="female" <?= $dog['gender'] === 'female' ? 'selected' : '' ?>>Nőstény</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Életkor</label>
            <input type="number" name="age" class="form-control" min="1" value="<?= $dog['age'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Leírás</label>
            <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($dog['description']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Mentés</button>

    </form>
    <a href="my_dogs.php" class="btn btn-secondary">Vissza</a>
</div>
</body>
</html>
