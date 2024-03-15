<?php
// Include Database
require '../private/Database.php';

// Check if code exists
if (!isset($_GET['code'])) return print "No code";

// Connect to Database and get data for the feeder
$db = Database::connect();
$stmt = $db->prepare("SELECT * FROM petai.feeder WHERE code=:code");
$stmt->bindParam(':code', $_GET['code']);
$stmt->execute();
$feeder = $stmt->fetchObject();
?>

<!--Show feeder's data as JSON-->
<pre><?php echo JSON_ENCODE($feeder) ?></pre>