<?php
require '../config/database.php';
require '../vendor/autoload.php';

use Dompdf\Dompdf;

/* =========================
   FILTRO DE MÊS
========================= */

$selectedMonth = $_GET['month'] ?? date('Y-m');
$startDate = $selectedMonth . '-01';
$endDate = date('Y-m-t', strtotime($startDate));

/* =========================
   DADOS DO RELATÓRIO
========================= */

// Total do mês
$sqlMonth = "
    SELECT COUNT(*) 
    FROM tickets
    WHERE resolved_at BETWEEN ? AND ?
";
$stmtMonth = $pdo->prepare($sqlMonth);
$stmtMonth->execute([$startDate, $endDate]);
$totalMonth = $stmtMonth->fetchColumn();

// Por dia
$sqlDay = "
    SELECT DATE(resolved_at) AS dia, COUNT(*) AS total
    FROM tickets
    WHERE resolved_at BETWEEN ? AND ?
    GROUP BY DATE(resolved_at)
    ORDER BY dia ASC
";
$stmtDay = $pdo->prepare($sqlDay);
$stmtDay->execute([$startDate, $endDate]);
$days = $stmtDay->fetchAll();

// Por categoria
$sqlCategory = "
    SELECT category, COUNT(*) AS total
    FROM tickets
    WHERE resolved_at BETWEEN ? AND ?
    GROUP BY category
    ORDER BY total DESC
";
$stmtCat = $pdo->prepare($sqlCategory);
$stmtCat->execute([$startDate, $endDate]);
$categories = $stmtCat->fetchAll();

/* =========================
   HTML DO PDF
========================= */
$html = "
<style>
@page {
    margin: 60px 40px;
}

footer {
    position: fixed;
    bottom: -30px;
    left: 0;
    right: 0;
    height: 30px;

    text-align: center;
    font-size: 10px;
    color: #777;
}

body { font-family: Arial, sans-serif; }
h2 { text-align: center; }
h3 { margin-top: 30px; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
th { background: #f2f2f2; }
.total { font-weight: bold; }
</style>

<h2>Relatório de Tickets</h2>
<p><strong>Mês:</strong> " . date('m/Y', strtotime($startDate)) . "</p>
<p><strong>Total de tickets:</strong> $totalMonth</p>

<h3>Tickets por dia</h3>
<table>
<tr><th>Dia</th><th>Total</th></tr>

<footer>
    Relatório gerado por Sônia Muniz · " . date('d/m/Y') . "
</footer>
";

foreach ($days as $d) {
    $html .= "<tr>
        <td>" . date('d/m/Y', strtotime($d['dia'])) . "</td>
        <td>{$d['total']}</td>
    </tr>";
}

$html .= "
</table>

<h3>Tickets por categoria</h3>
<table>
<tr><th>Categoria</th><th>Total</th></tr>
";

foreach ($categories as $c) {
    $html .= "<tr>
        <td>{$c['category']}</td>
        <td>{$c['total']}</td>
    </tr>";
}

$html .= "</table>";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("relatorio_tickets_" . $selectedMonth . ".pdf", [
    "Attachment" => true
]);
