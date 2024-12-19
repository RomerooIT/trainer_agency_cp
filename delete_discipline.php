<?php
ob_start();
session_start();
include('includes/db.php');
include 'includes/navbar.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'trainer')) {
    header("Location: 403.php");
    exit();
}

if (isset($_GET['id'])) {
    $discipline_id = $_GET['id'];

    $stmt = $conn->prepare('DELETE FROM disciplines WHERE ID = ?');
    $stmt->bind_param('i', $discipline_id);
    $stmt->execute();
    $stmt->close();

    header('Location: get_all_disciplines.php');
    exit;
}
?>
