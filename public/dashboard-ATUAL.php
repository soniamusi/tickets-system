<?php
require '../config/database.php';

$selectedMonth = $_GET['month'] ?? date('Y-m');

$startDate = $selectedMonth . '-01';
$endDate = date('Y-m-t', strtotime($startDate));


// Tickets hoje
$sqlToday = "
    SELECT COUNT(*) 
    FROM tickets 
    WHERE DATE(resolved_at) = CURDATE()
";
$ticketsToday = $pdo->query($sqlToday)->fetchColumn();

// Tickets mês atual
$sqlMonth = "
    SELECT COUNT(*) 
    FROM tickets 
    WHERE MONTH(resolved_at) = MONTH(CURDATE())
    AND YEAR(resolved_at) = YEAR(CURDATE())
";
$ticketsMonth = $pdo->query($sqlMonth)->fetchColumn();

// Total geral
$sqlTotal = "SELECT COUNT(*) FROM tickets";
$ticketsTotal = $pdo->query($sqlTotal)->fetchColumn();

// Dados para gráfico (por dia)
$sqlChart = "
    SELECT DATE(resolved_at) AS dia, COUNT(*) AS total
    FROM tickets
    GROUP BY DATE(resolved_at)
    ORDER BY dia ASC
";
$stmt = $pdo->query($sqlChart);
$chartData = $stmt->fetchAll();

$labels = [];
$values = [];

foreach ($chartData as $row) {
    $labels[] = date('d/m', strtotime($row['dia']));
    $values[] = $row['total'];
}

// Dados para gráfico por categoria
$sqlCategory = "
    SELECT category, COUNT(*) AS total
    FROM tickets
    GROUP BY category
    ORDER BY total DESC
";

$stmtCat = $pdo->query($sqlCategory);
$categoryData = $stmtCat->fetchAll();

$catLabels = [];
$catValues = [];

foreach ($categoryData as $row) {
    $catLabels[] = $row['category'];
    $catValues[] = $row['total'];
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
        }

        .cards {
            display: flex;
            gap: 20px;
        }

        .card {
            flex: 1;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .card h3 {
            margin-bottom: 10px;
            color: #555;
        }

        .card span {
            font-size: 32px;
            font-weight: bold;
        }

        .chart {
            margin-top: 40px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
        }

        a {
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>📊 Dashboard de Produtividade</h2>
        <form method="GET" style="margin-bottom:20px;">
            <label>Mês:</label>
            <input type="month" name="month" value="<?= $_GET['month'] ?? date('Y-m') ?>">
            <button type="submit">Filtrar</button>
        </form>

        <a href="tickets.php">📋 Ver tickets</a>

        <div class="cards">
            <div class="card">
                <h3>Hoje</h3>
                <span><?= $ticketsToday ?></span>
            </div>

            <div class="card">
                <h3>Este mês</h3>
                <span><?= $ticketsMonth ?></span>
            </div>

            <div class="card">
                <h3>Total</h3>
                <span><?= $ticketsTotal ?></span>
            </div>
        </div>

        <div class="chart">
            <h3>Tickets por dia</h3>
            <canvas id="ticketsChart"></canvas>
        </div>

        <div class="chart">
            <h3>Tickets por Categoria</h3>
            <canvas id="categoryChart"></canvas>
        </div>

    </div>

    <script>
        const ctx = document.getElementById('ticketsChart');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Tickets resolvidos',
                    data: <?= json_encode($values) ?>,
                    fill: false,
                    tension: 0.3
                }]
            }
        });
    </script>

    <script>
        const ctxCategory = document.getElementById('categoryChart');

        new Chart(ctxCategory, {
            type: 'pie',
            data: {
                labels: <?= json_encode($catLabels) ?>,
                datasets: [{
                    data: <?= json_encode($catValues) ?>
                }]
            }
        });
    </script>


</body>

</html>