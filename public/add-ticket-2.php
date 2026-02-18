<?php require '../config/database.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $description = $_POST['description'] ?? null;
    $dataEU = $_POST['resolved_at'];

    $date = DateTime::createFromFormat('Y/m/d', $dataEU);

    if (!$date) {
        die('Data inválida');
    }
    $resolved_at = $date->format('Y-m-d'); // MySQL

    $sql = "INSERT INTO tickets (title, category, description, resolved_at)
            VALUES (?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $title,
        $category,
        $description,
        $resolved_at
    ]);

    $success = true;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registrar Ticket Resolvido</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
        }

        .container {
            max-width: 500px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
        }

        input,
        textarea,
        select,
        button {
            width: 100%;
            margin-top: 10px;
            padding: 8px;
        }

        button {
            background: #2e86de;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        button:hover {
            opacity: 0.9;
        }

        .btn {
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            color: white;
        }

        .btn-primary {
            background: #1976d2;
        }

        /* FOOTER */
        .footer {
            margin-top: 280px;
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
        <a class="btn btn-primary" href="dashboard.php">📋 Ver Dashboard</a><br>
        <h2>🎫 Registrar Ticket Resolvido</h2>
        <?php if (!empty($success)): ?>
            <p style="color: green;">✅ Ticket registrado com sucesso!</p>
        <?php endif; ?>

        <form method="POST">
            <label>Título</label>
            <input type="text" name="title" required>

            <label>Categoria</label>
            <select name="category" required>
                <option value="">Selecione</option>
                <option value="SCLINICO-CSP-TÉCNICO">SCLINICO-CSP-TÉCNICO</option>
                <option value="CTH-TÉCNICO">CTH-TÉCNICO</option>
            </select>

            <label>Descrição (opcional)</label>
            <textarea name="description"></textarea>

            <label>Data de resolução</label>
            <!-- <input type="datetime-local" name="resolved_at" required>-->
            <input
                type="text"
                name="resolved_at"
                id="resolved_at"
                placeholder="yyyy/mm/dd"
                required>


            <button type="submit">Salvar</button>
        </form>
    </div>

    <script>
        document.getElementById('resolved_at').addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, '').slice(0, 8);

            if (v.length >= 5)
                e.target.value = v.replace(/(\d{4})(\d{2})(\d+)/, '$1/$2/$3');
            else if (v.length >= 3)
                e.target.value = v.replace(/(\d{4})(\d+)/, '$1/$2');
            else
                e.target.value = v;
        });
    </script>
    <?php include '../includes/footer.php'; ?>

</body>

</html>