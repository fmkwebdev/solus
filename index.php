<?php
session_start();
require_once 'config/db_config.php';

$stmt = $pdo->query("
    SELECT u.id, u.username, wi.photo, wi.description,
           COALESCE(AVG(r.rating), 0) AS avg_rating,
           COUNT(r.id) AS total_ratings
    FROM users u
    INNER JOIN walkers_info wi ON u.id = wi.user_id
    LEFT JOIN ratings r ON u.id = r.walker_id
    WHERE u.is_walker = 1 AND u.is_approved = 1
    GROUP BY u.id
    ORDER BY avg_rating DESC
    LIMIT 5
");
$topRated = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("
    SELECT u.id, u.username, wi.photo, wi.description,
           COALESCE(AVG(r.rating), 0) AS avg_rating,
           COUNT(r.id) AS total_ratings
    FROM users u
    INNER JOIN walkers_info wi ON u.id = wi.user_id
    LEFT JOIN ratings r ON u.id = r.walker_id
    WHERE u.is_walker = 1 AND u.is_approved = 1
    GROUP BY u.id
    ORDER BY total_ratings DESC
    LIMIT 5
");
$topActive = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Kezdőlap – Dog Walk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">🐾 Dog Walk – Kutyasétáltató rendszer</h1>
        <div>
            <?php if (isset($_SESSION['user'])): ?>
                <a href="dashboard.php" class="btn btn-outline-success">Irányítópult</a>
                <a href="logout.php" class="btn btn-outline-danger">Kijelentkezés</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary">Bejelentkezés</a>
                <a href="register.php" class="btn btn-secondary">Regisztráció</a>
		<a href="find_walkers.php" class="btn btn-secondary">Keresés</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="mb-5">
        <h2 class="mb-3">🏅 Top 5 legjobbra értékelt sétáltató</h2><br>
        <div class="row g-3">
           <div class="row">
    <?php foreach ($topRated as $walker): ?>
        <div class="col-md-4 col-lg-3 mb-4">
            <div class="card h-100 d-flex flex-column">
<img src="assets/img/<?= htmlspecialchars($walker['photo']) ?>" class="card-img-top" alt="Profilkép" height="225">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?= htmlspecialchars($walker['username']) ?></h5>
                    <p class="card-text flex-grow-1"><?= nl2br(htmlspecialchars($walker['description'])) ?></p>
                    <div class="mt-auto">
                        <p class="mb-1"><?= number_format($walker['avg_rating'], 2) ?> ⭐ (<?= $walker['total_ratings'] ?> értékelés)</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
        </div>
    </div>

    <div>
        <h2 class="mb-3">🔁 Top 5 legaktívabb sétáltató</h2>
        <div class="row g-3">
<div class="row g-3">
    <?php foreach ($topActive as $walker): ?>
        <div class="col-md-6 col-lg-2 d-flex">
            <div class="card shadow-sm w-100 h-100 d-flex flex-column">
                <img src="assets/img/<?= htmlspecialchars($walker['photo']) ?>" class="card-img-top" alt="Profilkép" height="250" style="object-fit: cover;">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?= htmlspecialchars($walker['username']) ?></h5>
                    
                    <!-- Leírás, ami kitölti a teret -->
                    <p class="card-text small flex-grow-1"><?= nl2br(htmlspecialchars($walker['description'])) ?></p>
                    
                    <!-- Értékelés mindig alul -->
                    <p class="card-text mt-auto">
                        <strong><?= number_format($walker['avg_rating'], 2) ?> ⭐</strong>
                        (<?= $walker['total_ratings'] ?> értékelés)
                    </p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
        </div>
    </div>
</div>

</body>
</html>
