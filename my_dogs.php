<?php
session_start();
require_once 'config/db_config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user']['id'];
$errors = [];

// Új kutya hozzáadása
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $breed = trim($_POST['breed'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $age = intval($_POST['age'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if ($name === '' || $breed === '' || !in_array($gender, ['male', 'female']) || $age <= 0) {
        $errors[] = "Kérlek töltsd ki helyesen az összes mezőt!";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO dogs (user_id, name, breed, gender, age, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $name, $breed, $gender, $age, $description]);
        header("Location: my_dogs.php");
        exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM dogs WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$userId]);
$dogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Kutyáim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-4">Saját kutyáim</h2>
    <?php if (empty($dogs)): ?>
        <p>Még nincs rögzített kutyád.</p>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach ($dogs as $dog): ?>
                <li class="list-group-item">
                    <strong>Név: <?= htmlspecialchars($dog['name']) ?></strong> <br>
                    Fajta: <?= htmlspecialchars($dog['breed']) ?>,<br>
                    Nem: <?= $dog['gender'] === 'male' ? 'Hím' : 'Nőstény' ?>,<br>
                    Kor : <?= (int)$dog['age'] ?><br>
                    Leírás: <em><?= nl2br(htmlspecialchars($dog['description'])) ?></em>
                    <div class="mt-2">
                        <a href="edit_dog.php?id=<?= $dog['id'] ?>" class="btn btn-sm btn-outline-primary">✏️ Szerkesztés</a>
                        <a href="delete_dog.php?id=<?= $dog['id'] ?>" onclick="return confirm('Biztosan törlöd?')" class="btn btn-sm btn-outline-danger">🗑️ Törlés</a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>


    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>
    <br><br>

    <div class="card mb-4">
        <div class="card-header">Új kutya hozzáadása</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label>Név</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Fajta</label>
                    <input type="text" name="breed" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Nem</label><br>
                    <div class="form-check form-check-inline">
                        <input type="radio" name="gender" value="male" class="form-check-input" required id="male">
                        <label class="form-check-label" for="male">Hím</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="radio" name="gender" value="female" class="form-check-input" id="female">
                        <label class="form-check-label" for="female">Nőstény</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Életkor</label>
                    <input type="number" name="age" min="1" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Leírás</label>
                    <textarea name="description" rows="3" class="form-control"></textarea>
                </div>

                <button type="submit" class="btn btn-success">Hozzáadás</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
