<?php

require("teleboy.inc.php");

$teleboy = new Teleboy;

$teleboy->login();

foreach($teleboy->recordings() as $recording) {
    $file_prefix = substr($recording->begin, 0, -14)."_".$recording->station_label."_".$recording->slug."_".str_replace(' ', '_', $recording->subtitle);

    if(!glob($file_prefix."*.mp4")) {
        file_put_contents($file_prefix.".json", json_encode($recording));
        file_put_contents($file_prefix.".mp4", fopen($teleboy->recording_download_url($recording->id), 'r'));
    }
}

?>
