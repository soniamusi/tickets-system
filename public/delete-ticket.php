<?php
session_start();
require '../config/database.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $sql = "DELETE FROM tickets WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
}

$_SESSION['success'] = '🗑 Ticket excluído com sucesso';
header('Location: tickets.php');
exit;
