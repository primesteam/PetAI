<?php
require 'ajax/connect.php';
//print_r($_GET);

if (!isset($_GET['code'])) return print "No code";

$code = $_GET['code'];

$db = Database::connect();
$stmt = $db->prepare("SELECT * FROM petai.feeder WHERE code=:code");
$stmt->bindParam(':code', $code);
$stmt->execute();
$feeder = $stmt->fetchObject();
if (isset($_GET['action'])) $action = $_GET['action'];
else return print "No action param";

// Check if the entry exists
if ($feeder) {
    if ($action === "record") {
        if (isset($_GET['cap'])) $capacity = $_GET['cap'];
        else return print "No data cap";
        // Calculation for food liters and remaining
    } else if ($action === "log") {
        if (isset($_GET['temp'])) $temperature = $_GET['temp'];
        else return print "No data temp";

        if (isset($_GET['volt'])) $voltage = $_GET['volt'];
        else return print "No data volt";

        $stmt = $db->prepare('INSERT INTO petai.logs (feeder, temperature, voltage) VALUES (:id, :temp, :volt)');
        $stmt->bindParam(':id', $feeder->id, PDO::PARAM_INT);
        $stmt->bindParam(':temp', $temperature);
        $stmt->bindParam(':volt', $voltage);
        $stmt->execute();
    } else {
        return print "No valid action";
    }
    print "Got Data!";
} else {
    return print "No feeder found";
}
