<!doctype html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/bootstrap.min.css"/>
</head>
<body>
<div class="container" style="margin-top:5%;">
    <div class="d-flex align-items-center">
        <div class="col-md6" style="padding:10px; border:1px solid black; border-radius: 5px;">
            <h2>Test Upload</h2>
            <form action="uploadImage.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="image">Input Image : </label>
                    <input type="file" name="imageFile" id="imageFile">
                </div>
                <br>
                <button type="submit" name="submit" class="btn btn-primary">Upload Image</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>