<?php
session_start();
require '../config/database.php';
/*
$sql = "SELECT id, title, category, description, resolved_at
        FROM tickets
        ORDER BY resolved_at DESC";

$stmt = $pdo->query($sql);
$tickets = $stmt->fetchAll();*/

//PAGINAÇÃO
$limit = 10; // tickets por página
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$stmt = $pdo->prepare("
    SELECT *
    FROM tickets
    ORDER BY resolved_at DESC
    LIMIT :limit OFFSET :offset
");

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
//PAGINAÇÃO
$totalTickets = $pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
$totalPages = ceil($totalTickets / $limit);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/mobile.css">
    <title>Tickets Registrados</title>
    <style>
        .actions {
            display: flex;
            gap: 10px;
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
    </style>
</head>

<body>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="toast-success" id="toast">
            <?= $_SESSION['success'] ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="container" class="table-wrapper">
        <h2>📋 Tickets Registrados</h2>
        <div class="actions">
            <a class="btn btn-info" href="add-ticket.php">➕ Registrar novo ticket</a><br>
            <a class="btn btn-primary" href="dashboard.php">📋 Ver Dashboard</a><br>

        </div>

        <table>
            <tr>
                <th>Título</th>
                <th>Categoria</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>

            <?php foreach ($tickets as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['title']) ?></td>
                    <td><?= htmlspecialchars($t['category']) ?></td>
                    <td><?= date('d/m/Y', strtotime($t['resolved_at'])) ?></td>
                    <td>
                        <button
                            type="button"
                            class="btn-edit"
                            onclick="openEditModal(
                <?= $t['id'] ?>,
                '<?= htmlspecialchars($t['title'], ENT_QUOTES) ?>',
                '<?= htmlspecialchars($t['category'], ENT_QUOTES) ?>',
                '<?= $t['resolved_at'] ?>'
            )">
                            ✏️
                        </button>
                        <button
                            class="btn-delete"
                            data-id="<?= $t['id'] ?>"
                            data-title="<?= htmlspecialchars($t['title'], ENT_QUOTES) ?>"
                            onclick="openDeleteModal(this)">
                            🗑
                        </button>

                    </td>
                </tr>

            <?php endforeach; ?>
        </table>

        <!--LOGICA PARA PAGINAÇÃO -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>">← Anterior</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>">Próxima →</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- EDITAR -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Editar Ticket</h3>

            <form method="POST" action="update-ticket.php">
                <input type="hidden" name="id" id="edit-id">

                <label>Título</label>
                <input type="text" name="title" id="edit-title" required>

                <label>Categoria</label>
                <input type="text" name="category" id="edit-category" required>

                <!-- <label>Data</label>-->
                <input type="hidden" name="resolved_at" id="edit-date" required>

                <div class="modal-actions">
                    <button type="submit">Salvar</button>
                    <button type="button" onclick="closeEditModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!--EXCLUIR-->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <h3>🗑 Excluir Ticket</h3>

            <p>
                Tem certeza que deseja excluir
                <strong id="ticketTitle"></strong>?
            </p>

            <div class="modal-actions">
                <button class="btn-secondary" onclick="closeDeleteModal()">Cancelar</button>
                <a id="confirmDeleteBtn" class="btn-danger">Excluir</a>

            </div>
        </div>
    </div>

    <script>
        function openEditModal(id, title, category, date) {
            console.log('Abrindo modal:', id); // 👈 DEBUG

            document.getElementById('edit-id').value = id;
            document.getElementById('edit-title').value = title;
            document.getElementById('edit-category').value = category;
            document.getElementById('edit-date').value = date;

            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>

    <script>
        function openDeleteModal(button) {
            const id = button.getAttribute('data-id');
            const title = button.getAttribute('data-title');

            document.getElementById('ticketTitle').textContent = title;
            document.getElementById('confirmDeleteBtn').href =
                'delete-ticket.php?id=' + id;

            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
    </script>

    <script>
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>

    <script>
        setTimeout(() => {
            const toast = document.getElementById('toast');
            if (toast) toast.remove();
        }, 3000);
    </script>
    <?php include '../includes/footer.php'; ?>

</body>

</html>