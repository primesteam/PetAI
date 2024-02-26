<?php
require '../private/Database.php';

$db = Database::connect();
$stmt = $db->prepare("SELECT * FROM petai.feeder");
$stmt->execute();
$stations = $stmt->fetchObject();
echo "<pre>";
print_r($stations);