<?php session_start(); ?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Sétáltatók keresése</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4"><a href="dashboard.php" class="btn btn-outline-dark btn-sm fs-5 text-black">←Vissza</a>
    <h2 class="mb-4">Sétáltatók keresése</h2>

    <div class="mb-4">
        <input type="text" id="searchInput" class="form-control" placeholder="Keresés név vagy fajta alapján...">
    </div>

    <div id="resultsContainer" class="row g-3">
        <!-- Itt jelennek meg a találatok AJAX-al -->
    </div>
</div>

<script src="assets/js/script.js"></script>
</body>
</html>
