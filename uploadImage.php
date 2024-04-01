<?php
require 'private/Database.php';

// Check for code
if (isset($_GET["code"])) $code = $_GET["code"];
else return print "No code";

$db = Database::connect();
$stmt = $db->prepare("SELECT * FROM petai.feeder WHERE code=:code");
$stmt->bindParam(':code', $code);
$stmt->execute();
$feeder = $stmt->fetchObject();

// Check if the feeder exists
if (!$feeder) return print "No feeder found";

date_default_timezone_set('Europe/Athens');  //--> Adjust to your time zone.
$target_dir = "uploads/"; //--> Folder to store images.
$date = new DateTime(); //--> this returns the current date time.
$date_string = $date->format('Y-m-d_His');

$original_name = basename($_FILES["imageFile"]["name"]);
$imageFileType = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
$target_file = $target_dir . $date_string. "." . $imageFileType;
$uploadOk = 1;
$new_name = pathinfo($target_file, PATHINFO_BASENAME);

// Check if image file is an actual image or fake image.
if (isset($_POST["imageFile"])) {
    $check = getimagesize($_FILES["imageFile"]["tmp_name"]);
    if ($check !== false) {
        print "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    }
    else {
        print "File is not an image.";
        $uploadOk = 0;
    }
}


if ($_FILES["imageFile"]["size"] > 5000000) {
    $uploadOk = 0;
    print "Sorry, your file is too large.";
}

// Allow certain file formats.
if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
    $uploadOk = 0;
    print "Sorry, only JPG, JPEG & PNG files are allowed.";
}

// Check if $uploadOk is set to 0 by an error.
if ($uploadOk == 0) {
    print "Sorry, your file was not uploaded.";
}
else {
    if (move_uploaded_file($_FILES["imageFile"]["tmp_name"], $target_file)) {
        $stmt = $db->prepare('INSERT INTO petai.photo (name, location, feeder) VALUES (:name, :location, :id)');
        $stmt->bindParam(':name', $original_name);
        $stmt->bindParam(':location', $target_file);
        $stmt->bindParam(':id', $feeder->id, PDO::PARAM_INT);
        $stmt->execute();
        return print "Photos successfully uploaded to the server with the name : " . $new_name;
    }
    else {
        return print "Sorry, there was an error in the photo upload process.";
    }
}

