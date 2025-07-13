<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user'];
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
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
