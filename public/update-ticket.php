<?php
session_start();
require '../config/database.php';

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
        $_POST['id']
    ]);

    // ✅ DEFINE A MENSAGEM AQUI
    $_SESSION['success'] = 'Ticket atualizado com sucesso ✔';
}

header('Location: tickets.php');
exit;
