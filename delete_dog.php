<?php
session_start();
require_once 'config/db_config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user']['id'];
$dogId = (int)($_GET['id'] ?? 0);

// Ellenőrzés és törlés
$stmt = $pdo->prepare("DELETE FROM dogs WHERE id = ? AND user_id = ?");
$stmt->execute([$dogId, $userId]);

header("Location: my_dogs.php");
exit;
