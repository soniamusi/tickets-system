<?php
require '../config/database.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: tickets.php');
    exit;
}

// Buscar ticket
$sql = "SELECT * FROM tickets WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    header('Location: tickets.php');
    exit;
}

// Atualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "
        UPDATE tickets
        SET title = ?, category = ?, resolved_at = ?
        WHERE id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['title'],
        $_POST['category'],
        $_POST['resolved_at'],
        $id
    ]);

    header('Location: tickets.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Tickets Registrados</title>
</head>

<body>
    <h2>Editar Ticket</h2>
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Editar Ticket</h3>
            <form method="POST" action="update-ticket.php">
                <input type="hidden" name="id" id="edit-id">

                <label>Título</label>
                <input type="text" name="title" id="edit-title" required>

                <label>Categoria</label>
                <input type="text" name="category" id="edit-category" required>

                <label>Data</label>
                <input type="hidden" name="resolved_at" id="edit-date" required>

                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>