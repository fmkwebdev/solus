<?php
session_start();
require_once 'config/db_config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['is_admin'] != 1) {
    die("Hozzáférés megtagadva.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $approve = $_POST['approve'] === '1';

    $stmt = $pdo->prepare("UPDATE users SET is_approved = ? WHERE id = ?");
    $stmt->execute([$approve ? 1 : 0, $userId]);
}

$stmt = $pdo->query("SELECT * FROM users WHERE is_walker = 1 ORDER BY created_at DESC");
$walkers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Admin panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-4">Admin – Sétáltatók kezelése</h2>

    <?php if (empty($walkers)): ?>
        <div class="alert alert-info">Nincs sétáltató a rendszerben.</div>
    <?php else: ?>
        <table class="table table-bordered table-hover bg-white shadow-sm">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Felhasználónév</th>
                    <th>Email</th>
                    <th>Regisztráció ideje</th>
                    <th>Státusz</th>
                    <th>Művelet</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($walkers as $w): ?>
                    <tr>
                        <td><?= $w['id'] ?></td>
                        <td><?= htmlspecialchars($w['username']) ?></td>
                        <td><?= htmlspecialchars($w['email']) ?></td>
                        <td><?= $w['created_at'] ?></td>
                        <td>
                            <?= $w['is_approved'] ? '<span class="badge bg-success">Jóváhagyva</span>' : '<span class="badge bg-warning text-dark">Függőben</span>' ?>
                        </td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="user_id" value="<?= $w['id'] ?>">
                                <input type="hidden" name="approve" value="<?= $w['is_approved'] ? '0' : '1' ?>">
                                <button type="submit" class="btn btn-sm <?= $w['is_approved'] ? 'btn-danger' : 'btn-success' ?>">
                                    <?= $w['is_approved'] ? 'Visszavonás' : 'Jóváhagyás' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
