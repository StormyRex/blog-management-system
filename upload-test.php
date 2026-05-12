<?php

require 'vendor/autoload.php';

use Shive\BlogManagementSystem\Providers\CloudinaryProvider;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_FILES['image'])) {

        $provider = new CloudinaryProvider();

        $result = $provider->upload(
            $_FILES['image']['tmp_name']
        );

        echo '<pre>';

        print_r($result);

        echo '</pre>';
    }
}
?>

<form method="POST" enctype="multipart/form-data">

    <input
        type="file"
        name="image"
        required
    >

    <button type="submit">
        Upload
    </button>

</form>