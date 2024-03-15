<?php
// Include Database
require '../private/Database.php';

// Connect to Database and get station's info
$db = Database::connect();
$stmt = $db->prepare("SELECT * FROM petai.feeder");
$stmt->execute();
$stations = $stmt->fetchObject();

// Return station infos as JSON
echo json_encode($stations);