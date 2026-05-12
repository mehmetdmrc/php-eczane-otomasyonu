<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "syp";

try {
    $db = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die(json_encode(["error" => "Veritabanı bağlantı hatası: " . $e->getMessage()]));
}
?>

