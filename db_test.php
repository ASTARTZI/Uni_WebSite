<?php
require_once "config.php";

try {
    $stmt = $pdo->query("SELECT 1 AS ok");
    $row = $stmt->fetch();
    echo "DB OK: " . $row["ok"];
} catch (Exception $e) {
    echo "DB ERROR";
}
