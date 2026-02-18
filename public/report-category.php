<?php
require '../config/database.php';

$sql = "
    SELECT 
        category,
        COUNT(*) AS total
    FROM tickets
    GROUP BY category
    ORDER BY total DESC
";

$stmt = $pdo->query($sql);
$reports = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório por Categoria</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
        }

        .container {
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border-bottom: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        th {
            background: #8e44ad;
            color: #fff;
        }

        tr:hover {
            background: #f1f1f1;
        }

        .total {
            font-weight: bold;
        }

        a {
            display: inline-block;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>🏷️ Relatório de Tickets por Categoria</h2>
        <a href="tickets.php">⬅ Voltar para tickets</a>
        <table>
            <thead>
                <tr>
                    <th>Categoria</th>
                    <th>Total de Tickets</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($reports) > 0): ?>
                    <?php foreach ($reports as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['category']) ?></td>
                            <td class="total"><?= $row['total'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2">Nenhum dado encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>

</html>