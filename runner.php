<?php

require("teleboy.inc.php");

$teleboy = new Teleboy;
$config = include("config.inc.php");

$teleboy->login();

foreach($teleboy->recordings() as $recording) {
    $file_prefix = substr($recording->begin, 0, -14)."_".$recording->station_label."_".$recording->slug."_".str_replace(' ', '_', $recording->subtitle);

    if(isset($recording->serie_season)) {
    	$file_prefix .= "_S".$recording->serie_season;
    }

    if(isset($recording->serie_episode)) {
        $file_prefix .= "_E".$recording->serie_episode;
    }

    // Just download if not already downloaded
    if(!glob($config["recording_path"].$file_prefix."*.mp4")) {
        file_put_contents($config["recording_path"].$file_prefix.".json", json_encode($recording));
        file_put_contents($config["recording_path"].$file_prefix.".mp4", fopen($teleboy->recording_download_url($recording->id), 'r'));
    }

}

?>
