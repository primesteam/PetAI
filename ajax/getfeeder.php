<?php
require '../private/Database.php';

if (!isset($_GET)) return print "No get data";
if (!isset($_GET['code'])) return print "No code";

$code = $_GET['code'];

$db = Database::connect();
$stmt = $db->prepare("SELECT * FROM petai.feeder WHERE code=:code");
$stmt->bindParam(':code', $code);
$stmt->execute();
$feeder = $stmt->fetchObject();
?>
<pre><?php echo JSON_ENCODE($feeder) ?></pre>