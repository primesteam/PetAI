<?php
require '../private/Database.php';

if (!isset($_GET['code'])) return print "No code";
$code = $_GET['code'];

$db = Database::connect();
$stmt = $db->prepare("SELECT * FROM petai.feeder WHERE code=:code");
$stmt->bindParam(':code', $code);
$stmt->execute();
$station = $stmt->fetchObject();

$id = $station->id;

$stmt = $db->prepare("SELECT COUNT(*) FROM petai.donate WHERE feeder=:id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$donates = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM petai.photo WHERE feeder=:id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$photos = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT * FROM petai.logs WHERE feeder=:id ORDER BY created DESC");
$stmt->bindParam(':id', $id);
$stmt->execute();
$logs = $stmt->fetchObject();

$stmt = $db->prepare("SELECT * FROM petai.feeder_data WHERE feeder=:id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$data = $stmt->fetchObject();

$stationInfo = new stdClass();
// Station Data
$stationInfo->id = $station->id;
$stationInfo->created = $station->created;
$stationInfo->code = $station->code;
$stationInfo->location = [
    'latitude' => $station->latitude,
    'longitude' => $station->longitude
];
$stationInfo->status = $station->status;

// Counters
$stationInfo->donates = $donates;
$stationInfo->images = $photos;
$stationInfo->tankTopups = $data->tankTopups;
$stationInfo->refillsTotal = $data->refillsTotal;

// Data array
$stationInfo->refills = $data->refills;
$stationInfo->currentState = [
    'temperature' => $logs->temperature,
    'humidity' => $logs->humidity,
    'tankLevel' => $logs->tankLevel,
    'plateLevel' => $logs->plateLevel,
    'voltage' => $logs->voltage
];

//echo "<pre>";
//print_r($stationInfo);
echo json_encode($stationInfo);