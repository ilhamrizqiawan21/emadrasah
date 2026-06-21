<?php
require_once __DIR__ . '/config/database.php';
try {
    $stmt = $pdo->query("DESCRIBE jadwals");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns: " . implode(", ", $cols);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
