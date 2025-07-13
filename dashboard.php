<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
require_once 'config/db_config.php';

$user = $_SESSION['user'];
$userId = $user['id'];

$stmt = $pdo->prepare("SELECT * FROM dogs WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$userId]);
$dogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Kezdőlap – Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-warning mb-4">
    <div class="container">
        <a class="navbar-brand fs-3" href="dashboard.php">Dog Walk</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto ">
                <li class="nav-item"><a href="find_walkers.php" class="nav-link text-white fs-5">Sétáltatók keresése</a></li>
                <li class="nav-item"><a href="my_dogs.php" class="nav-link text-white fs-5">Kutyáim</a></li>
                <li class="nav-item"><a href="profile.php" class="nav-link text-white fs-5">Profilom</a></li>
                <li class="nav-item"><a href="index.php" class="nav-link text-white fs-5">Top5</a></li>
                <?php if ($user['is_walker'] && $user['is_approved']): ?>
                    <li class="nav-item"><a href="walker_profile.php" class="nav-link text-white fs-5">Sétáltatói profil</a></li>
                <?php endif; ?>
                <?php if ($user['is_admin']): ?>
                    <li class="nav-item"><a href="admin.php" class="nav-link text-white fs-5">Admin</a></li>
                <?php endif; ?>
            </ul>
            <span class="navbar-text me-3 fs-5 text-white">@<?= htmlspecialchars($user['username']) ?></span>
            <a href="logout.php" class="btn btn-outline-light btn-sm fs-5 text-white">Kijelentkezés</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="p-4 bg-white shadow-sm rounded">
        <h2>Üdvözöllek, <?= htmlspecialchars($user['first_name'] ?? $user['username']) ?>!</h2>
        <p class="text-muted">Ez a Dog Walk irányítópultod.</p>
    </div>
<?php if (!empty($dogs)): ?>
    <div class="mt-4 p-4 bg-white shadow-sm rounded">
        <h4 class="mb-3">A kutyáid:</h4>
        <ul class="list-group">
            <?php foreach ($dogs as $dog): ?>
                <li class="list-group-item">
                    Kutya neve: <strong><?= htmlspecialchars($dog['name']) ?></strong><br> Fajta: <strong><?= htmlspecialchars($dog['breed']) ?></strong><br>
                    Nem: <strong><?= $dog['gender'] === 'male' ? 'Hím' : 'Nőstény' ?></strong><br> Kor: <strong><?= (int)$dog['age'] ?></strong>
                    <br>Leírás: <i><em><?= nl2br(htmlspecialchars($dog['description'])) ?></em></i>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php else: ?>
    <div class="mt-4 p-4 bg-white shadow-sm rounded">
        <p class="text-muted">Még nem adtál hozzá kutyát.</p>
        <a href="my_dogs.php" class="btn btn-sm btn-outline-primary mt-2">➕ Kutyát hozzáadok</a>
    </div>
<?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
