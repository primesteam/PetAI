<?php
require 'private/Database.php';
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
    $stmt = $db->prepare("SELECT * FROM petai.logs WHERE feeder=:id ORDER BY created DESC");
    $stmt->bindParam(':id', $feeder->id, PDO::PARAM_INT);
    $stmt->execute();
    $log = $stmt->fetchObject()[0];

    if ($action === "run") {
        if ($log->tankLevel > 0) {
            $tankLevel = $log->tankLevel - 1;
            $stmt = $db->prepare("UPDATE petai.logs SET tankLevel=:tanklvl WHERE id=:id");
            $stmt->bindParam(':id', $log->id, PDO::PARAM_INT);
            $stmt->bindParam(':tanklvl', $log->id, PDO::PARAM_INT);
            $stmt->execute();

            return print "Data saved";
        }
        else {
            // Send message to admins
            return print "Empty tank level";
        }
    }
    else if ($action === "log") {

        if (isset($_GET['temp'])) $temperature = $_GET['temp'];
        else return print "No data temp";

        if (isset($_GET['volt'])) $voltage = $_GET['volt'];
        else return print "No data volt";

        if (isset($_GET['humid'])) $humidity = $_GET['humid'];
        else return print "No data humid";

        if (isset($_GET['platelvl'])) $plateLevel = $_GET['platelvl'];
        else return print "No data platelvl";

        $stmt = $db->prepare('INSERT INTO petai.logs (feeder, temperature, voltage, humidity, tankLevel, plateLevel) VALUES (:id, :temp, :volt, :humid, :tanklvl, :platelvl)');
        $stmt->bindParam(':id', $feeder->id, PDO::PARAM_INT);
        $stmt->bindParam(':temp', $temperature);
        $stmt->bindParam(':volt', $voltage);
        $stmt->bindParam(':humid', $humidity);
        $stmt->bindParam(':tanklvl', $log['tankLevel']);
        $stmt->bindParam(':platelvl', $plateLevel);
        $stmt->execute();
    }
    else if ($action === "check") {
        $stmt = $db->prepare("SELECT * FROM petai.donate WHERE feeder=:id AND used!=1");
        $stmt->bindParam(':id', $feeder->id, PDO::PARAM_INT);
        $stmt->execute();
        $donates = $stmt->fetchAll();

        if (count($donates) != 0 && $log->tankLevel > 0 && $log->plateLevel <= 2) {
            $stmt = $db->prepare("UPDATE petai.donate SET used=1 WHERE id=:id");
            $stmt->bindParam(':id', $donates[0]["id"], PDO::PARAM_INT);
            $stmt->execute();
            return print "true";
        }
        else {
            return print "false";
        }
    }
    else {
        return print "No valid action";
    }
    print "Got Data!";
}
else {
    return print "No feeder found";
}
