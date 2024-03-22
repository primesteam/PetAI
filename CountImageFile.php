<?php
if (!empty($_POST)) {
    $cmd=$_POST["cmd"];

    if($cmd == "GTP") {
        $directory = "uploads/";  //--> Folder to store images.
        $filecount = count(glob($directory . "*.{jpeg,jpg,png,gif}",GLOB_BRACE));
        echo $filecount;
    }
}
?>