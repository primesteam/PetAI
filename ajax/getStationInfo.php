<?php
// Include Database
require '../private/Database.php';

// Check if code exists
if (!isset($_GET['code'])) return print "No code";

// Connect to Database and get station's info
$db = Database::connect();
$stmt = $db->prepare("SELECT * FROM petai.feeder WHERE code=:code");
$stmt->bindParam(':code', $_GET['code']);
$stmt->execute();
$station = $stmt->fetchObject();

// Set station ID
$id = $station->id;

// Get station's donates number
$stmt = $db->prepare("SELECT COUNT(*) FROM petai.donate WHERE feeder=:id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$donates = $stmt->fetchColumn();

// Get station's photos number
$stmt = $db->prepare("SELECT COUNT(*) FROM petai.photo WHERE feeder=:id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$photos = $stmt->fetchColumn();

// Get station's logs
$stmt = $db->prepare("SELECT * FROM petai.logs WHERE feeder=:id ORDER BY created DESC");
$stmt->bindParam(':id', $id);
$stmt->execute();
$logs = $stmt->fetchObject();

// Get station's data
$stmt = $db->prepare("SELECT * FROM petai.feeder_data WHERE feeder=:id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$data = $stmt->fetchObject();

// Create stationIndo instance
$stationInfo = new stdClass();

// Associate Station Data
// Station Info
$stationInfo->id = strval($station->id);
$stationInfo->created = $station->created;
$stationInfo->code = $station->code;
$stationInfo->location = [
    'latitude' => $station->latitude,
    'longitude' => $station->longitude
];
$stationInfo->status = $station->status;

// Counters
$stationInfo->donates = strval($donates);
$stationInfo->images = strval($photos);
$stationInfo->tankTopups = strval($data->tankTopups);
$stationInfo->refillsTotal = strval($data->refillsTotal);

// Data array
$stationInfo->refills = strval($data->refills);
$stationInfo->currentState = [
    'temperature' => strval($logs->temperature),
    'humidity' => strval($logs->humidity),
    'tankLevel' => strval($logs->tankLevel),
    'plateLevel' => strval($logs->plateLevel),
    'voltage' => strval($logs->voltage)
];

// Return station infos as JSON
echo json_encode($stationInfo);