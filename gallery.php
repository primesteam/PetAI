<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PetAI Animal Gallery</title>
</head>
<body style="background-color:#202125;" id="myESP32CAMPhotos">
<script>
    let totalphotos = 0;
    let last_totalphotos = 0;

    loadPhotos();

    const timer_1 = setInterval(myTimer_1, 2000);

    function myTimer_1() {
        getTotalPhotos();
        if(last_totalphotos !== totalphotos) {
            last_totalphotos = totalphotos;

            loadPhotos();
        }
    }

    function getTotalPhotos() {
        <?php
        // Get station's photos number
        require 'private/Database.php';
        $db = Database::connect();
        $stmt = $db->prepare("SELECT COUNT(*) FROM petai.photo WHERE feeder=:id");
        $stmt->bindParam(':id', $id);
        $stmt->execute(); ?>
        totalphotos = <?php $stmt->fetchColumn() ?>
    }

    function loadPhotos() {
        let xmlhttp;
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("myESP32CAMPhotos").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET","loadPhotos.php",true);
        xmlhttp.send();
    }
</script>
</body>
</html>