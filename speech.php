<?php

require __DIR__ . '/lib/Ffmpeg_Wrapper.php';
require __DIR__ . '/lib/GoogleSpeech.php';
require __DIR__ . '/lib/GoogleStorage.php';

use lib\Ffmpeg_Wrapper;
use lib\GoogleSpeech;
use lib\GoogleStorage;

$ffmpeg = new Ffmpeg_Wrapper();
$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["audio"]["name"]);

//Save file to local machine
if (move_uploaded_file($_FILES["audio"]["tmp_name"], $target_file)) {
    //convert wav file to FLAC
    $flac_file = $ffmpeg->convertToFlac($target_file);

    $storage = new GoogleStorage();
    
    $fileName = $_FILES["audio"]["name"] .'.flac';
    $file = __DIR__ . '/uploads/'.$fileName;

    //upload to cloud storage
    $upload = $storage->upload($fileName, $file);
    
    if($upload){
        $speech = new GoogleSpeech();
        $flac = $storage->get($fileName);
        $texts = $speech->translate($flac);

        echo '<h4>Transcript:</h4>';
        echo '<ul>';
        foreach($texts as $t){
            echo '<li>'.$t['alternatives'][0]['transcript'].'</li>';
        }
        echo '</ul>';

    }else{
        echo 'Failed to upload audio file to google cloud storage.';
    }
    

} else {
    echo "Sorry, there was an error uploading your file.";
}

?>