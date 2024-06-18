<?php
// Include Database
require '../private/Database.php';

// Connect to Database and get station's info
$db = Database::connect();
$stmt = $db->prepare("SELECT concat('station_', id) as name, code FROM petai.feeder");
$stmt->setFetchMode(PDO::FETCH_OBJ);
$stmt->execute();
$stations = $stmt->fetchAll();

// Return station infos as JSON
echo json_encode($stations);