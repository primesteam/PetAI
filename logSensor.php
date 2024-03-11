<?php
require 'private/Database.php';

if (!isset($_GET)) return print "No get data";

$sensorId = isset($_GET['sensorId'])?filter_var($_GET['sensorId']):null;
$dhtTemperature = isset($_GET['dhtTemperature'])?filter_var($_GET['dhtTemperature']):null;
$dhtHumidity = isset($_GET['dhtHumidity'])?filter_var($_GET['dhtHumidity']):null;
$bmpTemperature = isset($_GET['bmpTemperature'])?filter_var($_GET['bmpTemperature']):null;
$bmpPressure = isset($_GET['bmpPressure'])?filter_var($_GET['bmpPressure']):null;
$bmpAltitude = isset($_GET['bmpAltitude'])?filter_var($_GET['bmpAltitude']):null;
$bmpRealAltitude = isset($_GET['bmpRealAltitude'])?filter_var($_GET['bmpRealAltitude']):null;

try {
    $db = Database::connect();
    $stmt = $db->prepare("INSERT INTO SensorValues (sensorId, dhtTemperature, dhtHumidity, bmpTemperature,
                          bmpPressure, bmpAltitude, bmpRealAltitude) VALUES (:sensorId, :dhtTemperature, :dhtHumidity, 
                            :bmpTemperature, :bmpPressure, :bmpAltitude, :bmpRealAltitude)");
    $stmt->bindParam(':sensorId', $sensorId);
    $stmt->bindParam(':dhtTemperature', $dhtTemperature);
    $stmt->bindParam(':dhtHumidity', $dhtHumidity);
    $stmt->bindParam(':bmpTemperature', $bmpTemperature);
    $stmt->bindParam(':bmpPressure', $bmpPressure);
    $stmt->bindParam(':bmpAltitude', $bmpAltitude);
    $stmt->bindParam(':bmpRealAltitude', $bmpRealAltitude);
    $stmt->execute();
    echo 'Success inserted: ' . $db->lastInsertId();
} catch (Exception $e) {
    echo 'Something went wrong: ' . $e->getMessage();
}