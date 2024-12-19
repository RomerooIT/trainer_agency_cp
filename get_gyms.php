<?php
session_start();
include 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    header('Content-Type: application/json');
    try {
        $gyms = [];
        $result = $conn->query("SELECT ID, location, number FROM gyms ORDER BY location ASC");
        while ($row = $result->fetch_assoc()) {
            $gyms[] = $row;
        }
        echo json_encode(['gyms' => $gyms]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit();
}
