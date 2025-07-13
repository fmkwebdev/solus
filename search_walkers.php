<?php
require_once 'config/db_config.php';

$search = trim($_GET['search'] ?? '');
$params = [];

$sql = "
    SELECT u.id, u.username, wi.description, wi.favorite_breed, wi.photo,
           COALESCE(AVG(r.rating), 0) AS avg_rating,
           COUNT(r.id) AS total_ratings
    FROM users u
    INNER JOIN walkers_info wi ON wi.user_id = u.id
    LEFT JOIN ratings r ON r.walker_id = u.id
    WHERE u.is_walker = 1 AND u.is_approved = 1
";

if ($search !== '') {
    $sql .= " AND (u.username LIKE :search1 OR wi.favorite_breed LIKE :search2)";
    $params['search1'] = '%' . $search . '%';
    $params['search2'] = '%' . $search . '%';
}

$sql .= " GROUP BY u.id ORDER BY avg_rating DESC";

$stmt = $pdo->prepare($sql);


if (!empty($params)) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}

$walkers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (empty($walkers)): ?>
    <div class="col-12">
        <div class="alert alert-warning">Nincs találat.</div>
    </div>
<?php else: ?>
    <?php foreach ($walkers as $w): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm">
                <img src="assets/img/<?= htmlspecialchars($w['photo']) ?>" class="card-img-top" alt="Profilkép"  height="250">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($w['username']) ?></h5>
                    <p class="card-text"><?= nl2br(htmlspecialchars($w['description'])) ?></p>
                    <p class="card-text"><strong>Kedvenc fajta:</strong> <?= htmlspecialchars($w['favorite_breed']) ?></p>
                    <p><strong><?= number_format($w['avg_rating'], 2) ?> ⭐</strong> (<?= $w['total_ratings'] ?> értékelés)</p>
                    <a href="walkers.php?id=<?= $w['id'] ?>" class="btn btn-sm btn-outline-primary">Profil megtekintése</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
