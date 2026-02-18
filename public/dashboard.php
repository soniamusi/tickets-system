<?php
require '../config/database.php';

/* =========================
   FILTRO DE MÊS
========================= */
$selectedMonth = $_GET['month'] ?? date('Y-m');
$startDate = $selectedMonth . '-01';
$endDate = date('Y-m-t', strtotime($startDate));

/* =========================
   CARDS
========================= */

// Tickets HOJE
$sqlToday = "
    SELECT COUNT(*) 
    FROM tickets 
    WHERE DATE(resolved_at) = CURDATE()
";
$ticketsToday = $pdo->query($sqlToday)->fetchColumn();

// Tickets do MÊS selecionado
$sqlMonth = "
    SELECT COUNT(*) 
    FROM tickets
    WHERE resolved_at BETWEEN ? AND ?
";
$stmtMonth = $pdo->prepare($sqlMonth);
$stmtMonth->execute([$startDate, $endDate]);
$ticketsMonth = $stmtMonth->fetchColumn();

// Total geral
$sqlTotal = "SELECT COUNT(*) FROM tickets";
$ticketsTotal = $pdo->query($sqlTotal)->fetchColumn();

/* =========================
   GRÁFICO POR DIA (MÊS)
========================= */
$sqlChartDay = "
    SELECT DATE(resolved_at) AS dia, COUNT(*) AS total
    FROM tickets
    WHERE resolved_at BETWEEN ? AND ?
    GROUP BY DATE(resolved_at)
    ORDER BY dia ASC
";
$stmtDay = $pdo->prepare($sqlChartDay);
$stmtDay->execute([$startDate, $endDate]);
$chartDayData = $stmtDay->fetchAll();

$dayLabels = [];
$dayValues = [];

foreach ($chartDayData as $row) {
    $dayLabels[] = date('d/m', strtotime($row['dia']));
    $dayValues[] = $row['total'];
}

/* =========================
   GRÁFICO POR CATEGORIA (MÊS)
========================= */
$sqlChartCategory = "
    SELECT category, COUNT(*) AS total
    FROM tickets
    WHERE resolved_at BETWEEN ? AND ?
    GROUP BY category
    ORDER BY total DESC
";
$stmtCategory = $pdo->prepare($sqlChartCategory);
$stmtCategory->execute([$startDate, $endDate]);
$chartCategoryData = $stmtCategory->fetchAll();

$catLabels = [];
$catValues = [];

foreach ($chartCategoryData as $row) {
    $catLabels[] = $row['category'];
    $catValues[] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Dashboard de Tickets</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/mobile.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* FOOTER */
        .footer {
            margin-top: 40px;
            padding: 15px 0;
            background: #f5f5f5;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }

        .footer-content {
            max-width: 1100px;
            margin: auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        @media (max-width: 600px) {
            .footer-content {
                flex-direction: column;
                gap: 6px;
                text-align: center;
            }
        }
    </style>


</head>

<body>

    <div class="container">
        <header class="header">
            <h1>📊 Dashboard de Tickets</h1>
            <p>Relatórios de produtividade</p>
        </header>
        <!--
        <a href="tickets.php">📋 Ver tickets</a><br>
        <a href="add-ticket.php">➕ Registrar novo ticket</a><br>
        <a href="export-pdf.php?month=<?= $selectedMonth ?>" target="_blank">
            📄 Exportar PDF
        </a>
        -->
        <div class="toolbar">
            <form method="GET" class="filter">
                <input type="month" name="month" value="<?= $selectedMonth ?>">
                <button>Filtrar</button>
            </form>

            <div class="actions d-none d-md-flex">
                <a class="btn btn-primary" href="tickets.php">👀 Ver Tickets</a> <br>
                <a class="btn btn-info" href="add-ticket.php">➕ Registrar novo ticket</a><br>
                <a class="btn btn-danger" href="export-pdf.php?month=<?= $selectedMonth ?>" target="_blank">📄Gerar PDF</a>
            </div>
        </div>
        <div class="col-sm-12 d-block d-md-none">
            <div class="actions">
                <a class="btn btn-primary" href="tickets.php">👀 Ver Tickets</a><br>
                <a class="btn btn-info" href="add-ticket.php">➕ Registrar novo ticket</a><br>
                <a class="btn btn-danger" href="export-pdf.php?month=<?= $selectedMonth ?>" target="_blank">📄 Gerar PDF</a>
            </div>
        </div>


        <!-- Filtro de mês 
        <form method="GET">
            <label>Mês:</label>
            <input type="month" name="month" value="<?= $selectedMonth ?>">
            <button type="submit">Filtrar</button>
        </form> -->

        <!-- Cards -->
        <div class="cards">
            <div class="card">
                <h3>Hoje</h3>
                <span><?= $ticketsToday ?></span>
            </div>
            <div class="card">
                <h3>Mês selecionado</h3>
                <span><?= $ticketsMonth ?></span>
            </div>
            <div class="card">
                <h3>Total geral</h3>
                <span><?= $ticketsTotal ?></span>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="charts">
            <div class="chart-box">
                <h3>Tickets por dia</h3>
                <canvas id="chartDay"></canvas>
            </div>

            <div class="chart-box">
                <h3>Tickets por categoria</h3>
                <canvas id="chartCategory"></canvas>
            </div>
        </div>
    </div>

    <script>
        const dayCtx = document.getElementById('chartDay');
        new Chart(dayCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($dayLabels) ?>,
                datasets: [{
                    label: 'Tickets',
                    data: <?= json_encode($dayValues) ?>,
                    tension: 0.3
                }]
            }
        });

        const catCtx = document.getElementById('chartCategory');
        new Chart(catCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode($catLabels) ?>,
                datasets: [{
                    data: <?= json_encode($catValues) ?>
                }]
            }
        });
    </script>
    <?php include '../includes/footer.php'; ?>

</body>

</html>