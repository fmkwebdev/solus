<?php
session_start();
require_once 'config/db_config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user']['id'];
$walkerId = (int)($_GET['id'] ?? 0);

// Lekérdezzük a sétáltató adatait
$stmt = $pdo->prepare("
    SELECT u.username, u.email, wi.description, wi.favorite_breed, wi.photo,
           COALESCE(AVG(r.rating), 0) AS avg_rating, COUNT(r.id) AS total_ratings
    FROM users u
    INNER JOIN walkers_info wi ON wi.user_id = u.id
    LEFT JOIN ratings r ON r.walker_id = u.id
    WHERE u.id = ? AND u.is_walker = 1 AND u.is_approved = 1
    GROUP BY u.id
");
$stmt->execute([$walkerId]);
$walker = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$walker) {
    die("A sétáltató nem található vagy nincs jóváhagyva.");
}

// Ellenőrzés: már értékelt?
$stmt = $pdo->prepare("SELECT rating FROM ratings WHERE user_id = ? AND walker_id = ?");
$stmt->execute([$userId, $walkerId]);
$existingRating = $stmt->fetchColumn();

// Új értékelés leadása
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$existingRating) {
    $rating = (int)($_POST['rating'] ?? 0);
    if ($rating >= 1 && $rating <= 5) {
        $stmt = $pdo->prepare("INSERT INTO ratings (user_id, walker_id, rating) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $walkerId, $rating]);
        header("Location: walkers.php?id=" . $walkerId);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($walker['username']) ?> értékelése</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-4"><?= htmlspecialchars($walker['username']) ?> értékelése</h2>

    <div class="card mb-4">
        <div class="row g-0">
            <div class="col-md-4">
                <img src="assets/img/<?= htmlspecialchars($walker['photo']) ?>" class="img-fluid rounded-start" alt="Profilkép">
            </div>
            <div class="col-md-8">
                <div class="card-body">
                    <p class="card-text"><strong>Email:</strong> <?= htmlspecialchars($walker['email']) ?></p>
                    <p class="card-text"><strong>Kedvenc fajta:</strong> <?= htmlspecialchars($walker['favorite_breed']) ?></p>
                    <p class="card-text"><strong>Leírás:</strong><br><?= nl2br(htmlspecialchars($walker['description'])) ?></p>
                    <p class="card-text">
                        <strong>Átlag értékelés:</strong> <?= number_format($walker['avg_rating'], 2) ?> ⭐
                        (<?= $walker['total_ratings'] ?> értékelés)
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$existingRating): ?>
        <form method="POST" class="card p-4 shadow-sm">
            <h5>Értékeld a sétáltatót (1–5):</h5>
            <select name="rating" class="form-select mb-3" required>
                <option value="">Válassz értékelést</option>
                <option value="5">5 – Kiváló</option>
                <option value="4">4 – Jó</option>
                <option value="3">3 – Átlagos</option>
                <option value="2">2 – Gyenge</option>
                <option value="1">1 – Rossz</option>
            </select>
            <button type="submit" class="btn btn-primary">Értékelés mentése</button>
        </form>
    <?php else: ?>
        <div class="alert alert-info">Már értékelted ezt a sétáltatót <?= $existingRating ?> ⭐ értékeléssel.</div>
    <?php endif; ?>
</div>
</body>
</html>
