<!doctype html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css"/>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <title>PetAI Animal Gallery</title>
</head>
<body style="background-color:#202125;">
<div class="container" style="padding-top:30px;">
    <div class="d-flex justify-content-center">
        <h1 style="color:#FEFFFF;">PetAI Animal Gallery</h1>
    </div>
    <hr class="mt-2 mb-5" style="height:2px;background-color:#FFFFFF" ;>

    <?php
    // Image extensions.
    $image_extensions = array("png", "jpg", "jpeg", "gif");

    // Check delete HTTP GET request - remove images.
    if (isset($_GET["delete"])) {
        $imageFileType = strtolower(pathinfo($_GET["delete"], PATHINFO_EXTENSION));
        if (file_exists($_GET["delete"]) && ($imageFileType == "jpg" || $imageFileType == "png" || $imageFileType == "jpeg")) {
            unlink($_GET["delete"]);
            echo "<script>
            $(document).ready(function(){
            $('#myModalOK').modal('show');
            });

            </script>";
        }
    }
    ?>

    <div class="row text-center text-lg-start">
        <?php
        // Target directory.
        $dir = 'uploads/';
        if (is_dir($dir)) {
            $count = 1;
            $files = scandir($dir);
            rsort($files);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    ?>
                    <div class="col-lg-3 col-md-4 col-6" style="padding-bottom:30px;">
                        <div class="row">
                            <a href="<?php echo $dir . $file; ?>" class="d-block mb-4 h-100" target="_blank">
                                <img class="img-fluid img-thumbnail" src="<?php echo $dir . $file; ?>" alt="">
                            </a>
                        </div>
                        <div class="row justify-content-end">

                            <div class="col md-8">
                                <p style="color:#FEFFFF;"><?php echo $file; ?></p>
                            </div>
                            <div class="col md-4">
                                <a href="loadPhotos.php?delete=<?php echo $dir . $file; ?>"
                                   class="btn btn-danger btn-sm">Delete</a>
                            </div>

                        </div>
                    </div>
                    <?php
                    $count++;
                }
            }
            if ($count == 1) {
                echo "<p style='color:#FEFFFF;text-align: center'>No images found</p>";
            }
        }
        ?>
    </div>

    <!-- Modal Delete OK-->
    <div class="modal fade" id="myModalOK" tabindex="-1" aria-labelledby="myModalOKLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background-color:#2cc791;">
                    <h5 class="modal-title" id="exampleModalLabel" style="color:white;">Success</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Successfully delete image file <b><?php $path_parts = pathinfo($_GET["delete"], PATHINFO_BASENAME);
                        echo $path_parts; ?> </b>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-primary" href="gallery.php" role="button">OK</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Delete Not OK-->
    <div class="modal fade" id="myModalOK" tabindex="-1" aria-labelledby="myModalOKLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background-color:#fc8403;">
                    <h5 class="modal-title" id="exampleModalLabel" style="color:white;">Success</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Something wrong while deleting
                    file <?php $path_parts = pathinfo($_GET["delete"], PATHINFO_BASENAME);
                    echo $path_parts; ?>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-secondary" href="gallery.php" role="button">OK</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>