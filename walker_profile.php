<?php
session_start();
require_once 'config/db_config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['is_walker'] != 1 || $_SESSION['user']['is_approved'] != 1) {
    die("Hozzáférés megtagadva. <a href='login.php'>Bejelentkezés</a>");
}

$userId = $_SESSION['user']['id'];
$errors = [];
$success = "";

$targetDir = "assets/img/";
$photoName = "";

// Meglévő adat betöltése
$stmt = $pdo->prepare("SELECT * FROM walkers_info WHERE user_id = ?");
$stmt->execute([$userId]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

// Mentés
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');
    $favorite = trim($_POST['favorite_breed'] ?? '');

    if ($description === '' || $favorite === '') {
        $errors[] = "Minden mező kitöltése kötelező.";
    }

    // Fájl feltöltés
    if (!empty($_FILES['photo']['name'])) {
        $fileTmp = $_FILES['photo']['tmp_name'];
        $fileName = basename($_FILES['photo']['name']);
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($ext, $allowed)) {
            $errors[] = "Csak JPG, PNG vagy GIF képet tölthetsz fel.";
        } else {
            $photoName = uniqid() . '.' . $ext;
            move_uploaded_file($fileTmp, $targetDir . $photoName);
        }
    } else {
        $photoName = $existing['photo'] ?? '';
    }

    if (empty($errors)) {
        if ($existing) {
            $stmt = $pdo->prepare("UPDATE walkers_info SET description = ?, favorite_breed = ?, photo = ? WHERE user_id = ?");
            $stmt->execute([$description, $favorite, $photoName, $userId]);
            $success = "Profil frissítve.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO walkers_info (user_id, description, favorite_breed, photo) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $description, $favorite, $photoName]);
            $success = "Profil létrehozva.";
        }

        // Frissítsd a megjelenített adatokat
        $stmt = $pdo->prepare("SELECT * FROM walkers_info WHERE user_id = ?");
        $stmt->execute([$userId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Sétáltatói profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <h2 class="mb-4">Sétáltatói profil</h2>

    <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
    <?php endforeach; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label>Leírás magadról</label>
            <textarea name="description" rows="4" class="form-control" required><?= htmlspecialchars($existing['description'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
            <label>Kedvenc kutyafajta</label>
            <input type="text" name="favorite_breed" class="form-control" required value="<?= htmlspecialchars($existing['favorite_breed'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label>Fénykép (opcionális)</label>
            <input type="file" name="photo" class="form-control" accept="image/*" width="200" height="200">
        </div>

        <?php if (!empty($existing['photo'])): ?>
            <div class="mb-3">
                <img src="assets/img/<?= htmlspecialchars($existing['photo']) ?>" alt="Profilkép" class="img-thumbnail" width="150" height="150">
            </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-success">Mentés</button>
    </form>
</div>
</body>
</html>
